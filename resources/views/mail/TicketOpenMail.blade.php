<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Ticket Open Mail</title>
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
        height: auto;
        width: 200px;
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
            {{--            <img class="logoHead" src="{{url('/images/logo.webp')}}" alt="">--}}
            <img class="logoHead" src="https://alazzaz.tech/logo.png" alt="">
        </div>
        <div>
            <h2>Dear {{$user?->name}},</h2>
            <small>A new ticket is opened by <strong>"{{$ticket->client->name}}"</strong>
                at <strong>{{ \Carbon\Carbon::parse($ticket->create_at)->format('d-m-Y h:i A')}}</strong>.
                <strong>Ticket no: {{ $ticket->ticket_no }}.</strong>
            </small>
            <div>
                <small>Ticket Details: </small>
            </div>
            <h3>
                {{$ticket->title}}
            </h3>
            <p>{{$ticket->description}}</p>
            <div>Status:
                @if($ticket->status === 'open')
                    <span style="color: green">Open</span>
                @else
                    <span style="color: red">{{ ucfirst($ticket->status) }}</span>
                @endif
            </div>
            <div>Time:
                <strong>{{ \Carbon\Carbon::parse($ticket->create_at)->format('d-m-Y h:i A')}}</strong>
            </div>

            <a href="{{ config('app.frontend_url') }}/tickets/{{$ticket->id}}" class="btn">
                View Ticket
            </a>
        </div>
    </div>
</div>
</body>
</html>
