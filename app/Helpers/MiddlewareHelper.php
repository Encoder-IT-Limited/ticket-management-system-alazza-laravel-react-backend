<?php

use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;


function middlewareGenerator($model): array
{
    return [
        'auth',
        new Middleware(["permission:view $model"], only: ['index']),
        new Middleware(["permission:show $model"], only: ['show']),
        new Middleware(["permission:create $model"], only: ['store']),
        new Middleware(["permission:update $model"], only: ['update']),
        new Middleware(["permission:delete $model"], only: ['destroy', 'bulkDestroy']),
        new Middleware(["permission:restore $model"], only: ['restore', 'bulkRestore']),
        new Middleware(["permission:force delete $model"], only: ['forceDelete', 'bulkForceDelete']),
    ];
}

function failureResponse($message, $status = 400): \Illuminate\Http\JsonResponse
{
    return response()->json(['success' => false, 'message' => $message, 'status' => $status], $status);
}

//'view ' . $key,
//            'show ' . $key,
//            'create ' . $key,
//            'update ' . $key,
//            'delete ' . $key,
//
//            'view any ' . $key,
//            'show any ' . $key,
//            'update any ' . $key,
//            'delete any ' . $key,

//[
//    'role_or_permission:manager|edit articles',
//    new Middleware('role:author', only: ['index']),
//    new Middleware(RoleMiddleware::using('manager'), except:['show']),
//    new Middleware(PermissionMiddleware::using('delete records,api'), only:['destroy']),
//];

function isAuthorised($guard = '', $permission = ''): bool
{
    if ($guard) {
        return auth($guard)->check() && auth($guard)->user()->can($permission);
    }
    return auth()->check() && auth()->user()->can($permission);
}
