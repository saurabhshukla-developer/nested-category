<?php

namespace Tests\Feature;

use App\Enums\CategoryStatus;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_view_categories_list()
    {
        $response = $this->get('/categories');
        $response->assertStatus(200);
        $response->assertViewIs('categories.index');
    }

    #[Test]
    public function can_create_and_edit_category_with_parent()
    {
        $parent = Category::create(['name' => 'Bedroom', 'status' => CategoryStatus::Enabled]);

        $response = $this->post('/categories', [
            'name' => 'Beds',
            'status' => CategoryStatus::Enabled->value,
            'parent_id' => $parent->id,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Category created successfully']);
        $this->assertDatabaseHas('categories', ['name' => 'Beds', 'parent_id' => $parent->id]);

        $category = Category::where('name', 'Beds')->first();
        $response = $this->put("/categories/{$category->id}", [
            'name' => 'Beds Updated',
            'status' => CategoryStatus::Disabled->value,
            'parent_id' => null,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Category updated successfully']);
        $this->assertDatabaseHas('categories', [
            'name' => 'Beds Updated',
            'parent_id' => null,
            'status' => CategoryStatus::Disabled->value,
        ]);
    }

    #[Test]
    public function can_delete_category_and_children_are_reassigned()
    {
        $root = Category::create(['name' => 'Bedroom', 'status' => CategoryStatus::Enabled]);
        $child = Category::create(['name' => 'Beds', 'status' => CategoryStatus::Enabled, 'parent_id' => $root->id]);
        $grandchild = Category::create(['name' => 'Panel Bed', 'status' => CategoryStatus::Enabled, 'parent_id' => $child->id]);

        $response = $this->delete("/categories/{$child->id}");
        $response->assertRedirect('/categories');
        $response->assertSessionHas('success', 'Category deleted successfully');

        $grandchild->refresh();
        $this->assertEquals($root->id, $grandchild->parent_id);
    }

    #[Test]
    public function categories_are_paginated()
    {
        // Create 15 categories
        for ($i = 1; $i <= 15; $i++) {
            Category::create(['name' => "Category {$i}", 'status' => CategoryStatus::Enabled]);
        }

        $response = $this->get('/categories');
        $response->assertStatus(200);

        // First page should show 10 items
        $response->assertViewHas('categories', function ($categories) {
            return $categories->count() === 10;
        });

        // Check second page
        $response = $this->get('/categories?page=2');
        $response->assertStatus(200);
        $response->assertViewHas('categories', function ($categories) {
            return $categories->count() === 5;
        });
    }

    #[Test]
    public function categories_are_ordered_by_name()
    {
        Category::create(['name' => 'Zebra', 'status' => CategoryStatus::Enabled]);
        Category::create(['name' => 'Apple', 'status' => CategoryStatus::Enabled]);
        Category::create(['name' => 'Banana', 'status' => CategoryStatus::Enabled]);

        $response = $this->get('/categories');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['Apple', 'Banana', 'Zebra']);
    }

    #[Test]
    public function category_creation_validates_required_fields()
    {
        $response = $this->postJson('/categories', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'name' => 'The name field is required.',
            'status' => 'The status field is required.',
        ]);
    }

    #[Test]
    public function category_creation_validates_name_length()
    {
        $response = $this->postJson('/categories', [
            'name' => str_repeat('a', 256),
            'status' => CategoryStatus::Enabled->value,
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'name' => 'The name field must not be greater than 255 characters.',
        ]);
    }

    #[Test]
    public function category_creation_validates_status_enum()
    {
        $response = $this->postJson('/categories', [
            'name' => 'Test Category',
            'status' => 'invalid_status',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'status' => 'The selected status is invalid.',
        ]);
    }

    #[Test]
    public function category_creation_validates_parent_exists()
    {
        $response = $this->postJson('/categories', [
            'name' => 'Test Category',
            'status' => CategoryStatus::Enabled->value,
            'parent_id' => 999999,
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'parent_id' => 'The selected parent id is invalid.',
        ]);
    }

    #[Test]
    public function category_update_validates_unique_name_under_same_parent()
    {
        $parent = Category::create(['name' => 'Bedroom', 'status' => CategoryStatus::Enabled]);
        Category::create(['name' => 'Beds', 'status' => CategoryStatus::Enabled, 'parent_id' => $parent->id]);
        $category = Category::create(['name' => 'Chairs', 'status' => CategoryStatus::Enabled, 'parent_id' => $parent->id]);

        $response = $this->putJson("/categories/{$category->id}", [
            'name' => 'Beds',
            'status' => CategoryStatus::Enabled->value,
            'parent_id' => $parent->id,
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'name' => 'The name has already been taken.',
        ]);
    }

    #[Test]
    public function category_cannot_be_its_own_parent()
    {
        $category = Category::create(['name' => 'Bedroom', 'status' => CategoryStatus::Enabled]);

        $response = $this->putJson("/categories/{$category->id}", [
            'name' => 'Bedroom',
            'status' => CategoryStatus::Enabled->value,
            'parent_id' => $category->id,
        ]);
        $response->assertStatus(422);
        $response->assertJson(['errors' => ['parent_id' => ['Category cannot be its own parent']]]);
    }

    #[Test]
    public function category_cannot_have_circular_reference()
    {
        $parent = Category::create(['name' => 'Bedroom', 'status' => CategoryStatus::Enabled]);
        $child = Category::create(['name' => 'Beds', 'status' => CategoryStatus::Enabled, 'parent_id' => $parent->id]);

        $response = $this->putJson("/categories/{$parent->id}", [
            'name' => 'Bedroom',
            'status' => CategoryStatus::Enabled->value,
            'parent_id' => $child->id,
        ]);
        $response->assertStatus(422);
        $response->assertJson(['errors' => ['parent_id' => ['Cannot assign a descendant as parent']]]);
    }
}
