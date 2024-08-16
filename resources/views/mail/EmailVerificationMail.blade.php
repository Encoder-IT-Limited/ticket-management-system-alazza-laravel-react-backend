<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Email Verification Mail</title>
</head>

<style>
    .mailContainer {
        width: 100%;
        max-width: 800px;
        min-width: 260px;
        margin: 20px auto;
        padding: 20px;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
    }

    .logoHead {
        text-align: center;
        margin: 0 auto;
        height: 100px;
    }

    .logoContainer {
        text-align: center;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    body {
        color: #4b4b4c;
        font-family: sans-serif;
    }

    .btn {
        display: inline-block;
        padding: 10px 20px;
        background-color: #4CAF50;
        color: white;
        text-align: center;
        text-decoration: none;
        font-size: 16px;
        margin: 10px auto 4px auto;
        cursor: pointer;
        border-radius: 5px;
    }
</style>

<body>
<div>
    <div class="mailContainer">
        <div class="logoContainer">
            {{-- <img class="logoHead" src="{{url('/images/logo.webp')}}" alt="">--}}
            <img class="logoHead" src="https://alazzaz.tech/logo.png" alt="" height="160px">
        </div>
        <div>
            <h2>Dear {{$user?->name}},</h2>

            <p>
                Please click the button below to verify your email address. If you did not create an account, no further action is required.
            </p>
            <p>
                <strong>Email:</strong> {{$user->email}}
            </p>
            <p>
                Verification link will expire in 2 hours.
            </p>

            {{--            <a href="{{ route('verify-email', ['email'=>$user->email, 'token'=> $emailToken->token]) }}" class="btn">--}}
            <a href="{{ config('app.frontend_url') }}/email-verify?email={{$user->email}}&token={{$emailToken->token}}" class="btn">
                Verify Email
            </a>
        </div>
    </div>
</div>
</body>
</html>
