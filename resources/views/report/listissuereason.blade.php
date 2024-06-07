@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('content')
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Manage Exception Types</h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <a class="btn btn-primary pull-right" 
                           href="{{url('create-report-issue-reason')}}">
                            + Add  Exception Type</a>
                    </li>
                </ul>

                <div class="clearfix"></div>
            </div>

            <div class="x_content">
                <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action" >
                        <thead>
                            <tr class="hidden-xs">
                                <th>#</th>
                                <th>Exception Types</th>
                                <th width="280px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reportIssue as $key => $role)
                            <tr>
                                <td>{{ $key+1 }}</td>   
                                <td>{{ ucfirst($role->reason)}}</td>
                                <td>
                                    <a href="{{url('edit-issue-reason/'.$role->id.'')}}" class="N-edit-btn"><span class="fa fa-pencil"></span></a>
                                    <a href="{{url('destory-issue-reason/'.$role->id.'')}}" onclick="return confirm('Are you sure you want to delete the exception type?')"><span class="fa fa-trash"></span></a>
<!--                                            <a href="javascript:void(0);" onclick="if (!confirm('Are you sure?')) return false; document.getElementById('delete-role-'+{{$role->id}}).submit();" class="N-edit-btn"><span class="fa fa-trash"></span></a>-->
                                    {!! Form::close() !!}
                                </td>

                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<form id="delete-role" action="" method="POST">
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