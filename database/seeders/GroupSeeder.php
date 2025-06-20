<?php

namespace Database\Seeders;

use App\Models\group;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        group::firstOrCreate(
            ['name' => 'A'],
            ['description' => 'A']
        );
    }
}
