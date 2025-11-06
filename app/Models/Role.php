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
            ->select('categories.id')
            ->with(['children' => function ($q) {
                $q->select('id', 'parent_id')
                    ->with(['children' => function ($q) {
                        $q->select('id', 'parent_id');
                    }]);
            }]);
    }
}
