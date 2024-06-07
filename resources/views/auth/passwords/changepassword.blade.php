@extends('layouts.user_app')

@section('content')
 
<div>
    <a class="hiddenanchor" id="signup"></a>
    <a class="hiddenanchor" id="signin"></a>

    <div class="login_wrapper">
        <div class="animate form login_form">
            <section class="login_content">
                <form class="form-horizontal" role="form" method="POST" action="{{ route('admin.login.submit') }}">
                    {{ csrf_field() }}
                    <h1>Login Form</h1>
                    <div>
                        <input type="email" class="form-control"  name="email" id="email" placeholder="Email" value="{{ old('email') }}" required autofocus />
                        @if ($errors->has('email'))
                        <span class="help-block">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                        @endif
                    </div>
                    <div class="{{ $errors->has('password') ? ' has-error' : '' }}">
                        <input type="password" class="form-control" placeholder="Password" name="password" required/>
                        @if ($errors->has('password'))
                        <span class="help-block">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                        @endif
                    </div>
                    <div>
                        <button type="submit" class="btn btn-default submit">
                            Login
                        </button>

                        <a class="reset_pass" href="{{ route('password.request') }}">Lost your password?</a>
                    </div>

                    <div class="clearfix"></div>

                    
                </form>
            </section>
        </div>
    </div>
</div>


@endsection
