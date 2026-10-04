<?php

/**
 * Unified contract print batch collector.
 * Fixed list: 51 contract files.
 * - Jeddah excluded
 * - Previously printed clients excluded
 * - Cutoff: 2026-06-30
 * - Dry-run by default; use --execute to actually copy and zip.
 */

$options = getopt('', ['execute']);
$execute = array_key_exists('execute', $options);

$projectRoot = realpath(__DIR__ . '/..');
if (!$projectRoot) {
    fwrite(STDERR, "Could not resolve project root.\n");
    exit(1);
}

$items = json_decode(<<<'JSON'
[{"order":1,"client_id":14153,"client_name":"مروان مصطفى عبدالله الطوري","branch":"فرع المدينة المنورة","document_name":"عقد اتعاب مروان الطوري","stored_file":"7535816061775981623.pdf"},{"order":2,"client_id":14188,"client_name":"محمد  الهاجري","branch":"فرع الدمام","document_name":"عقد العميل","stored_file":"20804240321776159137.pdf"},{"order":3,"client_id":14444,"client_name":"صلاح الدين منير حسن يوسف","branch":"فرع  الرياض","document_name":"عقد اتعاب صلاح الدين منير حسن يوسف","stored_file":"9872217501778068811.pdf"},{"order":4,"client_id":14486,"client_name":"ريم فهاد مطلق السهلي","branch":"فرع  الرياض","document_name":"عقد الأتعاب","stored_file":"19467425981777442546.pdf"},{"order":5,"client_id":14489,"client_name":"مبارك محمد سبر العمور الدوسري","branch":"فرع  الرياض","document_name":"عقد الاتعاب","stored_file":"4110792011777786598.pdf"},{"order":6,"client_id":14490,"client_name":"ايهاب محمد سعيد خياط","branch":"فرع الدمام","document_name":"عقد العميل","stored_file":"15278037641777375440.pdf"},{"order":7,"client_id":14558,"client_name":"عبدالقوي سالم محمد","branch":"فرع الدمام","document_name":"عقد العميل","stored_file":"12738630441777788635.pdf"},{"order":8,"client_id":14563,"client_name":"خالد محمد حسين السلاطين","branch":"فرع الدمام","document_name":"عقد العميل","stored_file":"15490164051786871764.pdf"},{"order":9,"client_id":14682,"client_name":"ابراهيم سعد عبدالله بن شقير","branch":"فرع  الرياض","document_name":"عقد اتعاب-ابراهيم سعد عبدالله بن شقير(سب بالواتساب)","stored_file":"16719075381778408563.pdf"},{"order":10,"client_id":14682,"client_name":"ابراهيم سعد عبدالله بن شقير","branch":"فرع  الرياض","document_name":"عقد اتعاب-ابراهيم سعد عبدالله بن شقير(قذف في الجلسة)","stored_file":"14806349401778408581.pdf"},{"order":11,"client_id":14687,"client_name":"فهد صالح علي الغامدي","branch":"فرع  الرياض","document_name":"عقد اتعاب-فهد الغامدي","stored_file":"7162633651778656843.pdf"},{"order":12,"client_id":14714,"client_name":"حمد محمد صديق حكمي","branch":"فرع  الرياض","document_name":"عقد اتعاب","stored_file":"9139412371784813247.pdf"},{"order":13,"client_id":14722,"client_name":"سمر عزت محمد","branch":"فرع  الرياض","document_name":"عقد اتعاب (الحضانة)","stored_file":"12086356101786003250.pdf"},{"order":14,"client_id":14722,"client_name":"سمر عزت محمد","branch":"فرع  الرياض","document_name":"عقد اتعاب (فسخ)","stored_file":"10023733241786003324.pdf"},{"order":15,"client_id":14748,"client_name":"علي محمد بن صالح","branch":"فرع  الرياض","document_name":"عقد اتعاب-شركة بوبه","stored_file":"15779894611778589112.pdf"},{"order":16,"client_id":14748,"client_name":"علي محمد بن صالح","branch":"فرع  الرياض","document_name":"مقدم اتعاب العقد رقم 14748-1","stored_file":"9955385141778589018.pdf"},{"order":17,"client_id":14759,"client_name":"محمد علي حسين ال ريمان","branch":"فرع  الرياض","document_name":"عقد اتعاب-علي مانع حسين ال ريمان","stored_file":"20651837861778761732.pdf"},{"order":18,"client_id":14855,"client_name":"عبده محمد الفقيه","branch":"فرع المدينة المنورة","document_name":"العقد","stored_file":"1458249201788091208.pdf"},{"order":19,"client_id":14907,"client_name":"حسين محمد حسين ال شرمة","branch":"فرع  الرياض","document_name":"عقد اتعاب-حسين محمد حسين ال شرمه","stored_file":"15969143271779093915.pdf"},{"order":20,"client_id":15037,"client_name":"رانيا مشعل سليمان الهمزاني","branch":"فرع  الرياض","document_name":"عقد الاتعاب","stored_file":"5772759131788262123.pdf"},{"order":21,"client_id":15049,"client_name":"ايمان محمد بوشعيب","branch":"فرع الدمام","document_name":"عقد العميل","stored_file":"3502967931780819183.pdf"},{"order":22,"client_id":15054,"client_name":"مارية برناوي","branch":"فرع المدينة المنورة","document_name":"العقد","stored_file":"21175186911782037835.pdf"},{"order":23,"client_id":15142,"client_name":"مصطفى سعود سنوسي محروس","branch":"فرع الدمام","document_name":"عقد العميل","stored_file":"7326723091781179872.pdf"},{"order":24,"client_id":15149,"client_name":"عبدالمجيد محمد سفير المطيري","branch":"فرع  الرياض","document_name":"عقد اتعاب","stored_file":"16876387201782050475.pdf"},{"order":25,"client_id":15149,"client_name":"عبدالمجيد محمد سفير المطيري","branch":"فرع  الرياض","document_name":"عقد اتعاب 3","stored_file":"4838705911785745910.pdf"},{"order":26,"client_id":15149,"client_name":"عبدالمجيد محمد سفير المطيري","branch":"فرع  الرياض","document_name":"عقد اتعاب2","stored_file":"20100581441783424544.pdf"},{"order":27,"client_id":15196,"client_name":"موسى جعفر صافي الصبحي","branch":"فرع المدينة المنورة","document_name":"عقد اتعاب موسى الصبحي.pdf","stored_file":"5665640811781421530.pdf"},{"order":28,"client_id":15223,"client_name":"علي جهاد محمد المرزوق","branch":"فرع الدمام","document_name":"عقد العميل","stored_file":"8018344871783240747.pdf"},{"order":29,"client_id":15246,"client_name":"علي حامد هشبول الغامدي","branch":"فرع الدمام","document_name":"عقد العميل","stored_file":"11083477651781522867.pdf"},{"order":30,"client_id":15253,"client_name":"نواف تركي ناصر ال سلطان","branch":"فرع  الرياض","document_name":"عقد اتعاب-نواف تركي ناصر ال سلطان","stored_file":"17269196731781505130.pdf"},{"order":31,"client_id":15261,"client_name":"محمد عواض موسم المطيري","branch":"فرع  الرياض","document_name":"عقد اتعاب","stored_file":"1754792371782815117.pdf"},{"order":32,"client_id":15261,"client_name":"محمد عواض موسم المطيري","branch":"فرع  الرياض","document_name":"عقد اتعاب 2","stored_file":"15337074491787647677.pdf"},{"order":33,"client_id":15264,"client_name":"عبدالعزيز عبدالله مهدي العلي","branch":"فرع الدمام","document_name":"عقد العميل","stored_file":"12270130381785154455.pdf"},{"order":34,"client_id":15278,"client_name":"سمر جلال عبدالخالق","branch":"فرع  الرياض","document_name":"عقد اتعاب","stored_file":"15651177991782050516.pdf"},{"order":35,"client_id":15279,"client_name":"جواهر سمحان فهد اللحيان","branch":"فرع  الرياض","document_name":"عقد اتعاب","stored_file":"4298557431781699727.pdf"},{"order":36,"client_id":15311,"client_name":"عيسى جهيمان سويكت الرشيدي","branch":"فرع المدينة المنورة","document_name":"العقد","stored_file":"16852628121781759175.pdf"},{"order":37,"client_id":15313,"client_name":"عبدالله علي عبد المحسن الحمود","branch":"فرع  الرياض","document_name":"عقد اتعاب","stored_file":"16048704421781705951.pdf"},{"order":38,"client_id":15324,"client_name":"رزان عبدالمحسن براك الجيعان","branch":"فرع الدمام","document_name":"عقد العميل","stored_file":"13973237241782302266.pdf"},{"order":39,"client_id":15335,"client_name":"اماني حمد صالح الخربوش","branch":"فرع  الرياض","document_name":"عقد اتعاب","stored_file":"8278592101781782446.pdf"},{"order":40,"client_id":15350,"client_name":"خديجة عبدالصمد محمد الكاتب","branch":"فرع المدينة المنورة","document_name":"عقد الاتعاب","stored_file":"6963098551782042625.pdf"},{"order":41,"client_id":15355,"client_name":"سلمان علي سعيد الشهراني","branch":"فرع  الرياض","document_name":"عقد اتعاب","stored_file":"19137331681782304834.pdf"},{"order":42,"client_id":15362,"client_name":"نائل يوسف اسماعيل العميرة","branch":"فرع  الرياض","document_name":"عقد اتعاب","stored_file":"440804431782736758.pdf"},{"order":43,"client_id":15378,"client_name":"مريم دبيان عوض المطيري","branch":"فرع  الرياض","document_name":"عقد اتعاب","stored_file":"14339727041782133434.pdf"},{"order":44,"client_id":15385,"client_name":"سعد عمر طلال محلاوي","branch":"فرع المدينة المنورة","document_name":"العقد","stored_file":"20965638301782133804.pdf"},{"order":45,"client_id":15387,"client_name":"رياض سعيد محمد القحطاني","branch":"فرع المدينة المنورة","document_name":"عقد اتعاب رياض القحطاني١.pdf","stored_file":"13401505461782628825.pdf"},{"order":46,"client_id":15397,"client_name":"رامي ماجد عبدالله جواد","branch":"فرع الدمام","document_name":"عقد العميل","stored_file":"16392093711782198303.pdf"},{"order":47,"client_id":15401,"client_name":"محمد اسماعيل ابراهيم الجاسر","branch":"فرع  الرياض","document_name":"عقد اتعاب","stored_file":"15239005651782278757.pdf"},{"order":48,"client_id":15449,"client_name":"فاطمة سعد عبدالرحمن السبيعي","branch":"فرع الدمام","document_name":"عقد العميل","stored_file":"13557379681782303900.pdf"},{"order":49,"client_id":15472,"client_name":"حسن علي محمد البو موزه","branch":"فرع الدمام","document_name":"عقد العميل","stored_file":"2131178801782391913.pdf"},{"order":50,"client_id":15517,"client_name":"فهد حسين فهد القحطاني","branch":"فرع  الرياض","document_name":"عقد اتعاب","stored_file":"962286291782726331.pdf"},{"order":51,"client_id":15527,"client_name":"وائل ناصر خليفة محمد","branch":"فرع الدمام","document_name":"عقد العميل","stored_file":"17779433321782732332.pdf"}]
JSON
, true);

