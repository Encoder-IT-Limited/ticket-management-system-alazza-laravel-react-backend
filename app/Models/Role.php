<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name'];

    public function user()
    {
        return $this->hasOne(User::class, 'role_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission', 'role_id', 'permission_id');
    }

    public function models()
    {
        return $this->hasMany(RoleModel::class, 'role_id');
    }

    public function categories()
    {
        return $this
            ->hasManyThrough(
                Category::class,
                RoleModel::class,
                'role_id',
                'id',
                'id',
                'model_id'
            )
            ->where('model_type', Category::class)
            ->select('categories.id', 'categories.name')
            ->with(['children' => function ($q) {
                $q->select('id', 'parent_id')
                    ->with(['children' => function ($q) {
                        $q->select('id', 'parent_id');
                    }]);
            }]);
    }

    /**
     * Sync categories to the RoleModel morph table
     *
     * @param array $modelIds
     * @return void
     */
    public function syncModel(array $modelIds, string $modelType): void
    {
        // Delete existing category RoleModel records for this role
        $this->models()
            ->where('model_type', $modelType)
            ->delete();

        // Create new RoleModel records for each category
        $data = array_map(function ($modelId) use ($modelType) {
            return [
                'role_id' => $this->id,
                'model_id' => $modelId,
                'model_type' => $modelType,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $modelIds);

        if (!empty($data)) {
            RoleModel::insert($data);
        }
    }
}
