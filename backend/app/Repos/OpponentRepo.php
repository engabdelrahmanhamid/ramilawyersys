<?php

namespace App\Repos;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OpponentRepo
{
    /**
     * Return a read-only, deduplicated list of opponents already used in cases.
     * No opponent records are created and no existing cases are modified.
     */
    public function get($filter)
    {
        $limit = (int) ($filter->limit ?: 10);
        $limit = max(1, min($limit, 100));

        $query = DB::table('user_cases')
            ->leftJoin('types', 'types.id', '=', 'user_cases.opponent_type_id')
            ->leftJoin('branches', 'branches.id', '=', 'user_cases.branch_id')
            ->leftJoin('clients', 'clients.id', '=', 'user_cases.client_id')
            ->whereNotNull('user_cases.opponent_name')
            ->whereRaw("TRIM(user_cases.opponent_name) <> ''");

        if ($filter->search_text) {
            $search = trim($filter->search_text);
            $query->where('user_cases.opponent_name', 'LIKE', '%' . $search . '%');
        }

        if ($filter->branch_id) {
            $query->where('user_cases.branch_id', $filter->branch_id);
        }

        if ($filter->opponent_type_id) {
            $query->where('user_cases.opponent_type_id', $filter->opponent_type_id);
        }

        /*
         * Keep the SQL intentionally simple. Some production MySQL/MariaDB
         * configurations reject the former grouped query when strict
         * ONLY_FULL_GROUP_BY mode is enabled. The case table is small enough
         * for this read-only preview, so normalisation and grouping are done
         * safely in Laravel instead.
         */
        $rows = $query
            ->select([
                'user_cases.id as case_id',
                'user_cases.opponent_name',
                'user_cases.opponent_type_id',
                'types.name as opponent_type_name',
                'user_cases.branch_id',
                'branches.name as branch_name',
                'user_cases.client_id',
                'clients.name as client_name',
                'user_cases.gregorian_date',
            ])
            ->orderByDesc('user_cases.id')
            ->get()
            ->filter(function ($row) {
                $name = trim((string) $row->opponent_name);

                if ($name === '') {
                    return false;
                }

                // Some old cases use a dash (or similar punctuation) instead
                // of an opponent name. Treat those values as unregistered so
                // their unrelated clients are never merged into one row.
                $meaningfulName = preg_replace('/[\s\p{Pd}_ـ.]+/u', '', $name);

                return $meaningfulName !== '';
            })
            ->values();

        $opponents = $rows
            ->groupBy(function ($row) {
                $name = trim((string) $row->opponent_name);
                $normalisedName = function_exists('mb_strtolower')
                    ? mb_strtolower($name, 'UTF-8')
                    : strtolower($name);

                return $normalisedName . '|' .
                    (int) $row->opponent_type_id . '|' .
                    (int) $row->branch_id;
            })
            ->map(function ($group) {
                // Rows are already ordered newest first.
                $latest = $group->first();

                return (object) [
                    'id' => (int) $group->min('case_id'),
                    'name' => trim((string) $latest->opponent_name),
                    'opponent_type_id' => $latest->opponent_type_id,
                    'opponent_type_name' => $latest->opponent_type_name,
                    'branch_id' => $latest->branch_id,
                    'branch_name' => $latest->branch_name,
                    'cases_count' => $group->count(),
                    'clients_count' => $group
                        ->pluck('client_id')
                        ->filter(function ($clientId) {
                            return $clientId !== null && $clientId !== '';
                        })
                        ->unique()
                        ->count(),
                    'client_names' => $group
                        ->pluck('client_name')
                        ->filter(function ($clientName) {
                            return $clientName !== null && trim((string) $clientName) !== '';
                        })
                        ->map(function ($clientName) {
                            return trim((string) $clientName);
                        })
                        ->unique()
                        ->values()
                        ->all(),
                    'latest_case_id' => (int) $latest->case_id,
                    'latest_case_date' => $latest->gregorian_date,
                ];
            })
            ->sortByDesc('latest_case_id')
            ->values();

        $page = max(1, (int) ($filter->page ?: 1));
        $items = $opponents
            ->slice(($page - 1) * $limit, $limit)
            ->values();

        return new LengthAwarePaginator(
            $items,
            $opponents->count(),
            $limit,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }
}
