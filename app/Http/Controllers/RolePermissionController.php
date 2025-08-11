<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RolePermissionController extends Controller
{
    public function getRole(){
        $roles = Role::with('permissions')->orderBy('id','asc')->get();

        return response()->json([
            'roles' => $roles
        ], 200);
    }

    public function createOrUpdateRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'permissions' => 'required|array',
            'permissions.*' => 'required|integer|exists:permissions,id'
        ]);

        $role = Role::firstOrCreate(['name' => $request->name]);
        $role->permissions()->sync($request->permissions);

        $role->load('permissions');

        return response()->json([
            'role' => $role
        ], 201);
    }

    public function getPermission()
    {
        $permissions = Permission::orderBy('id','asc')->groupBy('category')->get();

        return response()->json([
            'permissions' => $permissions
        ], 200);
    }

    public function createPermission(Request $request){
        $request->validate([
            'name' => 'required|string|unique',
            'category' => 'required|string'
        ]);

        $permission = Permission::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'category' => $request->category
        ]);


        return response()->json([
            'permission' => $permission
        ], 201);

    }
}
