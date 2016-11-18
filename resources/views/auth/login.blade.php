
<!-- # (C) 2016 Matthias Kuhs, Ireland -->

@extends('layouts.main')

@section('title', "Login User")

@section('login', 'active')

@section('content')


    <div class="row">
        <div class="container signin-body">

            @include('layouts.flashing')
            
            {!! csrf_field() !!}
    

            <center class="mb-2">

                <h4>Sign in using your account from one of these service providers:</h4>

                @include('auth.social')
                
                <div class="mt-2">
                    Not sure what to do? <br>
                    <i class="fa fa-youtube-play red"></i>
                    <a href="https://www.youtube.com/watch?v=SNgq9ZW1KMs" target="new">
                        Watch this short training video</a>
                    <i class="fa fa-external-link small"></i><br> explaining 
                    the sign-in process and the basics features of c-SPOT
                </div>

            </center>


            <h5 class="card-header">You can also sign in by email address and password:</h5>

            <form class="form-horizontal" role="form" method="POST" id="inputForm"  action="{{ url('login') }}">

                <div class="row mt-1 form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                    <label class="col-md-3 offset-md-1 control-label">E-Mail Address</label>

                    <div class="col-md-6">
                        <input required type="email" class="form-control" name="email" value="{{ old('email') }}">

                        @if ($errors->has('email'))
                            <span class="help-block">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="row form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                    <label class="col-md-3 offset-md-1 control-label">Password</label>

                    <div class="col-md-6">
                        <input required type="password" class="form-control" name="password">

                        @if ($errors->has('password'))
                            <span class="help-block">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 offset-md-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="remember"> Remember Me
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row form-group">
                    <div class="col-md-6 offset-md-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-btn fa-sign-in"></i> Login
                        </button>
                        <a class="btn btn-outline-primary float-right" href="{{ url('password/reset') }}">Forgot Your Password?</a>
                    </div>
                </div>

            </form>

            <script type="text/javascript">document.forms.inputForm.email.focus()</script>

            <br>
            @include('help')

        </div>    
    </div>

@endsection
