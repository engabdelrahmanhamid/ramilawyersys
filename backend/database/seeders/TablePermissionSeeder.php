<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TablePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'Administrator',
            'Lawyer',
            'Office Manager',
            'Case Manager',
            'Office Staff',
        ];

        $timestamp = Carbon::now();

        $data = array_map(function ($permission) use ($timestamp) {
            return [
                'name' => $permission,
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ];
        }, $permissions);

        DB::table('permissions')->insert($data);
    }
}
