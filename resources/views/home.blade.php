
<!-- # (C) 2016 Matthias Kuhs, Ireland -->

@extends('layouts.main')


@section('content')


    <div class="container spark-screen">
        <div class="row">
            <div class="col-xl-10 offset-xl-1">
            
                @include('layouts.flashing')


                <div class="card card-block center">

                    @if (! Auth::user()->isMusician())
                        <p>Welcome, <strong class="shil">{{ Auth::user()->first_name }}</strong>, to </p>

                        <h3 class="card-title lora text-shadow">
                            c-SPOT, the <span class="text-primary">c</span>hurch-<span class="text-primary">S</span>ervices 
                            <span class="text-primary">P</span>lanning <span class="text-primary">O</span>nline <span class="text-primary">T</span>ool
                        </h3>
                        for
                        <small class="float-right"><a href="http://www.ie.ccli.com/?country=ie">CCLI</a> # {{ (env('CHURCH_CCLI')) ? env('CHURCH_CCLI') : '?' }}</small>

                    @else

                        <h3 class="korn text-shadow">c-SPOT, the <span class="text-primary">c</span>hurch-<span class="text-primary">S</span>ervices 
                            <span class="text-primary">P</span>lanning <span class="text-primary">O</span>nline <span class="text-primary">T</span>ool</h3>
                        <small><a href="http://www.ie.ccli.com/?country=ie">CCLI</a> # {{ (env('CHURCH_CCLI')) ? env('CHURCH_CCLI') : '?' }}</small>
                    @endif


                    <a href="{{ env('CHURCH_URL') }}" target="new">
                        <h3 class="shil">
                            <img src="{{ url($logoPath.env('CHURCH_LOGO_FILENAME')) }}" height="30">
                            {{ env('CHURCH_NAME') }}
                        </h3>
                    </a>


                    <hr>


                    <p class="card-text lora">

                        <span class="btn btn-lg btn-success md-full mr-2">
                            <a href="#" data-container="body" data-toggle="tooltip" data-placement="left" 
                                    class="float-right" title="Go directly to next Sunday's Service Plan">
                                &nbsp; <i class="fa fa-question-circle"></i></a>
                            <a href="{{ url('cspot/plans/next') }}" class="link">
                                Next Sunday's Plan
                            </a>
                        </span>

                        <button class="btn btn-lg btn-primary md-full mr-2 link"
                                onclick="location.href='{{ url('cspot/plans/calendar') }}'">
                            <a href="#" data-container="body" data-toggle="tooltip" data-placement="left" 
                                    class="float-right" title="Show all upcoming Service Plans">
                                <i class="fa fa-question-circle bg-primary text-white ml-1"></i></a>
                            Events Calendar
                        </button>

                        <span class="btn btn-lg btn-info md-full">
                            <a href="#" data-container="body" data-toggle="tooltip" data-placement="left" class="float-right" 
                                    title="Show (future) plans where you are leader or teacher">
                                &nbsp; <i class="fa fa-question-circle"></i></a>
                            <a href="{{ url('cspot/plans') }}" class="link">
                                Your Services/Events
                            </a>
                        </span>

                    </p>
                    <hr>

                    <p class="card-text lora">

                        <span class="btn btn-outline-success md-full mr-2">
                            <a href="{{ url('cspot/songs') }}">
                                <i class="fa fa-btn fa-music fa-lg float-left"> </i>
                                &nbsp; Songs Repository <small>{{ isset($songsCount) ? '('.$songsCount.')' : '' }}</small>
                                <i class="fa fa-btn fa-music fa-lg float-right hidden-md-up"></i>
                            </a>
                        </span>

                        <span class="btn btn-outline-success md-full mr-2" 
                                title="Available Bible versions stored on the server: {{ DB::table('bibleversions')->select('name')->get()->implode('name', ',') }}">
                            <a href="{{ url('admin/bibles') }}">
                                <i class="fa fa-btn fa-book fa-lg float-left"> </i>
                                &nbsp; Bibles ({{ DB::table('bibleversions')->count() }})
                                <i class="fa fa-btn fa-book fa-lg float-right hidden-md-up"></i>
                            </a>
                        </span>

                        <button class="btn btn-outline-primary md-full mr-2">
                            <a href="{{ url('cspot/songs?only=slides') }}">
                                <i class="fa fa-btn fa-clone fa-lg float-left"></i>
                                &nbsp; Slides 
                                <small>{{ isset($slideCount) ? '('.$slideCount.')' : '' }}</small>
                                <i class="fa fa-btn fa-clone fa-lg float-right hidden-md-up"></i>
                            </a>
                        </button>

                        <span class="btn btn-outline-info md-full">
                            <a href="{{ url('cspot/songs?only=video') }}">
                                <i class="fa fa-btn fa-tv fa-lg float-left"></i>
                                &nbsp; Videoclips 
                                <small>{{ isset($videoCount) ? '('.$videoCount.')' : '' }}</small>
                                <i class="fa fa-btn fa-tv fa-lg float-right hidden-md-up"></i>
                            </a>
                        </span>

                    </p>
                    <hr>

                    <div id="inpDate" onchange="openPlanByDate(this)"></div>

                </div>

  
                @include('help')


                <p class="small text-muted">Last Software Update:
                    {{ file_get_contents('lastCommit.txt') }}
                </p>

            </div>
        </div>
    </div>

@endsection
