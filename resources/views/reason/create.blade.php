@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('content')
<style>
    .parsley-required{
        display: none;
    }
    .form-horizontal .checkbox{
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
                <h2>Violation Rule <small></small></h2>

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

            <form id="add_role" name="add_role" data-parsley-validate="" class="form-horizontal form-label-left" novalidate="" action="{{url('reason')}}" method="post">
                <div class="x_content">
                    <br>
                    <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}"/>

                    <div class="row">
                        <div class="col-sm-12">
                            <!--<div class="group-bttom">
                                <div class="form-group">
                                    <label>Name</label>
                                    {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control','id'=>'name','style'=>'text-transform: lowercase')) !!}
                                </div>
                            </div>-->
                            <input type="hidden" name="name" value="" />
                            <div class="group-bttom-space">
                                <div class="form-group">
                                    <label>Violation Rule</label>
                                    {!! Form::text('Reason', null, array('placeholder' => 'Violation Rule','class' => 'form-control','id'=>'Reason')) !!}
                                </div>
                            </div>
                        </div>                                
                    </div>
                </div>

                <div class="clearfix"></div>


                <div class="clearfix"></div>

                <div class="form-group pull-left">
                    <div class="col-md-12">

                        <button type="submit" class="btn btn-success">@if(isset($employee->id) && !empty($employee->id)) Update @else Add @endif</button>

                        <button class="btn btn-primary" type="button" onclick="location = '{{url('reason')}}';return false;">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 