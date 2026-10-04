<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

$options = getopt('', ['from:', 'to:']);

$from = isset($options['from'])
    ? Carbon::parse($options['from'])->startOfDay()
    : Carbon::parse('2026-05-01')->startOfDay();

$to = isset($options['to'])
    ? Carbon::parse($options['to'])->endOfDay()
    : Carbon::parse('2026-07-31')->endOfDay();

echo PHP_EOL;
echo "====================================================".PHP_EOL;
echo " CONTRACT ELIGIBILITY DIAGNOSTIC".PHP_EOL;
echo "====================================================".PHP_EOL;
echo "FROM : ".$from->format('Y-m-d H:i:s').PHP_EOL;
echo "TO   : ".$to->format('Y-m-d H:i:s').PHP_EOL.PHP_EOL;

$baseClients = DB::table('clients')
    ->whereBetween('clients.created_at', [$from, $to]);

echo "TOTAL CLIENTS: ".$baseClients->count().PHP_EOL.PHP_EOL;

/*
|--------------------------------------------------------------------------
| Client statuses
|--------------------------------------------------------------------------
*/
echo "===== CLIENT STATUSES =====".PHP_EOL;

$statuses = DB::table('clients')
    ->leftJoin('client_statuses', 'clients.status_id', '=', 'client_statuses.id')
    ->whereBetween('clients.created_at', [$from, $to])
    ->select(
        'clients.status_id',
        'client_statuses.name as status_name',
        DB::raw('COUNT(*) as total'),
        DB::raw("SUM(CASE WHEN EXISTS (
            SELECT 1 FROM client_files cf WHERE cf.client_id = clients.id
        ) THEN 1 ELSE 0 END) as with_any_file"),
        DB::raw("SUM(CASE WHEN NOT EXISTS (
            SELECT 1 FROM client_files cf WHERE cf.client_id = clients.id
        ) THEN 1 ELSE 0 END) as no_files"),
        DB::raw("SUM(CASE WHEN EXISTS (
            SELECT 1 FROM user_cases uc WHERE uc.client_id = clients.id
        ) THEN 1 ELSE 0 END) as with_case"),
        DB::raw("SUM(CASE WHEN EXISTS (
            SELECT 1 FROM services s WHERE s.client_id = clients.id
        ) THEN 1 ELSE 0 END) as with_service")
    )
    ->groupBy('clients.status_id', 'client_statuses.name')
    ->orderByDesc('total')
    ->get();

foreach ($statuses as $row) {
    echo
        "STATUS ".$row->status_id.
        " | ".($row->status_name ?: '[NO STATUS NAME]').
        " | total=".$row->total.
        " | any_file=".$row->with_any_file.
        " | no_files=".$row->no_files.
        " | case=".$row->with_case.
        " | service=".$row->with_service.
        PHP_EOL;
}

/*
|--------------------------------------------------------------------------
| Client types
|--------------------------------------------------------------------------
*/
echo PHP_EOL."===== CLIENT TYPES =====".PHP_EOL;

$types = DB::table('clients')
    ->leftJoin('types', 'clients.type_id', '=', 'types.id')
    ->whereBetween('clients.created_at', [$from, $to])
    ->select(
        'clients.type_id',
        'types.name as type_name',
        DB::raw('COUNT(*) as total'),
        DB::raw("SUM(CASE WHEN EXISTS (
            SELECT 1 FROM client_files cf WHERE cf.client_id = clients.id
        ) THEN 1 ELSE 0 END) as with_any_file"),
        DB::raw("SUM(CASE WHEN EXISTS (
            SELECT 1 FROM user_cases uc WHERE uc.client_id = clients.id
        ) THEN 1 ELSE 0 END) as with_case"),
        DB::raw("SUM(CASE WHEN EXISTS (
            SELECT 1 FROM services s WHERE s.client_id = clients.id
        ) THEN 1 ELSE 0 END) as with_service")
    )
    ->groupBy('clients.type_id', 'types.name')
    ->orderByDesc('total')
    ->get();

foreach ($types as $row) {
    echo
        "TYPE ".$row->type_id.
        " | ".($row->type_name ?: '[NO TYPE NAME]').
        " | total=".$row->total.
        " | any_file=".$row->with_any_file.
        " | case=".$row->with_case.
        " | service=".$row->with_service.
        PHP_EOL;
}

/*
|--------------------------------------------------------------------------
| Strong signal: client has a case/service but no client files
|--------------------------------------------------------------------------
*/
echo PHP_EOL."===== CLIENTS WITH CASE/SERVICE BUT NO CLIENT FILES =====".PHP_EOL;

$strongMissing = DB::table('clients')
    ->leftJoin('branches', 'clients.branch_id', '=', 'branches.id')
    ->leftJoin('client_statuses', 'clients.status_id', '=', 'client_statuses.id')
    ->whereBetween('clients.created_at', [$from, $to])
    ->whereNotExists(function ($q) {
        $q->select(DB::raw(1))
            ->from('client_files')
            ->whereColumn('client_files.client_id', 'clients.id');
    })
    ->where(function ($q) {
        $q->whereExists(function ($sub) {
            $sub->select(DB::raw(1))
                ->from('user_cases')
                ->whereColumn('user_cases.client_id', 'clients.id');
        })->orWhereExists(function ($sub) {
            $sub->select(DB::raw(1))
                ->from('services')
                ->whereColumn('services.client_id', 'clients.id');
        });
    })
    ->select(
        'clients.id',
        'clients.name',
        'clients.created_at',
        'branches.name as branch_name',
        'client_statuses.name as status_name'
    )
    ->orderBy('clients.created_at')
    ->get();

echo "COUNT: ".$strongMissing->count().PHP_EOL;

foreach ($strongMissing->take(100) as $row) {
    echo
        $row->id." | ".
        $row->name." | ".
        $row->created_at." | ".
        ($row->branch_name ?: '[NO BRANCH]')." | ".
        ($row->status_name ?: '[NO STATUS]').
        PHP_EOL;
}

if ($strongMissing->count() > 100) {
    echo "... showing first 100 only".PHP_EOL;
}

echo PHP_EOL."DONE".PHP_EOL;
