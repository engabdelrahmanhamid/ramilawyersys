<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Contract Archive Catch-up V4
|--------------------------------------------------------------------------
| Purpose:
| - Catch up the paper archive for clients registered between 2026-05-01
|   and 2026-07-31 (defaults can be overridden by --from / --to).
| - Never trust the uploaded file name alone.
| - Split results into:
|     1) CONFIRMED contract
|     2) REVIEW REQUIRED (possible WhatsApp/generic/unknown document)
|     3) NO CONTRACT / NO CANDIDATE alert
| - Dry-run by default. --execute only copies files; it never edits DB rows.
|--------------------------------------------------------------------------
*/

$options = getopt('', ['from:', 'to:', 'execute']);

$from = isset($options['from'])
    ? Carbon::parse($options['from'])->startOfDay()
    : Carbon::parse('2026-07-01')->startOfDay();

$to = isset($options['to'])
    ? Carbon::parse($options['to'])->endOfDay()
    : Carbon::parse('2026-09-01')->endOfDay();

$execute = array_key_exists('execute', $options);

function normalizeArabic($text)
{
    $text = trim((string) $text);
    $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', $text);
    $text = str_replace(
        ['أ', 'إ', 'آ', 'ى', 'ة'],
        ['ا', 'ا', 'ا', 'ي', 'ه'],
        $text
    );
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[\-_().\[\]{}]+/u', ' ', $text);
    $text = preg_replace('/\s+/u', ' ', $text);
    return trim($text);
}

function containsText($haystack, $needle)
{
    return mb_strpos($haystack, $needle, 0, 'UTF-8') !== false;
}

function safeName($text)
{
    $text = trim((string) $text);
    $text = preg_replace('/[\/\\:*?"<>|]+/u', '-', $text);
    $text = preg_replace('/\s+/u', ' ', $text);
    return trim($text, " .");
}


function contractRequirement($statusId, $statusName)
{
    $statusId = (int) $statusId;

    // High-confidence statuses that represent actual legal clients whose
    // engagement agreement should be accounted for in the archive.
    if (in_array($statusId, [27, 75, 83], true)) {
        return 'REQUIRED';
    }

    // Cancelled clients may or may not have reached signing stage.
    if ($statusId === 78) {
        return 'CONDITIONAL';
    }

    return 'LEAD_OR_NOT_REQUIRED';
}

function locatePhysicalFile($storedFile)
{
    if (!$storedFile) {
        return null;
    }

    $paths = [
        base_path('images/Client/'.$storedFile),
        base_path('files/Client/'.$storedFile),
        public_path('images/Client/'.$storedFile),
        public_path('files/Client/'.$storedFile),
    ];

    foreach ($paths as $path) {
        if (is_file($path) && is_readable($path)) {
            return $path;
        }
    }

    return null;
}

function isObviousNonContractName($name)
{
    $name = normalizeArabic($name);

    $excluded = [
        'وكاله',
        'هويه',
        'العنوان الوطني',
        'تقرير',
        'حكم',
        'جلسه',
        'صحيفه',
        'دعوي',
        'مذكره',
        'اشعار',
        'مخالصه',
        'سجل الاسره',
        'السجل التجاري',
        'عقد الزواج',
        'عقد العمل',
        'العقد الوظيفي',
        'عقد ايجار',
        'ايصال تحويل',
        'كشف حساب',
        'تذكره',
        'تقرير طبي',
        'محادثه',
        'محادثات',
        'ضبط',
        'وثيقه تملك',
    ];

    foreach ($excluded as $word) {
        if (containsText($name, $word)) {
            return true;
        }
    }

    return false;
}

