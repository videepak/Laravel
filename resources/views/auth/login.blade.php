@extends('layouts.homeapp')
@section('content')
<!--Added vh-height-->
<div class="container vh-height"> 
    <div class="row">
<!--        Remove all class and added col-sm-12-->
        <div class="col-sm-12">
            <a class="hiddenanchor" id="signup"></a>
            <a class="hiddenanchor" id="signin"></a>

            <div class="login_wrapper">
                <div class="animate form login_form">
<!--                    Added text-center-->
                    <section class="login_content text-center">
                        <form class="form-horizontal" role="form" method="POST" action="{{ route('login') }}">
                            {{ csrf_field() }}
                            <h1>Login to your account</h1>



                            @if ($errors->has('email'))
                            <div class="alert alert-danger">
                                <strong>{{ $errors->first('email') }}</strong>
                            </div>
                            @endif


                            @if ($errors->has('password'))
                            <div class="alert alert-danger">
                                <strong>{{ $errors->first('password') }}</strong>
                            </div>
                            @endif

                            <div>
                                <input type="email" class="form-control"  name="email" id="email" placeholder="Email" value="{{ old('email') }}" required autofocus />
                            </div>
                            <div class="{{ $errors->has('password') ? ' has-error' : '' }}">
                                <input type="password" class="form-control" placeholder="Password" name="password" required/>
                            </div>
                            <div>
<!--                                Added login-btn-->
                                <button type="submit" class="btn btn-default submit login-btn">
                                    Login
                                </button>
<!--                                Added <br>-->
                                <br>
                                <a class="reset_pass" href="{{ route('password.request') }}">Lost your password?</a>
                            </div>

                            <div class="clearfix"></div>


                        </form>
                    </section>
                </div>
            </div>
        </div>

    </div>
</div>





@endsection