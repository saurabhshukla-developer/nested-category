<?php

namespace Database\Seeders;

use App\Enums\CategoryStatus;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Bedroom hierarchy
        $bedroom = Category::create(['name' => 'Bedroom', 'status' => CategoryStatus::Enabled]);
        $beds = Category::create(['name' => 'Beds', 'status' => CategoryStatus::Enabled, 'parent_id' => $bedroom->id]);
        Category::create(['name' => 'Panel Bed', 'status' => CategoryStatus::Enabled, 'parent_id' => $beds->id]);
        Category::create(['name' => 'Night Stand', 'status' => CategoryStatus::Enabled, 'parent_id' => $bedroom->id]);
        Category::create(['name' => 'Dresser', 'status' => CategoryStatus::Disabled, 'parent_id' => $bedroom->id]);

        // Living Room hierarchy
        $livingRoom = Category::create(['name' => 'Living Room', 'status' => CategoryStatus::Enabled]);
        Category::create(['name' => 'Sofas', 'status' => CategoryStatus::Enabled, 'parent_id' => $livingRoom->id]);
        Category::create(['name' => 'Loveseats', 'status' => CategoryStatus::Enabled, 'parent_id' => $livingRoom->id]);
        $tables = Category::create(['name' => 'Tables', 'status' => CategoryStatus::Enabled, 'parent_id' => $livingRoom->id]);
        Category::create(['name' => 'Coffee Table', 'status' => CategoryStatus::Enabled, 'parent_id' => $tables->id]);
        Category::create(['name' => 'Side Table', 'status' => CategoryStatus::Disabled, 'parent_id' => $tables->id]);

        // Kitchen hierarchy
        $kitchen = Category::create(['name' => 'Kitchen', 'status' => CategoryStatus::Enabled]);
        Category::create(['name' => 'Appliances', 'status' => CategoryStatus::Enabled, 'parent_id' => $kitchen->id]);
        Category::create(['name' => 'Cookware', 'status' => CategoryStatus::Enabled, 'parent_id' => $kitchen->id]);
    }
}
