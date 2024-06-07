@extends('layouts.homeapp')
@section('content')

<div class="container vh-height"> 
    <div class="row">

        <div class="col-sm-12">
            <a class="hiddenanchor" id="signup"></a>
            <a class="hiddenanchor" id="signin"></a>

            <div class="login_wrapper">
                <div class="animate form login_form">


                    <section class="login_content text-center">
                        <form class="form-horizontal" role="form" method="POST" action="{{ route('password.email') }}">
                            {{ csrf_field() }}
                            <h1>Reset Password</h1>
                            @if ($errors->has('email'))
                            <div class="alert alert-danger">
                                <strong>{{ $errors->first('email') }}</strong>
                            </div>
                            @endif

                            @if(Session::has('status'))
                            <p class="alert {{ Session::get('alert-class', 'alert-success') }}">
                                {{ Session::get('status') }}
                            </p>
                            @endif


                            <div>
                                <input type="email" class="form-control"  name="email" id="email" placeholder="Email" value="{{ old('email') }}" required autofocus />


                            </div>

                            <div>
                                <button type="submit" class="btn btn-default submit login-btn">
                                    Send Password Reset Link
                                </button>


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