/*
|--------------------------------------------------------------------------
| Filename classification
|--------------------------------------------------------------------------
| confirmed=true means it is safe enough to go directly to the final
| archive based on the naming convention observed in this system.
| candidate=true means it must be surfaced for review, not silently ignored.
|--------------------------------------------------------------------------
*/
function classifyByName($fileName, $storedFile, $branchId)
{
    $name = normalizeArabic($fileName);
    $stored = normalizeArabic(pathinfo((string) $storedFile, PATHINFO_FILENAME));
    $extension = strtolower(pathinfo((string) $storedFile, PATHINFO_EXTENSION));

    // Explicit fee agreement wording.
    if (containsText($name, 'عقد') && containsText($name, 'اتعاب')) {
        return [
            'confirmed' => true,
            'candidate' => true,
            'score' => 100,
            'reason' => 'EXPLICIT_FEE_AGREEMENT_NAME',
        ];
    }

    if ($name === 'الاتعاب' || $name === 'اتعاب') {
        return [
            'confirmed' => true,
            'candidate' => true,
            'score' => 98,
            'reason' => 'EXPLICIT_FEES_NAME',
        ];
    }

    // Branch conventions observed in the live data.
    if ((int) $branchId === 5 && in_array($name, ['عقد العميل', 'عقد العميله'], true)) {
        return [
            'confirmed' => true,
            'candidate' => true,
            'score' => 90,
            'reason' => 'DAMMAM_CONTRACT_NAMING',
        ];
    }

    if ((int) $branchId === 6 && $name === 'العقد') {
        return [
            'confirmed' => true,
            'candidate' => true,
            'score' => 90,
            'reason' => 'MADINAH_CONTRACT_NAMING',
        ];
    }

    // Do not promote documents whose names clearly describe something else.
    if (isObviousNonContractName($name)) {
        return [
            'confirmed' => false,
            'candidate' => false,
            'score' => 0,
            'reason' => 'OBVIOUS_NON_CONTRACT',
        ];
    }

    $documentExtensions = ['pdf', 'doc', 'docx', 'rtf', 'odt'];
    $imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'heic'];

    // WhatsApp / phone / generic downloaded-file naming patterns.
    $looksWhatsapp = (
        preg_match('/\bwa\s*\d+\b/ui', $name) ||
        preg_match('/\bwa\s*\d+\b/ui', $stored) ||
        containsText($name, 'whatsapp') ||
        containsText($name, 'واتساب') ||
        preg_match('/\b(img|doc|document|scan|file)\s*\d+/ui', $name) ||
        preg_match('/\b20\d{6,}\b/u', $name)
    );

    $looksGeneric = (
        $name === '' ||
        in_array($name, ['مستند', 'ملف', 'document', 'file', 'scan', 'pdf'], true) ||
        preg_match('/^\d+$/u', $name)
    );

    if ($looksWhatsapp || $looksGeneric) {
        if (in_array($extension, $documentExtensions, true)) {
            return [
                'confirmed' => false,
                'candidate' => true,
                'score' => 65,
                'reason' => $looksWhatsapp ? 'WHATSAPP_OR_DOWNLOADED_DOCUMENT' : 'GENERIC_DOCUMENT_NAME',
            ];
        }

        if (in_array($extension, $imageExtensions, true)) {
            return [
                'confirmed' => false,
                'candidate' => true,
                'score' => 55,
                'reason' => $looksWhatsapp ? 'WHATSAPP_OR_DOWNLOADED_IMAGE' : 'GENERIC_IMAGE_NAME',
            ];
        }
    }

    // Unknown PDFs/documents are safer to review than to ignore.
    if (in_array($extension, $documentExtensions, true)) {
        return [
            'confirmed' => false,
            'candidate' => true,
            'score' => 45,
            'reason' => 'UNKNOWN_DOCUMENT_REVIEW',
        ];
    }

    // Unknown images may be scans/photos of an agreement.
    if (in_array($extension, $imageExtensions, true)) {
        return [
            'confirmed' => false,
            'candidate' => true,
            'score' => 35,
            'reason' => 'UNKNOWN_IMAGE_REVIEW',
        ];
    }

    return [
        'confirmed' => false,
        'candidate' => false,
        'score' => 0,
        'reason' => 'NOT_A_DOCUMENT_CANDIDATE',
    ];
}

function findPdfToTextBinary()
{
    if (!function_exists('shell_exec')) {
        return null;
    }

    $result = @shell_exec('command -v pdftotext 2>/dev/null');
    $binary = trim((string) $result);

    return $binary !== '' ? $binary : null;
}

