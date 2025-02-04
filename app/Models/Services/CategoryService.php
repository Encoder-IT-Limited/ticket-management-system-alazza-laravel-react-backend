<?php

namespace App\Models\Services;

use App\Models\Category;
use App\Models\Ticket;

class CategoryService
{
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return Category::with('children')->whereNull('parent_id')->get();
    }

    public function store($request): Category
    {
        $data = $request->validated();
        $category = new Category();
        $category->fill($data);
        $category->save();

        return $category;
    }

    public function update($request, $category)
    {
        $data = $request->validated();
        $category->fill($data);
        $category->save();

        return $category;
    }
}
