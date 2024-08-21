<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Ticket Open Mail</title>
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/2.4.85/css/materialdesignicons.min.css">
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

    #reviewBox {
        max-width: 300px;
        width: 100%;
    }

    .flex {
        display: flex;
    }

    .items-center {
        align-items: center;
    }

    .justify-between {
        justify-content: space-between;
    }

    .flex-col {
        flex-direction: column;
    }

    textarea {
        width: 100%;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #e0e0e0;
        margin-bottom: 1rem;
        height: 100px;
    }

    h1, h2, h3, h4, h5, h6 {
        margin: 0;
        font-weight: lighter;
    }

    /* Base style for emojis */
    .emoji {
        transition: transform 0.3s ease;
    }

    /* Style for the emoji when the radio button is checked */
    #rating1:checked + .emoji,
    #rating1:checked + .emoji-text {
        transform: scale(1.2);
        color: red; /* You can change this to any color or style you like */
    }

    #rating2:checked + .emoji,
    #rating2:checked + .emoji-text {
        transform: scale(1.2);
        color: gray; /* You can change this to any color or style you like */
    }

    #rating3:checked + .emoji,
    #rating3:checked + .emoji-text {
        transform: scale(1.2);
        color: green; /* You can change this to any color or style you like */
    }

    .reactions {
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .reactions:hover .emoji {
        transition: all 0.3s ease;
        transform: scale(1.2);
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
            @php
                $resolvedByName = $ticket?->resolvedBy?->name;
                $resolvedAt = \Carbon\Carbon::parse($ticket->resolved_at)->format('d-m-Y h:i A');
                $ticketNo = $ticket->ticket_no;
            @endphp

            <small>
                @if($user->role === 'client')
                    @if($user->id == $ticket->resolved_by)
                        You closed a ticket you requested
                    @else
                        Your ticket was closed by <strong>"{{ $resolvedByName }}"</strong>
                    @endif
                @elseif($user->role === 'admin')
                    @if($user->id == $ticket->resolved_by)
                        You closed a ticket requested by <strong>"{{ $ticket?->client?->name }}"</strong>
                    @elseif($user->id == $ticket->admin_id)
                        A ticket has been closed by <strong>"{{ $resolvedByName }}"</strong>
                    @else
                        Ticket closed by <strong>"{{ $resolvedByName }}"</strong>
                    @endif
                @else
                    Ticket closed by <strong>"{{ $resolvedByName }}"</strong>
                @endif
                at <strong>{{ $resolvedAt }}</strong>. <strong>Ticket no: {{ $ticketNo }}.</strong>
            </small>
            <div>
                <small>Ticket Details: </small>
            </div>
            <h3>{{$ticket->title}}</h3>
            <p>{{$ticket->description}}</p>
            <div>Status:
                @if(lcfirst($ticket->status) =='open')
                    <span style="color: green">Open</span>
                @elseif(lcfirst($ticket->status) =='close')
                    <span style="color: red">Close</span>
                @else
                    {{ $ticket->status }}
                @endif
            </div>

            <div class="main-container">
                <h3>How was your Experience with us?</h3>
                <small style="margin-bottom: 1rem">Please, take a minute to give your review.</small>
                <form action="{{ route('ticket-reviews.store' ) }}" id="reviewBox">
                    @csrf
                    @method('POST')
                    <div class="flex items-center justify-between" style="margin-bottom: 1rem">
                        <label class="flex items-center flex-col reactions">
                            <input type="radio" id="rating1" name="rating" value="1" hidden required>
                            <span class="emoji" id="emoji1" style="font-size: 30px">🙁</span>
                            <small class="emoji-text">Unhappy</small>
                        </label>
                        <label class="flex items-center flex-col reactions">
                            <input type="radio" id="rating2" name="rating" value="2" hidden required>
                            <span class="emoji" id="emoji2" style="font-size: 30px">😐</span>
                            <small class="emoji-text">Neutral</small>
                        </label>
                        <label class="flex items-center flex-col reactions">
                            <input type="radio" id="rating3" name="rating" value="3" hidden required>
                            <span class="emoji" id="emoji3" style="font-size: 30px">😃</span>
                            <small class="emoji-text">Happy</small>
                        </label>
                    </div>

                    <div>
                        <textarea name="review"></textarea>
                    </div>
                    <div>
                        <button class="btn" type="submit">Submit</button>
                    </div>
                </form>
            </div>

            <div>
                <span>Request Time:</span>
                <strong>{{ \Carbon\Carbon::parse($ticket->create_at)->format('d-m-Y h:i A')}}</strong>
            </div>
            <div>
                <span>Resolved Time:</span>
                <strong>{{ \Carbon\Carbon::parse($ticket->resolved_at)->format('d-m-Y h:i A')}}</strong>
            </div>

            <a href="{{ config('app.frontend_url') }}/tickets/{{$ticket->id}}" class="btn">
                View Ticket
            </a>
        </div>
    </div>
</div>
</body>
</html>
