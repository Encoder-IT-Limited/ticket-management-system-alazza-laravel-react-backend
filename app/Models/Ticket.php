<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use NahidFerdous\Searchable\Searchable;

class Ticket extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'title',
        'description',
        'status',
        'client_id',
        'admin_id',
        'is_resolved',
        'resolved_at',
    ];

    public function casts()
    {
        return [
            'is_resolved' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = strtolower($value);
    }

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function admin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
