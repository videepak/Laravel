@extends('layouts.user_app')
@extends('layouts.user_menu')
@php $offset = paginateOffset($roles->currentPage(),10); @endphp
@section('content')
@yield('menu')     
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <div class="col-md-6"><h2>User Roles <small></small></h2></div> <div class="col-md-6" style="text-align: right;"><a class="btn btn-primary"  href="{{url('role/create')}}">+ Add Role</a></div>
                <div class="clearfix"></div>
            </div>

            <div class="x_content">



                <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action" >
                        <thead>
                            <tr class="hidden-xs">
                                <th>#</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th width="280px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $key => $role)
                            <tr>
                                <td data-title="sno">{{$offset++}}</td>   
                                <td data-title="name">{{ucwords($role->display_name)}}</td>

                                <td data-title="description">{{ ucwords($role->description) }}</td>
                                <td data-title="action">
                                    <a href="{{ route('role.edit',$role->id) }}" class="N-edit-btn"><span class="fa fa-pencil"></span></a>
                                   <!-- <a href="{{ route('role.show',$role->id) }}" class="N-edit-btn"><span class="fa fa-eye"></span></a>-->
                                    {!! Form::open(['method' => 'DELETE','route' => ['role.destroy', $role->id],'style'=>'display:inline','id'=>'delete-role-'.$role->id]) !!}
                                    <a href="javascript:void(0);" onclick="if (!confirm('Are you sure?')) return false; document.getElementById('delete-role-'+{{$role->id}}).submit();" class="N-edit-btn"><span class="fa fa-trash"></span></a>
                                    {!! Form::close() !!}
                                </td>

                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>

                <span style="float: right;">{{ $roles->links() }}</span>
            </div>
        </div>
    </div>
</div>
<form id="deleteEmployee" action="" method="POST">
    {{method_field('DELETE')}}
    {{ csrf_field() }}
</form>
<script>
    function deleteEmployee(element, event)
    {
    event.preventDefault();
    if (confirm('Are you sure you want to continue?'))
    {
    var url = $(element).attr('href');
    $('#deleteEmployee').attr('action', url);
    $('#deleteEmployee').submit();
    }
    }
</script>
@endsection 