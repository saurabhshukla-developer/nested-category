<?php

namespace Tests\Unit;

use App\Enums\CategoryStatus;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_root_category()
    {
        $category = Category::create([
            'name' => 'Bedroom',
            'status' => CategoryStatus::Enabled,
            'parent_id' => null,
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => $category->name,
            'status' => $category->status,
            'parent_id' => $category->parent_id,
        ]);
    }

    #[Test]
    public function it_can_create_a_child_category()
    {
        $parent = Category::create(['name' => 'Bedroom', 'status' => CategoryStatus::Enabled]);
        $child = Category::create(['name' => 'Beds', 'status' => CategoryStatus::Enabled, 'parent_id' => $parent->id]);

        $this->assertEquals($parent->id, $child->parent_id);
        $this->assertEquals('Bedroom > Beds', $child->getFullPath());
    }

    #[Test]
    public function it_returns_full_path_for_nested_categories()
    {
        $bedroom = Category::create(['name' => 'Bedroom', 'status' => CategoryStatus::Enabled]);
        $beds = Category::create(['name' => 'Beds', 'status' => CategoryStatus::Enabled, 'parent_id' => $bedroom->id]);
        $panelBed = Category::create(['name' => 'Panel Bed', 'status' => CategoryStatus::Enabled, 'parent_id' => $beds->id]);

        $this->assertEquals('Bedroom > Beds > Panel Bed', $panelBed->getFullPath());
    }

    #[Test]
    public function it_excludes_self_from_hierarchy_options()
    {
        $bedroom = Category::create(['name' => 'Bedroom', 'status' => CategoryStatus::Enabled]);
        $beds = Category::create(['name' => 'Beds', 'status' => CategoryStatus::Enabled, 'parent_id' => $bedroom->id]);

        $options = Category::hierarchyOptions($beds->id);

        $this->assertArrayNotHasKey($beds->id, $options);
        $this->assertArrayHasKey($bedroom->id, $options);
    }

    #[Test]
    public function deleting_a_category_moves_children_to_its_parent()
    {
        $root = Category::create(['name' => 'Bedroom', 'status' => CategoryStatus::Enabled]);
        $child = Category::create(['name' => 'Beds', 'status' => CategoryStatus::Enabled, 'parent_id' => $root->id]);
        $grandchild = Category::create(['name' => 'Panel Bed', 'status' => CategoryStatus::Enabled, 'parent_id' => $child->id]);

        $parentId = $child->getParentId();
        Category::where('parent_id', $child->id)->update(['parent_id' => $parentId]);
        $child->delete();

        $grandchild->refresh();
        $this->assertEquals($root->id, $grandchild->getParentId());
    }

    #[Test]
    public function it_prevents_setting_self_as_parent()
    {
        $category = Category::create(['name' => 'Bedroom', 'status' => CategoryStatus::Enabled]);
        $category->parent_id = $category->id;

        $this->expectException(InvalidArgumentException::class);
        $category->save();
    }

    #[Test]
    public function it_cannot_create_duplicate_name_under_same_parent()
    {
        $parent = Category::create(['name' => 'Bedroom', 'status' => CategoryStatus::Enabled]);
        Category::create(['name' => 'Beds', 'status' => CategoryStatus::Enabled, 'parent_id' => $parent->id]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Category::create(['name' => 'Beds', 'status' => CategoryStatus::Enabled, 'parent_id' => $parent->id]);
    }

    #[Test]
    public function it_can_create_same_name_under_different_parents()
    {
        $parent1 = Category::create(['name' => 'Bedroom', 'status' => CategoryStatus::Enabled]);
        $parent2 = Category::create(['name' => 'Living Room', 'status' => CategoryStatus::Enabled]);

        Category::create(['name' => 'Tables', 'status' => CategoryStatus::Enabled, 'parent_id' => $parent1->id]);
        Category::create(['name' => 'Tables', 'status' => CategoryStatus::Enabled, 'parent_id' => $parent2->id]);

        $this->assertDatabaseHas('categories', ['name' => 'Tables', 'parent_id' => $parent1->id]);
        $this->assertDatabaseHas('categories', ['name' => 'Tables', 'parent_id' => $parent2->id]);
    }

    #[Test]
    public function validation_fails_for_duplicate_name_under_same_parent()
    {
        $parent = Category::create(['name' => 'Bedroom', 'status' => CategoryStatus::Enabled]);
        Category::create(['name' => 'Beds', 'status' => CategoryStatus::Enabled, 'parent_id' => $parent->id]);

        $data = [
            'name' => 'Beds',
            'status' => CategoryStatus::Enabled->value,
            'parent_id' => $parent->id,
        ];

        $request = new StoreCategoryRequest;
        $request->merge($data);
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    #[Test]
    public function validation_passes_for_same_name_under_different_parents()
    {
        $parent1 = Category::create(['name' => 'Bedroom', 'status' => CategoryStatus::Enabled]);
        $parent2 = Category::create(['name' => 'Living Room', 'status' => CategoryStatus::Enabled]);

        Category::create(['name' => 'Tables', 'status' => CategoryStatus::Enabled, 'parent_id' => $parent1->id]);

        $data = [
            'name' => 'Tables',
            'status' => CategoryStatus::Enabled->value,
            'parent_id' => $parent2->id,
        ];

        $request = new StoreCategoryRequest;
        $request->merge($data);
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
    }
}
