<?php

namespace App\Models\Services;

use App\Models\Category;
use App\Models\Ticket;

class CategoryService
{
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return Category
            ::with('children.children')
            ->whereNull('parent_id')
            ->get();
    }

    public function store($request): Category
    {
        $data = $request->validated();
        $category = new Category();
        $category->fill($data);
        if ($request->get('parent', null)) {
            $category->parent_id = $request->get('parent')['id'];
        }
        $category->save();

        if ($request->get('children', [])) {
            $ids = array_column($request->get('children'), 'id');
            $ids = array_filter($ids, function ($id) use ($category) {
                return $id != $category->id;
            });
            Category::whereIn('id', $ids)->update(['parent_id' => $category->id]);
        }
        return $category;
    }

    public function update($request, $category)
    {
        $data = $request->validated();
        $category->fill($data);
        $category->save();

        if ($request->get('parent', null)) {
            $category->parent_id = $request->get('parent')['id'];
            $category->save();
        }

        if ($request->get('children', [])) {
            $ids = array_column($request->get('children'), 'id');
            $ids = array_filter($ids, function ($id) use ($category) {
                return $id != $category->id;
            });
            Category::where('parent_id', $category->id)->update(['parent_id' => null]);
            Category::whereIn('id', $ids)->update(['parent_id' => $category->id]);
        }
        return $category;
    }
}
