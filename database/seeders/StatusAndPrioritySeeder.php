<?php

// StatusAndPrioritySeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusAndPrioritySeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \App\Models\Status::truncate();
        \App\Models\Priority::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        \App\Models\Status::create(['name' => 'New', 'color' => '#FFEB3B']);
        \App\Models\Status::create(['name' => 'In Progress', 'color' => '#2196F3']);
        \App\Models\Status::create(['name' => 'Done', 'color' => '#4CAF50']);
        \App\Models\Status::create(['name' => 'Blocked', 'color' => '#F44336']);

        \App\Models\Priority::create(['name' => 'Low', 'color' => '#4CAF50']);
        \App\Models\Priority::create(['name' => 'Medium', 'color' => '#FFC107']);
        \App\Models\Priority::create(['name' => 'High', 'color' => '#FF9800']);
        \App\Models\Priority::create(['name' => 'Urgent', 'color' => '#F44336']);

        $this->command->info('Statuses and Priorities have been seeded!');
    }
}