if (!is_array($items)) {
    fwrite(STDERR, "Could not load fixed print manifest.\n");
    exit(1);
}

function safeName($text)
{
    $text = trim((string) $text);
    $text = preg_replace('/[\/\\\\:*?"<>|]+/u', '-', $text);
    $text = preg_replace('/\s+/u', ' ', $text);
    $text = trim($text, " .");

    if (function_exists('mb_substr')) {
        $text = mb_substr($text, 0, 90, 'UTF-8');
    } else {
        $text = substr($text, 0, 90);
    }

    return $text !== '' ? $text : 'بدون اسم';
}

function ensureDir($dir)
{
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException("Could not create directory: ".$dir);
    }
}

$timestamp = date('Ymd_His');
$batchName = 'print_batch_until_20260630_no_jeddah_'.$timestamp;
$archiveRoot = $projectRoot.'/storage/app/'.$batchName;
$zipPath = $archiveRoot.'.zip';

echo PHP_EOL;
echo "====================================================".PHP_EOL;
echo " UNIFIED CONTRACT PRINT BATCH".PHP_EOL;
echo "====================================================".PHP_EOL;
echo "MODE               : ".($execute ? 'EXECUTE' : 'DRY RUN').PHP_EOL;
echo "FILES IN MANIFEST  : ".count($items).PHP_EOL;
echo "JEDDAH              : EXCLUDED".PHP_EOL;
echo "PREVIOUSLY PRINTED  : EXCLUDED".PHP_EOL;
echo "CUTOFF              : 2026-06-30".PHP_EOL;
echo PHP_EOL;

