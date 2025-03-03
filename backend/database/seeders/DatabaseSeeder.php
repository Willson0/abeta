<?php

namespace Database\Seeders;

use App\Models\Analytic;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Webinar;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Webinar::factory(30)->create();
        Analytic::factory(10)->create();
    }
}
