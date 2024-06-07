@extends('layouts.user_app')
@include('layouts.user_menu')
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
                <h2>Note Reason <small></small></h2>

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

            <form id="add_role" name="add_role" data-parsley-validate="" class="form-horizontal form-label-left" novalidate="" action="@isset($reason->id){{route('note-reason.update', $reason->id)}}@else{{url('note-reason')}}@endisset" method="post">
                <div class="x_content">
                    <br>
                    {{ csrf_field() }}
                    @isset($reason->subject)
                    {{method_field('PUT')}}
                    @endisset
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="group-bttom-space">
                                <div class="form-group">
                                    <label>Note Reason</label>
                                    <input placeholder="Note Reason" class="form-control" id="Reason" name="Reason" type="text" value="@isset($reason->subject){{$reason->subject}}@endisset">
                                </div>
                            </div>
                        </div>                                
                    </div>
                </div>

                <div class="clearfix"></div>


                <div class="clearfix"></div>

                <div class="form-group pull-left">
                    <div class="col-md-12">

                        <button type="submit" class="btn btn-success">@if(isset($reason->id) && !empty($reason->id)) Update @else Add @endif</button>

                        <button class="btn btn-primary" type="button" onclick="location = '{{url('note-reason')}}';return false;">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 