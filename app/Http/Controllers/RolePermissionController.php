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

    public function getPermission(Request $request)
    {
        $search = $request->get('search', '');
        $per_page = $request->get('per_page', 10);

        $query = Permission::query();
        if($search){
            $query
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('category', 'like', '%'.$search.'%');
        }
        $permissions = $query
            ->orderBy('created_at','desc')
            ->paginate($per_page);

        return response()->json([
            'permissions' => $permissions
        ], 200);
    }

    public function createPermission(Request $request){
        $request->validate([
            'id' => 'sometimes|integer|exists:permissions,id',
            'name' => 'required|string|unique:permissions,name,'.$request->id,
            'category' => 'required|string'
        ]);
        if($request->id){
            $permission = Permission::find($request->id);
            $permission->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'category' => $request->category
            ]);
            return response()->json([
                'permission' => $permission
            ], 200);
        }
        $permission = Permission::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'category' => $request->category
        ]);
        return response()->json([
            'permission' => $permission
        ], 201);
    }

    public function deletePermission(Request $request, $id){
        Permission::find($request->id)->delete();
        return response()->json([
            'message' => 'Permission deleted successfully'
        ], 204);
    }
}
