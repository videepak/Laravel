@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('content')
<style>
    .parsley-required{
        display: none;
    }
    input[type=checkbox] {
        margin: 0px;
        padding: 0px;
        display: inline-block;
        vertical-align: middle;
        margin-left: 30px;
    }

</style>  
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2> Edit Roles <small></small></h2>

                <div class="clearfix"></div>
            </div>
            @if(session('display_name'))
            <div class="alert alert-danger">
                <ul>
                    <li>{{ session('display_name') }} </li>
                </ul>
            </div>
            @endif 
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            {!! Form::open(array('route' => array('role.update', $role->id), 'method' => 'PATCH')) !!}
            <!--                    {!! Form::model($role, ['method' => 'PATCH','route' => ['role.update', $role->id]])!!}-->
            <div class="x_content">
                <br>

                <div class="row">
                    <div class="col-sm-12">
                        <input type="hidden" name="name" value="@if(isset($role->name) && !empty($role->name)){{$role->name}}@endif" />
                        <div class="group-bttom-space">
                            <div class="form-group">
                                <label>Display Name <span class="text-danger"><b>*</b></span></label>
                                {!! Form::text('display_name', null, array('placeholder' => 'Display Name','class' => 'form-control','id'=>'display_name')) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="group-bttom-space">
                            <div class="form-group">
                                <label>Description <span class="text-danger">*</span></label>
                                {!! Form::textarea('description', null, array('placeholder' => 'Description','class' => 'form-control msg','style'=>'height:124px','id'=>'description','rows'=>"5")) !!}

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="clearfix"></div>

            <div class="permission">
                <div class="x_title">
                    <h2><i class="fa fa-lock" aria-hidden="true"></i> Permission <small><span class="text-danger">*</span> </small></h2>
                    <div class="clearfix"></div>
                </div>
                @foreach($permission as $list=>$key)
                @if(array_key_exists('self',$key) && $key['self'] != 'Manage Report')
                <div class="col-md-2" style="padding-bottom: 3%; ">
                    <div class="whitebox-new border-box">
                        <div class="col-md-3 heading-title">{{ $key['self'] }}</div>
                        <div class="col-md-1"> {{ Form::checkbox('permission[]', $list, in_array($list, $rolePermissions) ? true : false, array('class' => 'checkbox style-0 selectall')) }}</div>
                    </div>
                </div>

                @endif
                @endforeach


            </div>
            <div class="clearfix"></div>

            <div class="form-group pull-left">
                <div class="col-md-12">
                    <button class="btn btn-primary" type="button" onclick="location = '{{url('role')}}';return false;">Cancel</button>						 
                    <button type="submit" class="btn btn-success">Update</button>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>   
@endsection 