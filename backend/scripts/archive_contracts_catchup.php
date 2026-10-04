<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Arguments
|--------------------------------------------------------------------------
*/

$options = getopt('', [
    'from:',
    'to:',
    'execute'
]);

$from = isset($options['from'])
    ? Carbon::parse($options['from'])->startOfDay()
    : now()->subMonths(3)->startOfDay();

$to = isset($options['to'])
    ? Carbon::parse($options['to'])->endOfDay()
    : now()->endOfDay();

$execute = array_key_exists('execute', $options);


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function normalizeArabic($text)
{
    $text = trim((string) $text);

    // Remove Arabic diacritics + tatweel
    $text = preg_replace(
        '/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u',
        '',
        $text
    );

    $text = str_replace(
        ['أ', 'إ', 'آ', 'ى'],
        ['ا', 'ا', 'ا', 'ي'],
        $text
    );

    $text = mb_strtolower($text, 'UTF-8');

    $text = preg_replace('/[\-_().]+/u', ' ', $text);

    $text = preg_replace('/\s+/u', ' ', $text);

    return trim($text);
}

function containsArabic($haystack, $needle)
{
    return mb_strpos($haystack, $needle, 0, 'UTF-8') !== false;
}

function safeName($text)
{
    $text = trim((string) $text);

    $text = preg_replace(
        '/[\/\\\\:*?"<>|]+/u',
        '-',
        $text
    );

    $text = preg_replace('/\s+/u', ' ', $text);

    return trim($text, " .");
}


/*
|--------------------------------------------------------------------------
| Contract Detection
|--------------------------------------------------------------------------
|
| High confidence:
| Any document containing both عقد + اتعاب
|
| Branch fallback:
| 5 = Dammam -> عقد العميل
| 6 = Madinah -> العقد
|
*/

function contractScore($fileName, $branchId)
{
    $name = normalizeArabic($fileName);

    /*
     * Highest confidence:
     * عقد اتعاب / عقد الاتعاب / عقد اتعاب-اسم العميل etc.
     */
    if (
        containsArabic($name, 'عقد') &&
        containsArabic($name, 'اتعاب')
    ) {
        return 100;
    }

    /*
     * A document simply called "الاتعاب"
     */
    if (
        $name === 'الاتعاب' ||
        $name === 'اتعاب'
    ) {
        return 95;
    }

    /*
     * Dammam convention
     */
    if ((int) $branchId === 5) {
        if (
            $name === 'عقد العميل' ||
            $name === 'عقد العميلة'
        ) {
            return 80;
        }
    }

    /*
     * Madinah convention
     */
    if ((int) $branchId === 6) {
        if ($name === 'العقد') {
            return 80;
        }
    }

    return 0;
}


/*
|--------------------------------------------------------------------------
| Load Clients
|--------------------------------------------------------------------------
*/

echo PHP_EOL;
echo "============================================".PHP_EOL;
echo " CONTRACT ARCHIVE CATCH-UP".PHP_EOL;
echo "============================================".PHP_EOL;

echo "FROM : ".$from->format('Y-m-d H:i:s').PHP_EOL;
echo "TO   : ".$to->format('Y-m-d H:i:s').PHP_EOL;

echo "MODE : ".($execute ? 'EXECUTE' : 'DRY RUN').PHP_EOL;
echo PHP_EOL;


$clients = DB::table('clients')
    ->leftJoin(
        'branches',
        'clients.branch_id',
        '=',
        'branches.id'
    )
    ->whereBetween(
        'clients.created_at',
        [$from, $to]
    )
    ->select(
        'clients.id',
        'clients.name',
        'clients.branch_id',
        'clients.created_at',
        'branches.name as branch_name'
    )
    ->orderBy('clients.created_at')
    ->get();


$totalClients = $clients->count();

echo "CLIENTS FOUND : ".$totalClients.PHP_EOL;

if ($totalClients === 0) {
    echo "No clients found.".PHP_EOL;
    exit(0);
}


/*
|--------------------------------------------------------------------------
| Load Client Files
|--------------------------------------------------------------------------
*/