function scanPdfTextForContract($path, $pdftotextBinary)
{
    if (!$pdftotextBinary || strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'pdf') {
        return [
            'available' => false,
            'explicit' => false,
            'probable' => false,
            'score' => 0,
            'reason' => 'PDF_TEXT_SCAN_NOT_AVAILABLE',
        ];
    }

    $command = escapeshellarg($pdftotextBinary)
        .' -f 1 -l 4 -layout '
        .escapeshellarg($path)
        .' - 2>/dev/null';

    $text = @shell_exec($command);

    if (!is_string($text) || trim($text) === '') {
        return [
            'available' => true,
            'explicit' => false,
            'probable' => false,
            'score' => 0,
            'reason' => 'PDF_NO_EXTRACTABLE_TEXT',
        ];
    }

    $text = normalizeArabic($text);
    $score = 0;

    $explicit = (
        containsText($text, 'عقد اتعاب') ||
        containsText($text, 'اتفاقيه اتعاب') ||
        (containsText($text, 'عقد') && containsText($text, 'اتعاب المحاماه'))
    );

    if ($explicit) {
        $score += 120;
    }

    if (containsText($text, 'تقديم خدمات قانونيه')) {
        $score += 55;
    }

    if (containsText($text, 'اتعاب')) {
        $score += 35;
    }

    if (containsText($text, 'الطرف الاول')) {
        $score += 15;
    }

    if (containsText($text, 'الطرف الثاني')) {
        $score += 15;
    }

    if (containsText($text, 'محاماه') || containsText($text, 'استشارات قانونيه')) {
        $score += 15;
    }

    return [
        'available' => true,
        'explicit' => $explicit,
        'probable' => !$explicit && $score >= 90,
        'score' => $score,
        'reason' => $explicit
            ? 'PDF_TEXT_EXPLICIT_CONTRACT'
            : ($score >= 90 ? 'PDF_TEXT_PROBABLE_CONTRACT' : 'PDF_TEXT_NOT_ENOUGH'),
    ];
}

function openCsv($path, array $headers)
{
    $handle = fopen($path, 'w');
    fwrite($handle, "\xEF\xBB\xBF");
    fputcsv($handle, $headers);
    return $handle;
}

function writeCsvRow($handle, array $row)
{
    fputcsv($handle, $row);
}

function copyWithDirectories($source, $destination)
{
    $dir = dirname($destination);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    return copy($source, $destination);
}

$pdftotextBinary = findPdfToTextBinary();

$timestamp = now()->format('Ymd_His');
$reportDir = storage_path('app/contract_archive_reports');

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$runRoot = $execute
    ? storage_path('app/print_batch_no_jeddah_'.$from->format('Ymd').'_'.$to->format('Ymd').'_'.$timestamp)
    : $reportDir.'/dry_run_print_no_jeddah_'.$timestamp;

if ($execute && !is_dir($runRoot)) {
    mkdir($runRoot, 0775, true);
}

$reportPrefix = $execute ? $runRoot.'/' : $runRoot.'_';

$allReportPath = $reportPrefix.'all_clients.csv';
$confirmedReportPath = $reportPrefix.'confirmed_contracts.csv';
$reviewReportPath = $reportPrefix.'needs_review.csv';
$noContractReportPath = $reportPrefix.'no_contract_alert.csv';
$missingFileReportPath = $reportPrefix.'physical_file_missing.csv';
$requiredMissingReportPath = $reportPrefix.'missing_contract_required.csv';
$requiredReviewReportPath = $reportPrefix.'required_contract_review.csv';
$statusAnomalyReportPath = $reportPrefix.'status_anomalies.csv';

$commonHeaders = [
    'Client ID',
    'Client Name',
    'Registered At',
    'Branch',
    'Client Status ID',
    'Client Status Name',
    'Contract Requirement',
    'Has Case',
    'Has Service',
    'Status',
    'Client File ID',
    'Displayed Name',
    'Stored File',
    'Client File Date',
    'Client File Created At',
    'Physical Source',
    'Detection Reason',
    'Name Score',
    'PDF Text Score',
    'Destination'
];