$ready = [];
$missing = [];

foreach ($items as $item) {
    $storedFile = basename((string) $item['stored_file']);
    $source = $projectRoot.'/images/Client/'.$storedFile;

    if (is_file($source) && is_readable($source)) {
        $item['source'] = $source;
        $ready[] = $item;
    } else {
        $item['source'] = $source;
        $missing[] = $item;
    }
}

echo "READY              : ".count($ready).PHP_EOL;
echo "MISSING            : ".count($missing).PHP_EOL;

if (!empty($missing)) {
    echo PHP_EOL."===== MISSING FILES =====".PHP_EOL;
    foreach ($missing as $item) {
        echo
            $item['order']." | ".
            $item['client_id']." | ".
            $item['client_name']." | ".
            $item['stored_file'].
            PHP_EOL;
    }
}

if (!$execute) {
    echo PHP_EOL;
    echo "DRY RUN ONLY - NO FILES WERE COPIED.".PHP_EOL;
    echo "If READY is 51 and MISSING is 0, run again with --execute.".PHP_EOL;
    echo PHP_EOL;
    exit(0);
}

if (count($ready) !== count($items)) {
    fwrite(STDERR, PHP_EOL."Execution stopped because one or more source files are missing.".PHP_EOL);
    exit(2);
}

ensureDir($archiveRoot);