$clientIds = $clients->pluck('id')->all();

$allFiles = collect();

foreach (array_chunk($clientIds, 500) as $ids) {

    $files = DB::table('client_files')
        ->whereIn('client_id', $ids)
        ->select(
            'id',
            'name',
            'file',
            'client_id',
            'created_at'
        )
        ->get();

    $allFiles = $allFiles->merge($files);
}

$filesByClient = $allFiles->groupBy('client_id');


/*
|--------------------------------------------------------------------------
| Output Directories
|--------------------------------------------------------------------------
*/

$timestamp = now()->format('Ymd_His');

$reportDir = storage_path('app/contract_archive_reports');

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$archiveRoot = storage_path(
    'app/contract_archive_catchup_'.$timestamp
);

if ($execute && !is_dir($archiveRoot)) {
    mkdir($archiveRoot, 0775, true);
}

$reportPath = $execute
    ? $archiveRoot.'/report.csv'
    : $reportDir.'/dry_run_'.$timestamp.'.csv';


/*
|--------------------------------------------------------------------------
| Report
|--------------------------------------------------------------------------
*/

$report = fopen($reportPath, 'w');

// UTF-8 BOM for Excel
fwrite($report, "\xEF\xBB\xBF");

fputcsv($report, [
    'Client ID',
    'Client Name',
    'Registered At',
    'Branch',
    'Status',
    'Matched Document',
    'Original File',
    'Physical Source',
    'Destination',
    'Matches Found'
]);


/*
|--------------------------------------------------------------------------
| Process
|--------------------------------------------------------------------------
*/

$stats = [
    'ready' => 0,
    'missing_contract' => 0,
    'missing_file' => 0,
    'multiple' => 0,
    'copied' => 0,
    'copy_failed' => 0
];


