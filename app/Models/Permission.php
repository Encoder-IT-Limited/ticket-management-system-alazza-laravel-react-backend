<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category'
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($permission) {
            if (empty($permission->slug)) {
                $permission->slug = Str::slug($permission->name);
            }
        });

        static::updating(function ($permission) {
            if ($permission->isDirty('name') && empty($permission->slug)) {
                $permission->slug = Str::slug($permission->name);
            }
        });
    }
}