$manifestPath = $archiveRoot.'/manifest.csv';
$manifest = fopen($manifestPath, 'w');
if (!$manifest) {
    throw new RuntimeException("Could not create manifest.csv");
}

fwrite($manifest, "\xEF\xBB\xBF");
fputcsv($manifest, [
    'Print Order',
    'Client ID',
    'Client Name',
    'Branch',
    'Document Name',
    'Original File',
    'Print File'
]);

$copied = 0;
$copyFailed = 0;

foreach ($ready as $item) {
    $extension = strtolower(pathinfo($item['stored_file'], PATHINFO_EXTENSION));
    if ($extension === '') {
        $extension = 'pdf';
    }

    $printFileName =
        str_pad((string) $item['order'], 3, '0', STR_PAD_LEFT).
        ' - '.
        $item['client_id'].
        ' - '.
        safeName($item['client_name']).
        ' - '.
        safeName($item['document_name']).
        '.'.
        $extension;

    $destination = $archiveRoot.'/'.$printFileName;

    if (copy($item['source'], $destination)) {
        $copied++;
        fputcsv($manifest, [
            $item['order'],
            $item['client_id'],
            $item['client_name'],
            $item['branch'],
            $item['document_name'],
            $item['stored_file'],
            $printFileName
        ]);
    } else {
        $copyFailed++;
    }
}

fclose($manifest);

if ($copyFailed > 0) {
    echo PHP_EOL;
    echo "COPIED             : ".$copied.PHP_EOL;
    echo "COPY FAILED        : ".$copyFailed.PHP_EOL;
    echo "ZIP                : NOT CREATED".PHP_EOL;
    exit(3);
}

if (!class_exists('ZipArchive')) {
    echo PHP_EOL;
    echo "COPIED             : ".$copied.PHP_EOL;
    echo "COPY FAILED        : 0".PHP_EOL;
    echo "ARCHIVE ROOT       : ".$archiveRoot.PHP_EOL;
    echo "ZIP                : NOT CREATED (ZipArchive unavailable)".PHP_EOL;
    exit(0);
}

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    throw new RuntimeException("Could not create ZIP: ".$zipPath);
}

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($archiveRoot, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $file) {
    if ($file->isFile()) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($archiveRoot) + 1);
        $zip->addFile($filePath, $relativePath);
    }
}

$zip->close();

echo PHP_EOL;
echo "====================================================".PHP_EOL;
echo " SUMMARY".PHP_EOL;
echo "====================================================".PHP_EOL;
echo "FILES REQUESTED    : ".count($items).PHP_EOL;
echo "COPIED             : ".$copied.PHP_EOL;
echo "COPY FAILED        : ".$copyFailed.PHP_EOL;
echo "ARCHIVE ROOT       : ".$archiveRoot.PHP_EOL;
echo "ZIP                : ".$zipPath.PHP_EOL;
echo PHP_EOL;