foreach ($clients as $client) {

    $clientFiles = $filesByClient->get(
        $client->id,
        collect()
    );

    $matches = [];

    foreach ($clientFiles as $file) {

        $score = contractScore(
            $file->name,
            $client->branch_id
        );

        if ($score > 0) {

            $matches[] = [
                'record' => $file,
                'score' => $score
            ];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | No Contract
    |--------------------------------------------------------------------------
    */

    if (count($matches) === 0) {

        $stats['missing_contract']++;

        fputcsv($report, [
            $client->id,
            $client->name,
            $client->created_at,
            $client->branch_name,
            'NO_CONTRACT',
            '',
            '',
            '',
            '',
            0
        ]);

        continue;
    }


    /*
    |--------------------------------------------------------------------------
    | Sort: confidence first, newest second
    |--------------------------------------------------------------------------
    */

    usort($matches, function ($a, $b) {

        if ($a['score'] !== $b['score']) {
            return $b['score'] <=> $a['score'];
        }

        return strcmp(
            (string) $b['record']->created_at,
            (string) $a['record']->created_at
        );
    });


    if (count($matches) > 1) {
        $stats['multiple']++;
    }


    /*
    |--------------------------------------------------------------------------
    | Select Best Match
    |--------------------------------------------------------------------------
    */

    $selected = $matches[0]['record'];


    /*
    |--------------------------------------------------------------------------
    | Find Physical File
    |--------------------------------------------------------------------------
    */

    $possibleSources = [

        public_path(
            'images/Client/'.$selected->file
        ),

        public_path(
            'files/Client/'.$selected->file
        )
    ];


    $source = null;

    foreach ($possibleSources as $possibleSource) {

        if (
            is_file($possibleSource) &&
            is_readable($possibleSource)
        ) {
            $source = $possibleSource;
            break;
        }
    }


    if (!$source) {

        $stats['missing_file']++;

        fputcsv($report, [
            $client->id,
            $client->name,
            $client->created_at,
            $client->branch_name,
            'FILE_MISSING',
            $selected->name,
            $selected->file,
            '',
            '',
            count($matches)
        ]);

        continue;
    }


    /*
    |--------------------------------------------------------------------------
    | Destination Structure
    |--------------------------------------------------------------------------
    |
    | YYYY-MM
    |   DD
    |     Branch
    |
    */

    $date = Carbon::parse($client->created_at);

    $month = $date->format('Y-m');
    $day   = $date->format('d');

    $branch = safeName(
        $client->branch_name ?: 'بدون فرع'
    );

    $clientName = safeName(
        $client->name ?: 'بدون اسم'
    );

    $extension = strtolower(
        pathinfo(
            $selected->file,
            PATHINFO_EXTENSION
        )
    );

    if (!$extension) {
        $extension = 'file';
    }


    $newFileName =
        $client->id.
        ' - '.
        $clientName.
        ' - عقد الأتعاب.'.
        $extension;


    $destinationDir =
        $archiveRoot.
        '/'.
        $month.
        '/'.
        $day.
        '/'.
        $branch;


    $destination =
        $destinationDir.
        '/'.
        $newFileName;


    $stats['ready']++;


    /*
    |--------------------------------------------------------------------------
    | Copy only in EXECUTE mode
    |--------------------------------------------------------------------------
    */

    $status = count($matches) > 1
        ? 'READY_MULTIPLE_MATCHES'
        : 'READY';


    if ($execute) {

        if (!is_dir($destinationDir)) {
            mkdir(
                $destinationDir,
                0775,
                true
            );
        }


        if (copy($source, $destination)) {

            $stats['copied']++;

            $status = count($matches) > 1
                ? 'COPIED_MULTIPLE_MATCHES'
                : 'COPIED';

        } else {

            $stats['copy_failed']++;

            $status = 'COPY_FAILED';
        }
    }


    fputcsv($report, [
        $client->id,
        $client->name,
        $client->created_at,
        $client->branch_name,
        $status,
        $selected->name,
        $selected->file,
        $source,
        $execute ? $destination : '',
        count($matches)
    ]);
}


fclose($report);


/*
|--------------------------------------------------------------------------
| ZIP Archive
|--------------------------------------------------------------------------
*/

$zipPath = null;

if (
    $execute &&
    class_exists('ZipArchive')
) {

    $zipPath = $archiveRoot.'.zip';

    $zip = new ZipArchive();

    if (
        $zip->open(
            $zipPath,
            ZipArchive::CREATE |
            ZipArchive::OVERWRITE
        ) === true
    ) {

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $archiveRoot,
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {

            if (!$file->isDir()) {

                $filePath = $file->getRealPath();

                $relativePath = substr(
                    $filePath,
                    strlen($archiveRoot) + 1
                );

                $zip->addFile(
                    $filePath,
                    $relativePath
                );
            }
        }

        $zip->close();
    }
}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

echo PHP_EOL;
echo "============================================".PHP_EOL;
echo " SUMMARY".PHP_EOL;
echo "============================================".PHP_EOL;

echo "TOTAL CLIENTS      : ".$totalClients.PHP_EOL;
echo "READY              : ".$stats['ready'].PHP_EOL;
echo "NO CONTRACT        : ".$stats['missing_contract'].PHP_EOL;
echo "FILE MISSING       : ".$stats['missing_file'].PHP_EOL;
echo "MULTIPLE MATCHES   : ".$stats['multiple'].PHP_EOL;

if ($execute) {
    echo "COPIED             : ".$stats['copied'].PHP_EOL;
    echo "COPY FAILED        : ".$stats['copy_failed'].PHP_EOL;
}

echo PHP_EOL;
echo "REPORT: ".$reportPath.PHP_EOL;

if ($execute) {

    echo "ARCHIVE: ".$archiveRoot.PHP_EOL;

    if ($zipPath && file_exists($zipPath)) {
        echo "ZIP: ".$zipPath.PHP_EOL;
    }
}

echo PHP_EOL;

if (!$execute) {

    echo "DRY RUN ONLY - NO CONTRACT FILES WERE COPIED.".PHP_EOL;
    echo "Run again with --execute after reviewing the results.".PHP_EOL;
}

echo PHP_EOL;

