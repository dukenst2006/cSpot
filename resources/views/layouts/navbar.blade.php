
<nav id="main-navbar" class="navbar navbar-toggleable-sm navbar-inverse bg-primary mb-2 py-0">

    <button class="navbar-toggler navbar-toggler-right" type="button" 
        data-toggle="collapse" data-target="#navbarSupportedContent" 
        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    
    {{--________________________________________________________

                    LEFT - Home button
        ________________________________________________________
    --}}
    <a class="navbar-brand" href="{{ Auth::guest() ? url('.') : url('home') }}">
        <img src="{{ url('images/xs-cspot.png') }}" height="20" width="30"></a>



<div class="collapse navbar-collapse" id="navbarSupportedContent">


    {{--________________________________________________________

                    LEFT - Main menu items
        ________________________________________________________
    --}}
    <ul class="navbar-nav mt-2">

        @if (Auth::user())

            <li class="nav-item dropdown{{ Request::is('cspot/plans') || Request::is('cspot/songs') || Request::is('cspot/history') || Request::is('admin/types') ? ' active' : '' }}">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                    Planning <span class="caret"></span>
                </a>
                <div class="dropdown-menu" role="menu">
                    <a class="dropdown-item {{ Request::is('cspot/plans/next') ? 'active' : '' }}" href="{{ url('cspot/plans/next') }}">
                        <i class="fa fa-btn fa-bell-o fa-lg"></i> &nbsp; Next Sunday</a>
                    <a class="dropdown-item" href="{{ url('cspot/plans/calendar') }}">
                        <i class="fa fa-btn fa-calendar fa-lg"></i> &nbsp; Event Calendar</a>
                    <a class="dropdown-item" href="{{ url('cspot/plans?filterby=future') }}">
                        <i class="fa fa-btn fa-calendar fa-lg"></i> &nbsp; Upcoming Event List</a>

                    <hr>
                    <a class="dropdown-item {{ Request::is('cspot/plans') ? 'active' : '' }}" href="{{ url('cspot/plans') }}">
                        <i class="fa fa-btn fa-calendar-check-o fa-lg"></i> &nbsp; Your Events</a>
                    @if( Auth::user()->isEditor() )
                    <a class="dropdown-item" href="{{ url('cspot/plans/create') }}">
                        <i class="fa fa-btn fa-calendar-plus-o fa-lg"></i> &nbsp; Create New Event</a>
                    @endif
                    <a class="dropdown-item" href="{{ url('admin/types') }}">
                        <i class="fa fa-btn fa-tasks fa-lg"></i> &nbsp; Event Types</a>
                    <a class="dropdown-item" href="{{ url('admin/default_items') }}">
                        <i class="fa fa-btn fa-server fa-lg"></i> &nbsp; Default Items</a>

                    <hr>
                    <a class="dropdown-item" href="{{ url('cspot/songs') }}">
                        <i class="fa fa-btn fa-music fa-lg"> </i> &nbsp;&nbsp; Songs</a>
                    <a class="dropdown-item" href="{{ url('cspot/songs') }}?filterby=title_2&filtervalue=video">
                        <i class="fa fa-btn fa-tv fa-lg"></i> &nbsp; Videoclips</a>
                    <a class="dropdown-item" href="{{ url('cspot/songs') }}?filterby=title_2&filtervalue=slides">
                        <i class="fa fa-btn fa-clone fa-lg"></i> &nbsp; Slides</a>
                    <a class="dropdown-item" href="{{ url('admin/bibles') }}">
                        <i class="fa fa-btn fa-book fa-lg"></i> &nbsp; Scripture</a>
                    @if (Auth::user()->isEditor())
                        <a class="dropdown-item" href="{{ url('admin/song_parts') }}">
                            <i class="fa fa-btn fa-server fa-lg"></i> &nbsp; Song Parts Names</a>
                        <hr class="mb-0">
                        <a class="dropdown-item" href="{{ url('cspot/history') }}">
                            <span class="xl-big">&#9991;</span> &nbsp; Event Plan History</a>
                    @endif
                </div>
            </li>
    
            <li class="nav-item hidden-sm-down">
                <a class="nav-link {{ Request::is('cspot/plans/next') ? 'active' : '' }}" href="{{ url('cspot/plans/next') }}">Next Sunday</a>
            </li>


            <li class="nav-item hidden-sm-down">
                <a class="nav-link{{ Request::is('cspot/plans/calendar') ? ' active' : '' }}" 
                    href="{{ url('cspot/plans/calendar') }}">Event Calendar</a>
            </li>


            <li class="nav-item hidden-lg-down">
                <a class="nav-link{{ Request::is('cspot/plans') && ! Request::has('filterby')  ? ' active' : '' }}" href="{{ url('cspot/plans') }}">Your Events</a>
            </li>

        @endif

    </ul>



    <!-- 
        CENTER - Show church logo and name 
    -->
    <span class="navbar-text hidden-md-down mx-auto">
        <a class="nav-link shil text-white" target="new" href="{{ env('CHURCH_URL') }}">
            <img src="{{ url($logoPath.env('CHURCH_LOGO_FILENAME')) }}" height="20">
            {{ env('CHURCH_NAME') }}
        </a>
    </span>




    {{--________________________________________________________

                                            RIGHT - Login form        
        ________________________________________________________
    --}}
    @if ( Auth::guest() )
        <form class="form-inline ml-auto hidden-md-down" method="POST" role="form" action="{{ url('login') }}">
            Log in using &nbsp;@include('auth.social', ['hideLblText' => 'true']) &nbsp;or: &nbsp;
            {!! csrf_field() !!}
            <div class="form-group">
                <input type="email" name="email" class="form-control-sm" id="inputEmail" placeholder="Enter email">
            </div>
            <div class="form-group small-pw-input">
                <input type="password" name="password" class="form-control-sm small-pw-input" id="inputPassword" placeholder="Password">
            </div>
            <div class="checkbox hidden-xs-up">
                <label>
                    <input type="checkbox" name="remember" checked="checked"> Remember me
                </label>
            </div>
            <button type="submit" class="btn btn-sm btn-primary">Sign in</button> &nbsp; &nbsp; &nbsp;
        </form>
    @endif


    {{--___________________________________________________________

                                            RIGHT - Admin and User
        ___________________________________________________________
    --}}

    <ul class="navbar-nav mt-2 ml-auto">



        @if (Auth::guest())

            {{-- user is not logged in --}}
            <li class="nav-item"><a class="nav-link" href="{{ url('login') }}">Sign in</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('register') }}">Register</a></li>

        @else

            {{-- show unread messages count --}}
            <?php 
                $count = Auth::user()->newThreadsCount(); 
            ?>
            @if($count > 0)
               <li class="nav-item" title="You have new mail!">
                    <a class="nav-link mail-alert bg-danger" href="{{URL::to('messages')}}">{!! $count !!}</a>
                </li>
                <script>blink($('.mail-alert'))</script>
            @endif


            {{-- show modal popup for feedback on current page --}}
            <li class="nav-item hidden-md-down">
                <a class="nav-link" href="#" data-toggle="modal" data-target="#createMessage">
                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i> 
                    Page Feedback</a>
            </li>


            {{-- direct link to user list page --}}
            <li class="nav-item hidden-lg-down">
                <a class="nav-link{{ Request::is('admin/users') ? ' active' : '' }}" href="{{ url('admin/users') }}">User List</a>
            </li>


            {{-- main administration drop-down menu --}}
            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle{{ Request::is('admin/*') ? ' active' : '' }}" 
                   data-toggle="dropdown" role="button" aria-expanded="false">
                    <i class="fa fa-cogs"></i> <span class="caret"></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right" role="menu">
                    @if (Auth::user()->isMusician())
                        <a class="dropdown-item" href="{{ url('cspot/files') }}"><i class="fa fa-btn fa-clone fa-lg"></i> &nbsp; Files/Images</a>
                        <a class="dropdown-item" href="{{ url('admin/file_categories') }}"><i class="fa fa-btn fa-file-archive-o fa-lg"></i> &nbsp; File Categories</a>
                        <hr>
                    @endif
                    <a class="dropdown-item" href="{{ url('admin/users') }}"><i class="fa fa-btn fa-users fa-lg"></i> &nbsp; User List</a>
                    <a class="dropdown-item" href="{{ url('admin/roles') }}"><i class="fa fa-btn fa-check-square-o fa-lg"></i> &nbsp; User Roles</a>
                    <a class="dropdown-item" href="{{ url('admin/instruments') }}"><i class="fa fa-btn fa-music fa-lg"></i> &nbsp; User Instruments</a>
                    <hr>
                    <a class="dropdown-item" href="{{ url('admin/resources') }}"><i class="fa fa-btn fa-cubes fa-lg"></i> &nbsp; Resources</a>
                    <hr>
                    <a class="dropdown-item" href="{{ url('admin/bibleversions') }}"><i class="fa fa-btn fa-book fa-lg"></i> &nbsp; Bible Versions</a>
                    <a class="dropdown-item" href="{{ url('admin/biblebooks') }}"><i class="fa fa-btn fa-book fa-lg"></i> &nbsp; Bible Books</a>
                    <a class="dropdown-item" href="{{ url('admin/bibles') }}"><i class="fa fa-btn fa-book fa-lg"></i> &nbsp; Bibles</a>
                    <hr>
                    <a class="dropdown-item" href="{{ route('trainingVideos') }}"><big>&#127979;</big> &nbsp; Training Videos</a>

                    @if (Auth::user()->isAdmin())
                        <hr>
                        <a target="_new" class="dropdown-item" href="{{ url('admin/logs')  }}">
                            <i class="fa fa-btn fa-file-zip-o fa-lg"></i> &nbsp; Laravel Logs</a>
{{--                         <a class="dropdown-item" href="{{ url('admin/runjob/batch')  }}">
                            <i class="fa fa-btn fa-cubes"></i> &nbsp; Run Batch Job(s)</a> --}}
                            <a class="dropdown-item" href="{{ url('admin/customize')  }}">
                            <i class="fa fa-btn fa-cog fa-lg"></i> &nbsp; Customisation</a>
                    @endif
                </div>
            </li>


            {{-- user specific drop-down menu  --}}
            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle{{ Request::is('admin/users/'.Auth::user()->id) ? ' active' : '' }}" 
                   data-toggle="dropdown" role="button" aria-expanded="false">
                    {{ Auth::user()->first_name }}
                    <span class="caret"></span>
                </a>


                <div class="dropdown-menu dropdown-menu-right" role="menu">
                    <a class="dropdown-item" href="{{ url('admin/users/'.Auth::user()->id) }}">
                        <i class="fa fa-btn fa-user fa-lg"></i>
                        Profile</a>
                    <hr>

                    <a  class="dropdown-item" href="#" onclick="setCurrentPageAsStartupPage(this)" 
                        data-action-URL="{{ route('user.setstartpage', Auth::user()->id) }}"
                        title="Set the current page as your personal startup page">
                        <i class="fa fa-btn fa-home fa-lg"></i>
                        Set as Startup Page {!! Auth::user()->startPage=='/'.Request::path() ? '<i class="fa fa-check"></i>' : '' !!}</a>
                    <hr>

                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#createMessage">
                        <i class="fa fa-btn fa-pencil-square-o fa-lg"></i>
                        Page Feedback</a>

                    <a class="dropdown-item" href="{{ URL::to('messages/create') }}">
                        <i class="fa fa-btn fa-pencil-square-o fa-lg"></i>
                        New Message</a>

                    <a class="dropdown-item" href="{{ URL::to('messages') }}">
                        <i class="fa fa-btn fa-inbox fa-lg"></i>
                        Messages @include('messenger.unread-count')</a>
                    <hr>

                    <a class="dropdown-item" href="{{ url('logout') }}">
                        <i class="fa fa-btn fa-sign-out fa-lg"></i>
                        Logout</a>

                </div>
                
            </li>


        @endif

    </ul>


</div>

</nav>
