
@extends('layouts.main')


@section('content')

    @if (Session::has('error_message'))
        <div class="alert alert-danger" role="alert">
            {!! Session::get('error_message') !!}
        </div>
    @endif


    @if($threads->count() > 0)

        @foreach($threads as $thread)

            <?php $class = $thread->isUnread($currentUserId) ? 'alert-info' : ''; ?>

            <div class="media alert {!!$class!!}" style="justify-content: space-between;">

                {!! link_to_route('messages.show', 'Read', [$thread->id], ['class' => 'btn btn-success'] ) !!}

                <h4 class="media-heading">{!! link_to('messages/' . $thread->id, $thread->subject) !!}</h4>

                <div>{!! $thread->latestMessage ? $thread->latestMessage->body : 'msg body missing' !!}</div>

                <p><small><strong>Creator:</strong> {!! $thread->creator()->first_name.' '.$thread->creator()->last_name !!}</small></p>

                <p><small><strong>Participants:</strong> {!! $thread->participantsString( Auth::id() ) !!}</small></p>

                @if ($thread->creator()->id == Auth::user()->id)
                    {!! link_to_route('messages.delete', 'Delete!', [$thread->messages->first()->id], ['class' => 'btn btn-outline-danger'] ) !!}
                @endif


            </div>

            <hr>

        @endforeach

    @else
        <p>No messages.</p>
        <a href="{{ url('messages/create') }}"><i class="fa fa-envelope-o" aria-hidden="true"></i> Create a new message.</a>
    @endif

@stop