<?php

namespace Tests\Feature;

use App\Enums\CategoryStatus;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryUITest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function categories_index_page_shows_all_categories()
    {
        $parent = Category::create(['name' => 'Bedroom', 'status' => CategoryStatus::Enabled]);
        $child = Category::create(['name' => 'Beds', 'status' => CategoryStatus::Enabled, 'parent_id' => $parent->id]);

        $response = $this->get('/categories');

        $response->assertStatus(200);
        $response->assertSee('Bedroom');
        $response->assertSee('Beds');
        $response->assertSee('Enabled');
    }

    #[Test]
    public function category_list_shows_hierarchy_correctly()
    {
        $parent = Category::create(['name' => 'Bedroom', 'status' => CategoryStatus::Enabled]);
        $child = Category::create(['name' => 'Beds', 'status' => CategoryStatus::Enabled, 'parent_id' => $parent->id]);
        $grandchild = Category::create(['name' => 'Panel Bed', 'status' => CategoryStatus::Enabled, 'parent_id' => $child->id]);

        $response = $this->get('/categories');

        $response->assertStatus(200);
        $response->assertSee('Bedroom');
        $response->assertSee('Beds');
        $response->assertSee('Panel Bed');
        $response->assertSee('Bedroom > Beds > Panel Bed');
    }

    #[Test]
    public function category_list_shows_status_badges()
    {
        Category::create(['name' => 'Enabled Category', 'status' => CategoryStatus::Enabled]);
        Category::create(['name' => 'Disabled Category', 'status' => CategoryStatus::Disabled]);

        $response = $this->get('/categories');

        $response->assertStatus(200);
        $response->assertSee('Enabled Category');
        $response->assertSee('Disabled Category');
        $response->assertSee('bg-success', false);
        $response->assertSee('bg-secondary', false);
    }

    #[Test]
    public function category_list_shows_no_records_message()
    {
        $response = $this->get('/categories');

        $response->assertStatus(200);
        $response->assertSee('No categories found');
        $response->assertSee('bi-inbox', false);
    }

    #[Test]
    public function category_list_shows_pagination()
    {
        for ($i = 1; $i <= 15; $i++) {
            Category::create(['name' => "Category {$i}", 'status' => CategoryStatus::Enabled]);
        }

        $response = $this->get('/categories');

        $response->assertStatus(200);
        $response->assertSee('Next');
        $response->assertSee('Previous');
        $response->assertSee('?page=2', false);
    }

    #[Test]
    public function category_list_shows_action_buttons()
    {
        $category = Category::create(['name' => 'Test Category', 'status' => CategoryStatus::Enabled]);

        $response = $this->get('/categories');

        $response->assertStatus(200);
        $response->assertSee('Edit');
        $response->assertSee('Delete');
        $response->assertSee('bi-pencil', false);
        $response->assertSee('bi-trash', false);
        $response->assertSee('data-bs-toggle="modal"', false);
        $response->assertSee('data-bs-target="#categoryModal"', false);
        $response->assertSee('data-bs-target="#deleteModal"', false);
    }

    #[Test]
    public function pagination_shows_correct_number_of_items()
    {
        for ($i = 1; $i <= 15; $i++) {
            Category::create(['name' => "Category {$i}", 'status' => CategoryStatus::Enabled]);
        }

        $response = $this->get('/categories');
        $response->assertStatus(200);
        $response->assertViewHas('categories', function ($categories) {
            return $categories->count() === 10;
        });

        $response = $this->get('/categories?page=2');
        $response->assertStatus(200);
        $response->assertViewHas('categories', function ($categories) {
            return $categories->count() === 5;
        });
    }
}
