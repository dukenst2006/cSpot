
<div class="row">

    <div class="col-sm-3 col-md-2 sidebar">
        <ul class="nav nav-sidebar">
            @if(!Auth::guest())
                <li class="@yield('future')"><a href="/cspot/plans/future">Upcoming Services</a></li>
                <li class="@yield('plans') ">
                    <a href="/cspot/plans">{{ (Auth::user()->isAdmin()) ? 'All' : 'Your' }} Services
                    <span class="sr-only">(current)</span></a>
                </li>
                <br />
                <li class="@yield('songs')"><a href="/cspot/songs">Songs</a></li>
                <br />
                <strong>Administration</strong>
                @if( Auth::user()->isAuthor() )
                    <li class="@yield('create')"><a href="/cspot/plans/create">Create New Plan</a></li>
                @endif
                <li class="@yield('types')"><a href="/admin/types">Service Types</a></li>
                <li class="@yield('items')"><a href="/admin/default_items">Default Items</a></li>
                <li class="@yield('roles')"><a href="/admin/roles">User Roles</a></li>
                @if(Auth::user()->id===1 || Auth::user()->isAuthor())
                    <li class="@yield('users' )"><a href="/admin/users">Users</a></li>
                @endif
            @endif
        </ul>
    </div>

    
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
