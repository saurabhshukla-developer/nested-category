<?php

namespace App\Models;

use App\Enums\CategoryStatus;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'status', 'parent_id'];

    protected $casts = [
        'status' => CategoryStatus::class,
        'parent_id' => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): CategoryStatus
    {
        return $this->status;
    }

    public function getParentId(): ?int
    {
        return $this->parent_id;
    }

    public function getFullPath(): string
    {
        $categoryNames = collect([]);
        $currentCategory = $this;

        while ($currentCategory->parent) {
            $categoryNames->prepend($currentCategory->parent->getName());
            $currentCategory = $currentCategory->parent;
        }

        $categoryNames->push($this->getName());

        return $categoryNames->implode(' > ');
    }

    public static function hierarchyOptions($excludeCategoryId = null): array
    {
        $hierarchyOptions = [null => '— No Parent —'];

        $availableCategories = static::all()
            ->reject(fn ($category) => $category->id === $excludeCategoryId)
            ->mapWithKeys(function ($category) {
                $categoryPath = $category->getFullPath();

                return [$category->id => $categoryPath];
            })
            ->filter();

        return $hierarchyOptions + $availableCategories->sortBy(function ($categoryPath) {
            return strtolower($categoryPath);
        })->all();
    }

    protected static function booted()
    {
        static::saving(function ($category) {
            if ($category->getParentId() && $category->getParentId() == $category->id) {
                throw new \InvalidArgumentException('A category cannot be its own parent.');
            }
        });
    }

    public function getStatusLabel(): string
    {
        return match ($this->getStatus()) {
            CategoryStatus::Enabled => 'Enabled',
            CategoryStatus::Disabled => 'Disabled',
        };
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }
}