$allCsv = openCsv($allReportPath, $commonHeaders);
$confirmedCsv = openCsv($confirmedReportPath, $commonHeaders);
$reviewCsv = openCsv($reviewReportPath, $commonHeaders);
$missingFileCsv = openCsv($missingFileReportPath, $commonHeaders);
$requiredReviewCsv = openCsv($requiredReviewReportPath, $commonHeaders);

$noContractCsv = openCsv($noContractReportPath, [
    'Client ID',
    'Client Name',
    'Registered At',
    'Branch',
    'Client Status ID',
    'Client Status Name',
    'Contract Requirement',
    'Has Case',
    'Has Service',
    'Alert',
    'Total Attached Files',
    'Existing File Names'
]);

$requiredMissingCsv = openCsv($requiredMissingReportPath, [
    'Client ID',
    'Client Name',
    'Registered At',
    'Branch',
    'Client Status ID',
    'Client Status Name',
    'Has Case',
    'Has Service',
    'Alert',
    'Total Attached Files',
    'Existing File Names'
]);

$statusAnomalyCsv = openCsv($statusAnomalyReportPath, [
    'Client ID',
    'Client Name',
    'Registered At',
    'Branch',
    'Client Status ID',
    'Client Status Name',
    'Has Case',
    'Has Service',
    'Reason',
    'Total Attached Files',
    'Existing File Names'
]);

$clients = DB::table('clients')
    ->leftJoin('branches', 'clients.branch_id', '=', 'branches.id')
    ->leftJoin('client_statuses', 'clients.status_id', '=', 'client_statuses.id')
    ->whereBetween('clients.created_at', [$from, $to])
    ->where('clients.branch_id', '!=', 4)
    ->select(
        'clients.id',
        'clients.name',
        'clients.branch_id',
        'clients.status_id',
        'clients.created_at',
        'branches.name as branch_name',
        'client_statuses.name as status_name'
    )
    ->orderBy('clients.created_at')
    ->get();

$totalClients = $clients->count();

$clientIds = $clients->pluck('id')->all();
$allFiles = collect();

foreach (array_chunk($clientIds, 500) as $ids) {
    $files = DB::table('client_files')
        ->whereIn('client_id', $ids)
        ->select('id', 'name', 'date', 'file', 'client_id', 'created_at')
        ->get();
    $allFiles = $allFiles->merge($files);
}

$filesByClient = $allFiles->groupBy('client_id');


$caseClientIds = DB::table('user_cases')
    ->whereIn('client_id', $clientIds)
    ->distinct()
    ->pluck('client_id')
    ->mapWithKeys(function ($id) { return [(int) $id => true]; });

$serviceClientIds = DB::table('services')
    ->whereIn('client_id', $clientIds)
    ->distinct()
    ->pluck('client_id')
    ->mapWithKeys(function ($id) { return [(int) $id => true]; });

$stats = [
    'total_clients' => $totalClients,
    'confirmed_clients' => 0,
    'confirmed_files' => 0,
    'content_confirmed_files' => 0,
    'review_clients' => 0,
    'review_candidate_files' => 0,
    'no_contract_clients' => 0,
    'clients_without_any_files' => 0,
    'missing_physical_files' => 0,
    'multiple_confirmed_clients' => 0,
    'copied_confirmed_files' => 0,
    'copied_review_files' => 0,
    'copy_failed' => 0,
    'required_clients' => 0,
    'required_confirmed_clients' => 0,
    'required_review_clients' => 0,
    'required_missing_clients' => 0,
    'conditional_missing_clients' => 0,
    'lead_no_contract_ignored' => 0,
    'status_anomalies' => 0,
];

echo PHP_EOL;
echo "====================================================".PHP_EOL;
echo " PRINT BATCH JUL-SEP01 NO JEDDAH".PHP_EOL;
echo "====================================================".PHP_EOL;
echo "FROM          : ".$from->format('Y-m-d H:i:s').PHP_EOL;
echo "TO            : ".$to->format('Y-m-d H:i:s').PHP_EOL;
echo "MODE          : ".($execute ? 'EXECUTE' : 'DRY RUN').PHP_EOL;
echo "JEDDAH        : EXCLUDED".PHP_EOL;
echo "PRINT CONTENT : CONFIRMED CONTRACTS ONLY".PHP_EOL;
echo "CLIENTS FOUND : ".$totalClients.PHP_EOL;
echo "PDF TEXT SCAN : ".($pdftotextBinary ? 'AVAILABLE ('.$pdftotextBinary.')' : 'NOT AVAILABLE').PHP_EOL;
echo PHP_EOL;

