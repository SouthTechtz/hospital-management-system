<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use \Illuminate\Database\Console\Seeds\WithoutModelEvents;

    /**
     * Seed the reference data the application needs to run locally.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            FacilityDefaultsSeeder::class,
            Icd10Seeder::class,
            DrugSeeder::class,
            TariffSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}