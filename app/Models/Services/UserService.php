<?php

namespace App\Models\Services;

use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Mail\CreateUserMail;
use App\Mail\TicketCloseMail;
use App\Models\EmailVerificationToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserService
{
    public function getAll()
    {
        $query = request('search_query');
        return User::whereAny(['name', 'email'], 'like', "%$query%")
            ->with('media')
            ->latest()
            ->paginate(request('per_page', 25));
    }

    public function store($request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        $this->uploadFiles($request, $user);
        $user->load('media');

        $emailToken = null;
        if ($user && $user->email_verified_at === null) {
            EmailVerificationToken::where('email', $user->email)->delete();
            $emailToken = EmailVerificationToken::create([
                'email' => $user->email,
                'token' => $token = sha1(time() . Str::random(10)),
            ]);
        }
        Mail::to($user->email)->queue(new CreateUserMail($user, $data['password'], $emailToken));

        return $user;
    }

    public function update($request, $user)
    {
        $data = $request->validated();
        $user->update($data);
        $this->uploadFiles($request, $user);
        $user->load('media');

        return $user;
    }

    protected function uploadFiles($request, $model): void
    {
        if ($request->has('user_id_documents')) {
            foreach ($request->user_id_documents as $key => $document) {
                $model->uploadMedia($document, 'user_id_document_' . $key, 'user_id_document');
            }
        }

        if ($request->has('device_licenses')) {
            foreach ($request->device_licenses as $key => $document) {
                $model->uploadMedia($document, 'device_license_' . $key, 'device_license');
            }
        }

        if ($request->has('other_documents')) {
            foreach ($request->other_documents as $key => $document) {
                $model->uploadMedia($document, 'other_document_' . $key, 'other_document');
            }
        }
    }
}
