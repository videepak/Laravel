@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')<!-- Datatables -->
<link href="{{url('assets/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/1.6.3/css/buttons.dataTables.min.css" rel="stylesheet">
<style>
    #example_processing {
        height: 50px !important;
    }
    .pagination>.active>a,
    .pagination>.active>a:focus,
    .pagination>.active>a:hover,
    .pagination>.active>span,
    .pagination>.active>span:focus,
    .pagination>.active>span:hover {
        color: white !important;
    }
    .set-width {
        width: 205%;
        margin-left: -89%;
    }
    .dataTables_wrapper .dt-buttons {
        display: none;
    }
</style>
@endsection
@section('content')
        <div class="right_col" role="main">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                            <h2>Property Manager</h2>
                            <ul class="nav navbar-right panel_toolbox">
                            <li>
                                <a class="btn btn-primary pull-right" 
                                   href="{{url('property-manager/create')}}">
                                    + Property Manager
                                </a>
                            </li>
                        </ul>
                        <div class="clearfix"></div>
                    </div>

                    <div class="x_content">
                        <div class="table-responsive">
                            <table
                                id="example" 
                                class="table table-striped jambo_table bulk_action">
                                <thead>
                                    <tr class="headings">
                                        <th class="column-title">S.No</th>
                                        <th class="column-title">Name</th>
                                        <th class="column-title">Mobile</th>
                                        <th class="column-title">Email</th>
                                        <th class="column-title">Last Login</th>
                                        <th class="column-title">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($employees) && !empty($employees))
                                    @foreach($employees as $employee)
                                    <tr class="even pointer">  
                                        <td>{{ ++$offset }}</td>
                                        <td>

                                            @if(isset($employee->title) && !empty($employee->title))
                                            {{ucwords($employee->title)}}
                                            @endif
                                            @if(isset($employee->firstname) && !empty($employee->firstname))
                                            {{ucwords($employee->firstname)}}
                                            @endif
                                            @if(isset($employee->lastname) && !empty($employee->lastname))
                                            {{ucwords($employee->lastname)}}
                                            @endif

                                        </td>
                                        <td>{{ $employee->mobile }}</td>
                                        <td>{{ $employee->email }}</td>
                                        <td>
                                            @if ($employee->id == Auth::user()->id && !is_null($employee->prevoius_login)) 
                                                {{ \Carbon\Carbon::parse($employee->prevoius_login)
                                                    ->timezone(getUserTimezone())
                                                    ->format('m-d-Y h:i A') }}
                                            @elseif ($employee->id != Auth::user()->id && !is_null($employee->last_login))
                                                    {{ \Carbon\Carbon::parse($employee->last_login)
                                                        ->timezone(getUserTimezone())
                                                        ->format('m-d-Y h:i A') }}
                                            @endif  
                                        </td>
                                        <td>
                                            <a href="{{url('property-manager/edit/'.$employee->id)}}" title='Edit'><li class="fa fa-edit"></li></a>
                                            <a href="{{url('property-manager/destory/'.$employee->id)}}"  onclick="return deleteEmployee(this, event);" title='Delete' ><li class="fa fa-trash-o"></li></a>  
                                        </td>
                                    </tr>
                                    @endforeach
                                    @endif 
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<form id="deleteEmployee" action="" method="POST">
    {{method_field('DELETE')}}
    {{ csrf_field() }}
</form>
@endsection 
@section('js')
<script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
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
$(document).ready(function () {
	$('#example').DataTable();
    
    $('.makeaction').click(function () {
        $('#preloader').show();
    });
});
</script>
@endsection 

