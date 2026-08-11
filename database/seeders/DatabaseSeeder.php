<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use \App\Modules\HR\database\seeders\TerRateSeeder;
use \App\Modules\HR\database\seeders\BpjsRateSeeder;
use \App\Modules\HR\database\seeders\PayrollComponentSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            TerRateSeeder::class,
            BpjsRateSeeder::class,
            PayrollComponentSeeder::class,
        ]);
    }
}
