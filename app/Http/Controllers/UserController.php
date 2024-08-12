<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Models\Services\UserService;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\CommonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Facades\CauserResolver;

class UserController extends Controller
{
    use ApiResponseTrait, CommonTrait;

    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(): \Illuminate\Http\JsonResponse
    {
        $users = $this->userService->getAll();
        return $this->success('Success', UserCollection::make($users));
    }

    public function store(UserStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            DB::beginTransaction();
            $user = $this->userService->store($request);

            CauserResolver::setCauser(auth()->user());
//            activity()
//                ->causedBy(auth()->user())
//                ->performedOn($user)
//                ->log('edited');
            DB::commit();
            return $this->success('User created successfully', new UserResource($user));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->failure($e->getMessage());
        }
    }

    public function update(UserUpdateRequest $request, User $user): \Illuminate\Http\JsonResponse
    {
        try {
            $user = $this->userService->update($request, $user);
            return $this->success('User updated successfully', new UserResource($user));
        } catch (\Exception $e) {
            return $this->failure($e->getMessage());
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'previous_password' => 'required:token',
            'password' => 'required|different:previous_password|min:4|confirmed',
        ]);
        if ($request->has('previous_password')) {
            $user = User::where('email', $request->email)->first();
            if (!Hash::check($request->previous_password, $user->password)) {
                return $this->failure('The provided password does not match your current password.', 422);
            }

            $user->update(['password' => Hash::make($request->password)]);
            return $this->success('Password updated successfully.');
        }
    }

    public function show(User $user): \Illuminate\Http\JsonResponse
    {
        $user->load('media');
        return $this->success('Success', new UserResource($user));
    }

    public function destroy(User $user): \Illuminate\Http\JsonResponse
    {
        try {
            $user->deleteAllMedia();
            $user->forceDelete();
            return $this->success('User deleted successfully');
        } catch (\Exception $e) {
            return $this->failure($e->getMessage());
        }
    }

    public function export(Request $request): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse|string
    {
        $request->validate([
            'ids' => 'sometimes|required|array',
            'format' => 'sometimes|required|in:excel,xlsx,csv,pdf',
        ]);
        $columns = ['id', 'name', 'email', 'created_at',];
        $headers = ['ID', 'Name', 'Email', 'Created At',];

        return $this->exportData(User::class, $columns, $headers, 'users');
    }
}