foreach ($clients as $client) {
    $clientFiles = $filesByClient->get($client->id, collect());
    $requirement = contractRequirement($client->status_id, $client->status_name);
    $hasCase = $caseClientIds->has((int) $client->id);
    $hasService = $serviceClientIds->has((int) $client->id);

    if ($requirement === 'REQUIRED') {
        $stats['required_clients']++;
    }
    $confirmed = [];
    $reviewCandidates = [];
    $existingNames = [];

    foreach ($clientFiles as $file) {
        $existingNames[] = (string) $file->name;
        $source = locatePhysicalFile($file->file);
        $nameClass = classifyByName($file->name, $file->file, $client->branch_id);
        $pdfScan = [
            'available' => false,
            'explicit' => false,
            'probable' => false,
            'score' => 0,
            'reason' => 'NOT_SCANNED',
        ];

        if ($source && strtolower(pathinfo($source, PATHINFO_EXTENSION)) === 'pdf') {
            // Scan explicit-name files only when needed; always scan uncertain PDF candidates.
            if (!$nameClass['confirmed']) {
                $pdfScan = scanPdfTextForContract($source, $pdftotextBinary);
            }
        }

        $item = [
            'record' => $file,
            'source' => $source,
            'name_class' => $nameClass,
            'pdf_scan' => $pdfScan,
        ];

        if ($nameClass['confirmed']) {
            $confirmed[] = $item;
            continue;
        }

        // Random/WhatsApp PDF whose text explicitly says fee agreement becomes confirmed.
        if ($pdfScan['explicit']) {
            $item['name_class']['score'] = max($item['name_class']['score'], 100);
            $item['name_class']['reason'] = 'CONTENT_CONFIRMED_DESPITE_FILENAME';
            $confirmed[] = $item;
            $stats['content_confirmed_files']++;
            continue;
        }

        // Probable text match stays in review to prevent false positives.
        if ($nameClass['candidate'] || $pdfScan['probable']) {
            $reviewCandidates[] = $item;
        }
    }

    // If we found confirmed contracts, don't clutter review with unrelated generic files.
    if (count($confirmed) > 0) {
        $stats['confirmed_clients']++;
        $stats['confirmed_files'] += count($confirmed);

        if ($requirement === 'REQUIRED') {
            $stats['required_confirmed_clients']++;
        } elseif ($requirement === 'LEAD_OR_NOT_REQUIRED') {
            // A lead-like status with a confirmed contract often means the
            // workflow status was not updated after signing.
            $stats['status_anomalies']++;
            writeCsvRow($statusAnomalyCsv, [
                $client->id,
                $client->name,
                $client->created_at,
                $client->branch_name,
                $client->status_id,
                $client->status_name,
                $hasCase ? 'YES' : 'NO',
                $hasService ? 'YES' : 'NO',
                'CONFIRMED_CONTRACT_BUT_STATUS_NOT_CONTRACT_REQUIRED',
                $clientFiles->count(),
                implode(' | ', array_values(array_filter($existingNames))),
            ]);
        }

        if (count($confirmed) > 1) {
            $stats['multiple_confirmed_clients']++;
        }

        usort($confirmed, function ($a, $b) {
            return strcmp((string) $a['record']->created_at, (string) $b['record']->created_at);
        });

        $date = Carbon::parse($client->created_at);
        $month = $date->format('Y-m');
        $day = $date->format('d');
        $branch = safeName($client->branch_name ?: 'بدون فرع');
        $clientName = safeName($client->name ?: 'بدون اسم');
        $countConfirmed = count($confirmed);

        foreach ($confirmed as $index => $item) {
            $file = $item['record'];
            $source = $item['source'];
            $extension = strtolower(pathinfo((string) $file->file, PATHINFO_EXTENSION)) ?: 'file';
            $suffix = $countConfirmed > 1
                ? ' '.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)
                : '';

            $baseFileName = $client->id.' - '.$clientName.' - عقد الأتعاب'.$suffix.'.'.$extension;
            $printOrder = str_pad((string) ($stats['copied_confirmed_files'] + 1), 3, '0', STR_PAD_LEFT);
            $newFileName = $printOrder.' - '.$baseFileName;
            $destination = $execute
                ? $runRoot.'/PRINT_READY/'.$newFileName
                : '';

            $status = 'CONFIRMED';

            if (!$source) {
                $stats['missing_physical_files']++;
                $status = 'CONFIRMED_BUT_PHYSICAL_FILE_MISSING';
            } elseif ($execute) {
                if (copyWithDirectories($source, $destination)) {
                    $stats['copied_confirmed_files']++;
                    $status = 'COPIED_CONFIRMED';
                } else {
                    $stats['copy_failed']++;
                    $status = 'COPY_FAILED';
                }
            }

            $reason = $item['name_class']['reason'];
            if ($item['pdf_scan']['explicit']) {
                $reason .= '|PDF_TEXT_EXPLICIT';
            }

            $row = [
                $client->id,
                $client->name,
                $client->created_at,
                $client->branch_name,
                $client->status_id,
                $client->status_name,
                $requirement,
                $hasCase ? 'YES' : 'NO',
                $hasService ? 'YES' : 'NO',
                $status,
                $file->id,
                $file->name,
                $file->file,
                $file->date,
                $file->created_at,
                $source ?: '',
                $reason,
                $item['name_class']['score'],
                $item['pdf_scan']['score'],
                $destination,
            ];

            writeCsvRow($allCsv, $row);
            writeCsvRow($confirmedCsv, $row);

            if (!$source) {
                writeCsvRow($missingFileCsv, $row);
            }
        }

        continue;
    }

    // No confirmed agreement: surface possible files for human review.
    if (count($reviewCandidates) > 0) {
        $stats['review_clients']++;
        $stats['review_candidate_files'] += count($reviewCandidates);

        if ($requirement === 'REQUIRED') {
            $stats['required_review_clients']++;
        } elseif ($requirement === 'LEAD_OR_NOT_REQUIRED' && ($hasCase || $hasService)) {
            $stats['status_anomalies']++;
            writeCsvRow($statusAnomalyCsv, [
                $client->id,
                $client->name,
                $client->created_at,
                $client->branch_name,
                $client->status_id,
                $client->status_name,
                $hasCase ? 'YES' : 'NO',
                $hasService ? 'YES' : 'NO',
                'LEAD_STATUS_WITH_CASE_OR_SERVICE_AND_REVIEW_FILES',
                $clientFiles->count(),
                implode(' | ', array_values(array_filter($existingNames))),
            ]);
        }

        usort($reviewCandidates, function ($a, $b) {
            $aScore = max($a['name_class']['score'], $a['pdf_scan']['score']);
            $bScore = max($b['name_class']['score'], $b['pdf_scan']['score']);
            if ($aScore !== $bScore) {
                return $bScore <=> $aScore;
            }
            return strcmp((string) $b['record']->created_at, (string) $a['record']->created_at);
        });

        $date = Carbon::parse($client->created_at);
        $month = $date->format('Y-m');
        $day = $date->format('d');
        $branch = safeName($client->branch_name ?: 'بدون فرع');
        $clientName = safeName($client->name ?: 'بدون اسم');

        foreach ($reviewCandidates as $index => $item) {
            $file = $item['record'];
            $source = $item['source'];
            $extension = strtolower(pathinfo((string) $file->file, PATHINFO_EXTENSION)) ?: 'file';
            $reviewFileName = $client->id.' - '.$clientName.' - مراجعة '.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT).' - '.safeName($file->name ?: 'بدون اسم').'.'.$extension;
            $copyReview = false;
            $destination = '';
            $status = 'REVIEW_NOT_INCLUDED_IN_PRINT_BATCH';

            if (!$source) {
                $stats['missing_physical_files']++;
                $status = 'REVIEW_CANDIDATE_FILE_MISSING';
            }

            $reason = $item['name_class']['reason'];
            if ($item['pdf_scan']['probable']) {
                $reason .= '|PDF_TEXT_PROBABLE';
            }

            $row = [
                $client->id,
                $client->name,
                $client->created_at,
                $client->branch_name,
                $client->status_id,
                $client->status_name,
                $requirement,
                $hasCase ? 'YES' : 'NO',
                $hasService ? 'YES' : 'NO',
                $status,
                $file->id,
                $file->name,
                $file->file,
                $file->date,
                $file->created_at,
                $source ?: '',
                $reason,
                $item['name_class']['score'],
                $item['pdf_scan']['score'],
                $destination,
            ];

            writeCsvRow($allCsv, $row);
            writeCsvRow($reviewCsv, $row);
            if ($requirement === 'REQUIRED') {
                writeCsvRow($requiredReviewCsv, $row);
            }

            if (!$source) {
                writeCsvRow($missingFileCsv, $row);
            }
        }

        continue;
    }

    // No confirmed agreement and no plausible file candidate.
    $stats['no_contract_clients']++;

    if ($clientFiles->count() === 0) {
        $stats['clients_without_any_files']++;
    }

    $alert = 'NO_CONTRACT_OR_CANDIDATE_FOUND';
    if ($clientFiles->count() === 0) {
        $alert = 'NO_FILES_AT_ALL';
    }

    if ($requirement === 'REQUIRED') {
        $stats['required_missing_clients']++;
        $alert = $clientFiles->count() === 0
            ? 'REQUIRED_CLIENT_NO_FILES_AT_ALL'
            : 'REQUIRED_CLIENT_NO_CONTRACT_FOUND';

        writeCsvRow($requiredMissingCsv, [
            $client->id,
            $client->name,
            $client->created_at,
            $client->branch_name,
            $client->status_id,
            $client->status_name,
            $hasCase ? 'YES' : 'NO',
            $hasService ? 'YES' : 'NO',
            $alert,
            $clientFiles->count(),
            implode(' | ', array_values(array_filter($existingNames))),
        ]);
    } elseif ($requirement === 'CONDITIONAL') {
        $stats['conditional_missing_clients']++;
        $alert = $clientFiles->count() === 0
            ? 'CANCELLED_CLIENT_NO_FILES_REVIEW'
            : 'CANCELLED_CLIENT_NO_CONTRACT_REVIEW';
    } else {
        $stats['lead_no_contract_ignored']++;

        if ($hasCase || $hasService) {
            $stats['status_anomalies']++;
            writeCsvRow($statusAnomalyCsv, [
                $client->id,
                $client->name,
                $client->created_at,
                $client->branch_name,
                $client->status_id,
                $client->status_name,
                $hasCase ? 'YES' : 'NO',
                $hasService ? 'YES' : 'NO',
                'LEAD_STATUS_WITH_CASE_OR_SERVICE_BUT_NO_CONTRACT_FILE',
                $clientFiles->count(),
                implode(' | ', array_values(array_filter($existingNames))),
            ]);
        }
    }

    // General report keeps every client, but the dedicated REQUIRED report is
    // the one intended for operational follow-up.
    writeCsvRow($noContractCsv, [
        $client->id,
        $client->name,
        $client->created_at,
        $client->branch_name,
        $client->status_id,
        $client->status_name,
        $requirement,
        $hasCase ? 'YES' : 'NO',
        $hasService ? 'YES' : 'NO',
        $alert,
        $clientFiles->count(),
        implode(' | ', array_values(array_filter($existingNames))),
    ]);

    writeCsvRow($allCsv, [
        $client->id,
        $client->name,
        $client->created_at,
        $client->branch_name,
        $client->status_id,
        $client->status_name,
        $requirement,
        $hasCase ? 'YES' : 'NO',
        $hasService ? 'YES' : 'NO',
        $alert,
        '', '', '', '', '', '', '', '', '', ''
    ]);
}

