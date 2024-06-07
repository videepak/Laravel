@extends('layouts.user_app')
@include('layouts.user_menu')
@php $offset = paginateOffset($action->currentPage(),50); @endphp
@section('content')   
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <div class="col-md-8 col-sm-8 col-xs-3">
                    <h2>Violation Action </h2>
                </div> 
                <div class="col-md-4 col-sm-4 col-xs-9" style="text-align: right;">
                    <a class="btn btn-primary"  href="{{url('create-violation-action')}}">
                        + Add Violation Action
                    </a>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="x_content">
                <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action" >
                        <thead>
                            <tr class="hidden-xs">
                                <th>#</th>
                                <th>Violation Action</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($action as $key => $role)
                            <tr>
                                <td data-title="sno">{{$offset++}}</td>   
                                <td data-title="name">{{ $role->action }}</td>
                                <td data-title="action">
                                    @if($role->type)
                                        <a href="{{ url('action-edit',$role->id) }}" class="N-edit-btn">
                                            <span class="fa fa-pencil"></span>
                                        </a>|
                                    @endif
                                   
                                    {!! Form::open(['method' => 'get','url' => ['action-destroy', $role->id],'style'=>'display:inline','id'=>'delete-role-'.$role->id]) !!}
                                    <a href="javascript:void(0);" onclick="if (!confirm('Are you sure?')) return false; document.getElementById('delete-role-'+{{$role->id}}).submit();" class="N-edit-btn"><span class="fa fa-trash"></span></a>
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>

            <span style="float: right;"> {{ $action->links() }} </span>
        </div>
    </div>
</div>

@endsection 