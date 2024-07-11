<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use NahidFerdous\Searchable\Searchable;

class Ticket extends Model
{
    use HasFactory, Searchable;
}