fclose($allCsv);
fclose($confirmedCsv);
fclose($reviewCsv);
fclose($noContractCsv);
fclose($missingFileCsv);
fclose($requiredReviewCsv);
fclose($requiredMissingCsv);
fclose($statusAnomalyCsv);

$zipPath = null;

if ($execute && class_exists('ZipArchive')) {
    $zipPath = $runRoot.'.zip';
    $zip = new ZipArchive();

    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($runRoot, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($runRoot) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();
    }
}

echo PHP_EOL;
echo "====================================================".PHP_EOL;
echo " SUMMARY".PHP_EOL;
echo "====================================================".PHP_EOL;
echo "TOTAL CLIENTS                : ".$stats['total_clients'].PHP_EOL;
echo "CONFIRMED CONTRACT CLIENTS   : ".$stats['confirmed_clients'].PHP_EOL;
echo "CONFIRMED CONTRACT FILES     : ".$stats['confirmed_files'].PHP_EOL;
echo "CONFIRMED BY PDF CONTENT     : ".$stats['content_confirmed_files'].PHP_EOL;
echo "NEEDS REVIEW CLIENTS         : ".$stats['review_clients'].PHP_EOL;
echo "REVIEW CANDIDATE FILES       : ".$stats['review_candidate_files'].PHP_EOL;
echo "NO CONTRACT / NO CANDIDATE   : ".$stats['no_contract_clients'].PHP_EOL;
echo "CLIENTS WITH NO FILES AT ALL : ".$stats['clients_without_any_files'].PHP_EOL;
echo "PHYSICAL FILE MISSING        : ".$stats['missing_physical_files'].PHP_EOL;
echo "MULTIPLE CONFIRMED CLIENTS   : ".$stats['multiple_confirmed_clients'].PHP_EOL;
echo "REQUIRED STATUS CLIENTS        : ".$stats['required_clients'].PHP_EOL;
echo "REQUIRED + CONFIRMED           : ".$stats['required_confirmed_clients'].PHP_EOL;
echo "REQUIRED + NEEDS REVIEW        : ".$stats['required_review_clients'].PHP_EOL;
echo "REQUIRED + MISSING CONTRACT    : ".$stats['required_missing_clients'].PHP_EOL;
echo "CANCELLED + MISSING/REVIEW     : ".$stats['conditional_missing_clients'].PHP_EOL;
echo "LEAD NO-CONTRACT IGNORED       : ".$stats['lead_no_contract_ignored'].PHP_EOL;
echo "STATUS / DATA ANOMALIES        : ".$stats['status_anomalies'].PHP_EOL;

