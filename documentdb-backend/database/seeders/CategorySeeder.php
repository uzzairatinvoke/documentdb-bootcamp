<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Policies', 'Reports', 'Contracts', 'Training'] as $name) {
            Category::query()->updateOrCreate(['name' => $name]);
        }
    }
}
