@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('content')
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <div class="col-md-6"><h2>Change Password <small></small></h2></div> <div class="col-md-6" style="text-align: right;"></div>


                <div class="clearfix"></div>
            </div>
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <div class="x_content">
                <form id="demo-form2" data-parsley-validate="" class="form-horizontal form-label-left" novalidate="" action="{{url('update_password')}}" method="post">
                    <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}"/>
                    <input type="hidden" name="email" id="email" value="{{ Auth::user()->email }}"/>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Current Password <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input id="current_password"  name="current_password"  required="required" class="form-control col-md-7 col-xs-12" type="password" minlength="6">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">New Password <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input id="new_password" name="password" minlength="6" required="required" class="form-control col-md-7 col-xs-12" type="password">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Confirm Password <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input id="confirm_password" name="password_confirmation" minlength="6" required="required" class="form-control col-md-7 col-xs-12" type="password">
                        </div>
                    </div>      



                    <div class="ln_solid"></div>
                    <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">

                            <button type="submit" class="btn btn-success">Update</button>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>   
@endsection 
@section('js')
<script type="text/javascript">
    $('#demo-form2').parsley({
        excluded: '.two input'
    });
</script>>
@endsection