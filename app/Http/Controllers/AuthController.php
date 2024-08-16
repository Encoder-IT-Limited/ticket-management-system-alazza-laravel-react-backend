<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Resources\User\UserResource;
use App\Models\EmailVerificationToken;
use App\Models\Services\MailService;
use App\Models\Services\UserService;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Facades\CauserResolver;

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
        $user = Auth::user();
        if (!$user || !$user->status) {
            return $this->failure('Your account is inactive. Please contact the administrator.', 401);
        }

        if ($user->email_verified_at === null) {
            $emailSent = (new MailService)->sendEmailVerificationMail(auth()->user());
            if ($emailSent) {
                return $this->failure('Please verify your email address. An Email verification request was sent to your email.', 401);
            }
            return $this->failure('Failed to send verification email.', 422);
        }

        $token = $user->createToken('user' . 'Token', ['check-' . ($user->role ?? 'user')]);

        return $this->success('Login Successful.', [
            'token' => $token->plainTextToken,
        ]);
    }

    public function register(UserStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            DB::beginTransaction();
            $data = $request->except('role');
            $data['password'] = Hash::make($data['password']);
            User::create($data);

            $credentials = $request->validated();
            if (!Auth::attempt($credentials)) {
                throw ValidationException::withMessages([
                    'email' => ['Incorrect credentials.'],
                ]);
            }
            $user = Auth::user();
            if (!$user || !$user->status) {
                return $this->failure('Your account is inactive. Please contact the administrator.', 401);
            }
            if ($user) {
                CauserResolver::setCauser($user);
            }
            $token = $user->createToken('user' . 'Token', ['check-' . ($user->role ?? 'user')]);

            $emailSent = (new MailService)->sendEmailVerificationMail(auth()->user());
            if ($emailSent) {
                return $this->success('Please verify your email address. An Email verification request was sent to your email.');
            }

            DB::commit();
            return $this->success('User created successfully', [
                'user' => new UserResource($user),
                'token' => $token->plainTextToken
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->failure($e->getMessage());
        }
    }

    public function sendVerificationEmail(): \Illuminate\Http\JsonResponse
    {
        $email = request('email');
        $user = User::where('email', $email)->firstOrFail();
        $emailSent = (new MailService)->sendEmailVerificationMail($user);
        if ($emailSent) {
            return $this->success('Verification email sent successfully.');
        }
        return $this->failure('Failed to send verification email.', 422);
    }

    public function verifyEmail(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
        ]);

        $email = request('email');
        $token = request('token');
        $emailToken = EmailVerificationToken::where('email', $email)->where('token', $token)->first();
        if ($emailToken === null) {
            return $this->failure('Email verification token is invalid.', 422);
        }

        if (Carbon::parse($emailToken->created_at)->addMinutes(120)->isPast()) {
            $emailToken->delete();
            return $this->failure('Email verification token has expired.', 422);
        }

        $user = User::where('email', $email)->first();
        $user->update(['email_verified_at' => now()]);
        $emailToken->delete();
        return $this->success('Email verified successfully.');
    }

    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
//        auth()->user()->tokens()->delete();
        $request->user()->tokens()->delete();
        return $this->success('Successfully logged out.');
    }

    public function getAuthUser(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = User::findOrFail($request->user()->id);
        return $this->success('Success.', new UserResource($user));
    }

    /**
     * @throws \Exception
     */
    public function forgotPassword(ForgetPasswordRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        if (isset($data['redirect_url'])) {
            config(['FRONTEND_URL' => $data['redirect_url']]);
        }
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
        if ($password_reset === null) {
            return $this->failure('Password reset token is invalid.', 422);
        }

        if (Carbon::parse($password_reset->created_at)->addMinutes(720)->isPast()) {
            $password_reset->delete();
            return $this->failure('Password reset token has expired.', 422);
        }

        return $this->success('Token is valid.');
    }

    public function resetPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|different:previous_password|min:4|confirmed',
            'token' => 'required_without:previous_password',
            'previous_password' => 'required_without:token',
        ]);
        if ($request->has('previous_password')) {

            if (auth()->check()) {
                CauserResolver::setCauser(auth()->user());
            }
            $user = User::where('email', $request->email)->first();
            if (auth()->check()) {
                activity()
                    ->performedOn($user)
                    ->causedBy(auth()->user())
                    ->log('edited');
            }
            if (!Hash::check($request->previous_password, $user->password)) {
                return $this->failure('The provided password does not match your current password.', 422);
            }

            $user->update(['password' => Hash::make($request->password)]);
            return $this->success('Password updated successfully.');
        }
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