if ($execute) {
    echo "COPIED CONFIRMED FILES       : ".$stats['copied_confirmed_files'].PHP_EOL;
    echo "REVIEW FILES COPIED          : 0 (excluded from print batch)".PHP_EOL;
    echo "COPY FAILED                  : ".$stats['copy_failed'].PHP_EOL;
}

echo PHP_EOL;
echo "ALL CLIENTS REPORT : ".$allReportPath.PHP_EOL;
echo "CONFIRMED REPORT   : ".$confirmedReportPath.PHP_EOL;
echo "REVIEW REPORT      : ".$reviewReportPath.PHP_EOL;
echo "NO CONTRACT ALERT  : ".$noContractReportPath.PHP_EOL;
echo "MISSING FILE REPORT: ".$missingFileReportPath.PHP_EOL;
echo "REQUIRED MISSING    : ".$requiredMissingReportPath.PHP_EOL;
echo "REQUIRED REVIEW     : ".$requiredReviewReportPath.PHP_EOL;
echo "STATUS ANOMALIES    : ".$statusAnomalyReportPath.PHP_EOL;

if ($execute) {
    echo "ARCHIVE ROOT       : ".$runRoot.PHP_EOL;
    if ($zipPath && file_exists($zipPath)) {
        echo "ZIP                : ".$zipPath.PHP_EOL;
    }
} else {
    echo PHP_EOL."DRY RUN ONLY - NO FILES WERE COPIED AND DATABASE WAS NOT CHANGED.".PHP_EOL;
}

echo PHP_EOL;
