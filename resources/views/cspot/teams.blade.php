
<!-- # (C) 2016 Matthias Kuhs, Ireland -->

@extends('layouts.main')

@section('title', 'Team Management')

@section('plans', 'active')


@section('content')


    <?php 
        // create shortcut to a complex question....
        $userIsAuthorized = Auth::user()->isEditor() || Auth::user()->ownsPlan($plan->id);
    ?>

    @include('layouts.flashing')

    @if (  $plan->teams->count() > 0  &&  $userIsAuthorized  )  
        <button onclick="$('#new-member-form').toggle();" class="float-right ml-1">Add new team member</button>
    @endif
    

    <h4>Team for 
        <a href="{{ url('cspot/plans/'.$plan->id) }}">
            "{{ $plan->type->name }}" on 
            <span class="hidden-md-down">{{ $plan->date->formatLocalized('%A, %d %B %Y') }}</span>
            <span class="hidden-lg-up"  >{{ $plan->date->formatLocalized('%a, %d %B')    }}</span>
        </a>
    </h4>


    <!-- 
        show the current team members and their roles 
    -->
    <table class="table table-striped table-bordered 
                @if(count($plan->teams)>15)
                 table-sm
                @endif
                 ">
        <thead class="thead-default">
            <tr>
                <th>Name</th>
                <th>Role</th>
                <th class="hidden-sm-down">Comment/Instruments</th>
                <th>Available?</th>
                @if( $userIsAuthorized )
                <th>Requested?</th>
                @endif
                <th>Confirmed?</th>
                
                @if( $userIsAuthorized )
                <th>Action</th>
                @endif
            </tr>
        </thead>

        <tbody>
        @foreach( $plan->teams as $team )
            @if ($team->user)
                <tr class="{{ ($team->confirmed) ? 'bg-success' : 'bg-info' }}">

                    <!-- row NAME -->
                    <td scope="row">{{ $team->user->name }}</td>

                    <!-- row ROLE -->
                    <td
                        @if( $userIsAuthorized )
                            class="link" onclick="location.href='{{ url('cspot/plans/'.$team->plan_id.'/team/'.$team->id) }}/edit'"
                        @endif
                        >
                        {{ $team->role ? ucfirst($team->role->name) : '(tbd)' }}</td>

                    <!-- row COMMENT -->
                    <td 
                        @if( $userIsAuthorized )
                            class="link hidden-sm-down" onclick="location.href='{{ url('cspot/plans/'.$team->plan_id.'/team/'.$team->id) }}/edit'"
                        @endif
                        class="hidden-sm-down">{{ $team->comment }}
                        @if ($team->user->instruments->count())
                            @foreach ($team->user->instruments as $key => $instrument)
                                {{  $instrument->name }}{{ ($key==$team->user->instruments->count()-1) ? '' : ', ' }}
                            @endforeach
                        @endif
                    </td>

                    <!-- row AVAILABLE? -->
                    <td>
                        @if ( Auth::user()->id == $team->user_id  &&  ! $team->confirmed )
                            <a class="btn btn-secondary btn-sm float-sm-right" data-available="{{ $team->available ? 'true' : 'false' }}"
                               title="Click if you are {{ $team->available ? 'NOT' : '' }} available for this Service" 
                                href="#" onclick='userAvailableForPlan(this, {{ $team->plan_id }})'>
                                {!! ($team->available) ? '<i class="fa fa-frown-o"></i> I\'m unavailable' : '<i class="fa fa-smile-o"></i> I\'m available' !!}
                                <i class="fa fa-square-o"></i></a>
                        @endif
                        <i class="fa fa-{{ ($team->available) ? 'check-square' : 'minus-square-o' }} fa-big"> </i>
                        {{ ($team->available) ? '(available)' : '(unavailable)' }}
                    </td>

                    <!-- row REQUESTED? -->
                    @if( $userIsAuthorized )
                    <td>
                            <a class="btn btn-secondary btn-sm float-sm-right" title="Send request to user" 
                                onclick="$('#show-spinner').modal({keyboard: false});" 
                                href='{{ url('cspot/plans/'.$team->plan_id.'/team/'.$team->id) }}/sendrequest'><i class="fa fa-envelope-o"></i></a>
                        <i class="fa fa-{{ ($team->requested) ? 'check-square' : 'minus-square-o' }}"> </i> 
                    </td>
                    @endif

                    <!-- row CONFIRMED? -->
                    <td>
                        @if ( Auth::user()->id == $team->user_id )
                            <a class="btn btn-secondary btn-sm float-sm-right" title="Confirm/Decline" 
                                onclick="$('#show-spinner').modal({keyboard: false});" 
                                href='{{ url('cspot/plans/'.$team->plan_id.'/team/'.$team->id) }}/confirm'>
                                {{ ($team->confirmed) ? 'Decline' : 'Confirm' }}:
                                <i class="fa fa-square-o"></i></a>
                        @endif
                        <i class="fa fa-{{ ($team->confirmed) ? 'check-square' : 'minus-square-o' }} fa-big"> </i>
                        {{ ($team->confirmed) ? '(confirmed)' : '(unconfirmed)' }}
                    </td>

                    <!-- row ACTIONS -->
                    @if( $userIsAuthorized )
                    <td class="nowrap">
                            <a class="btn btn-outline-primary btn-sm" title="Edit" 
                                onclick="$('#show-spinner').modal({keyboard: false});" 
                                href='{{ url('cspot/plans/'.$team->plan_id.'/team/'.$team->id) }}/edit'><i class="fa fa-pencil"></i></a>
                            <a class="btn btn-danger btn-sm" title="Delete!" 
                                onclick="$('#show-spinner').modal({keyboard: false});" 
                                href='{{ url('cspot/plans/'.$team->plan_id.'/team/'.$team->id) }}/delete'><i class="fa fa-trash"></i></a>
                    </td>
                    @endif

                </tr>
            @else
                <tr><td colspan="7">User with id {{$team->user_id }} is lost!</td></tr>
            @endif

        @endforeach
        </tbody>

    </table>



    @if ( $plan->date > \Carbon\Carbon::yesterday() )

        <!-- 
            show form to enter a new team member 
        -->
        @if ( $plan->teams->count() == 0 )
            <h5>Add a new team member:</h5>
        @endif

        <form id="new-member-form" method="POST" 
            @if ( $plan->teams->count() > 0 )
                style="display: none"
            @endif
            >

            <input type="hidden" name="_method" value="POST">
            {{ csrf_field() }}

            <label class="form-control-label">1. Select a User:</label>
            <select name="user_id" class="form-control text-help c-select" onchange="showRoleSelect(this)">
                <option selected>
                    Select ...
                </option>
                @foreach ($users as $user)
                    @if ( ! $user->hasRole('retired') && $user->roles->count() > 0  )
                    <option 
                        @if(  ''<>old('user_id') && $user->id==old('user_id') )  
                                selected
                        @endif
                        value="{{ $user->id }}">{{ $user->name }}
                    </option>
                    @endif
                @endforeach
            </select>
            @if ($errors->has('user_id'))
                <br><span class="help-block">
                    <strong>{{ $errors->first('user_id') }}</strong>
                </span>
            @endif

            <div class="ml-2" id="show-instruments"></div>

            <div id="select-team-role" style="display: none">
                <label class="form-control-label">2. Select a role (one at a time):
                    <div class="c-inputs-stacked ml-1" id="select-role-box"></div>
                </label>
            </div>

            <label class="form-control-label" id="comment-input" style="display: none">
                3. Add a comment (optional):
                <input type="text" name="comment">
            </label>
            
            <div class="form-control-label">
                <button type="submit" id="submit-button" style="display: none">Submit</button>
            </div>

        </form>

    @endif

    <script>
        var userRolesJSON  = '{!! $userRoles !!}';
        var userRolesArray = JSON.parse(userRolesJSON);
    </script>


@stop