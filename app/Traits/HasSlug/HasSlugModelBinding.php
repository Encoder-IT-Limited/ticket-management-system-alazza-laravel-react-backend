<?php

namespace App\Traits\HasSlug;

use App\Traits\HasSlug\HasSlug;
use App\Traits\HasSlug\SlugOptions;
use Illuminate\Database\Eloquent\Model;

trait HasSlugModelBinding
{
    use HasSlug;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->replaceRouteKey(true, true);
    }
}
