<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'parent_id', 'level'];

    public $timestamps = false;

    protected $casts = [
        'level' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function conditions(): BelongsToMany
    {
        return $this->belongsToMany(Condition::class, 'category_conditions');
    }

    // Recursive relationship to find root parent
    public function getRootParentAttribute()
    {
        $category = $this;
        while ($category->parent) {
            $category = $category->parent;
        }
        return $category;
    }
}
