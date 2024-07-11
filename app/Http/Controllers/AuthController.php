<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Mail\ForgotPasswordMail;
use App\Models\Admin;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * @throws ValidationException
     */
    public function login(LoginRequest $request): \Illuminate\Http\JsonResponse
    {
        $credentials = $request->validated();
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Incorrect credentials.'],
            ]);
        }
        $user = Auth::guard($this->guard)->user();
        if (!$user || !$user->status) {
            return $this->failure('Your account is inactive. Please contact the administrator.', 401);
        }

        $token = $user->createToken('user' . 'Token', ['check-' . ($user->role ?? 'user')]);

        return $this->success('Login Successful.', [
            'token' => $token->plainTextToken,
        ]);
    }

    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        auth()->user()->tokens()->delete();
        return $this->success('Successfully logged out.');
    }

    public function getAuthUser(): \Illuminate\Http\JsonResponse
    {
        $user = User::findOrFail(auth()->id());
        return $this->success('Success.', $user);
    }

    /**
     * @throws \Exception
     */
    public function forgotPassword(ForgetPasswordRequest $request): \Illuminate\Http\JsonResponse
    {
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return $this->success(__($status));
        } else {
            return $this->failure(__($status), 422);
        }
    }

    public function verifyForgotPasswordToken(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'token' => 'required|exists:password_reset_tokens,token',
        ]);

        $password_reset = DB::table('password_reset_tokens')->where('token', $request->token)->first();
        if ($password_reset === null)
            abort(400, 'Token not valid');

        if (Carbon::parse($password_reset->created_at)->addMinutes(720)->isPast()) {
            $password_reset->delete();
            abort(500, 'Password reset token is expired.');
        }

        return response()->json([
            'message' => 'Password reset token is valid.',
            'token' => $password_reset
        ], 200);
    }

    public function resetPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|different:previous_password|min:4|confirmed',
            'token' => 'required_without:previous_password',
            'previous_password' => 'required_without:token',
        ]);
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
                event(new PasswordReset($user));
            }
        );


        if ($status === Password::PASSWORD_RESET) {
            return $this->success(__($status));
        } else {
            return $this->failure(__($status), 422);
        }
    }
}
