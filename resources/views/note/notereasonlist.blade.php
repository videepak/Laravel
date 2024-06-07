@extends('layouts.user_app')
@extends('layouts.user_menu')
@php $offset = paginateOffset($reason->currentPage(),50); @endphp
@section('content')
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <div class="col-6 col-md-6 col-xs-3"><h2>Note Reason <small></small></h2></div>
                <div class="col-6" style="text-align: right;">
                    <a class="btn btn-primary"  href="{{url('note-reason/create')}}">+ Add Note Reason</a>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="x_content">
                <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action" >
                        <thead>
                            <tr class="hidden-xs">
                                <th>#</th>
                                <th>Note Reason</th>
                                <th width="280px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reason as $key => $role)
                            <tr>
                                <td data-title="sno">{{$offset++}}</td>   
                                <td data-title="name">{{ $role->subject }}</td>
                                <td data-title="action">
                                    <a href="{{ route('note-reason.edit',$role->id) }}" class="N-edit-btn"><span class="fa fa-pencil"></span></a>
                                    {!! Form::open(['method' => 'DELETE','route' => ['note-reason.destroy', $role->id],'style'=>'display:inline','id'=>'delete-role-'.$role->id]) !!}
                                    <a href="javascript:void(0);" onclick="if (!confirm('Are you sure?')) return false; document.getElementById('delete-role-'+{{$role->id}}).submit();" class="N-edit-btn"><span class="fa fa-trash"></span></a>
                                    {!! Form::close() !!}
                                </td>

                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                    <span style="float: right;"> {{ $reason->links() }} </span>
                </div>                       
            </div>
        </div>
    </div>
</div>

<script src="{{url('assets/trashscanjs/notes.reason.list.js')}}"></script>
@endsection 