<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'parent_id'];

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function tickets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get all descendant category IDs (children at all nested levels).
     */
    public static function getDescendantIds(int $categoryId): array
    {
        $allIds = [];
        $currentLevel = [$categoryId];

        while (!empty($currentLevel)) {
            $children = self::whereIn('parent_id', $currentLevel)->pluck('id')->toArray();
            if (empty($children)) {
                break;
            }
            $allIds = array_values(array_unique(array_merge($allIds, $children)));
            $currentLevel = $children;
        }

        return $allIds;
    }
}
