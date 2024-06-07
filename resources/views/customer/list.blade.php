@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('content')
@section('css')
<link href="{{url('assets/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">
<link href="{{url('assets/css/responsive.dataTables.min.css')}}" rel="stylesheet">
<style>
.pagination>.active>a,
.pagination>.active>a:focus,
.pagination>.active>a:hover,
.pagination>.active>span,
.pagination>.active>span:focus,
.pagination>.active>span:hover
{
    color: white !important;
}
</style>
@endsection
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Customers List </h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <a class="btn btn-primary"  href="{{url('customer/create')}}">+ Add Customer</a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>

            <div class="x_content">

                <div class="table-responsive">
                    <table id="notes-list" class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings"> 
                                <th class="column-title">S.No </th>
                                <th class="column-title">Name </th>
                                <th class="column-title">Email </th>
                                <th class="column-title">Phone </th>
                                <th class="column-title">State </th>
                                <th class="column-title">Zip </th>
                                <th class="column-title">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($customers) && ($customers->isNotEmpty()))
                            @foreach($customers as $customer)
                            <tr class="even pointer">   
                                <td class=" ">{{ $loop->iteration }}</td>
                                <td class=" ">{{ucwords($customer->name)}} {{ucwords($customer->lastname)}}</td>
                                <td class=" ">{{$customer->email}}</td>
                                <td class=" ">{{$customer->phone}}</td>
                                <td class=" ">@if(isset($customer->stateinfo) && !empty($customer->stateinfo)){{$customer->stateinfo->name}}@endif</td>
                                <td class=" ">{{$customer->zip}}</td>
                                <td class=" ">
                                    <a href="{{url('customer/'.$customer->id)}}"  onclick="return deleteCustomer(this, event);" ><li class="fa fa-trash-o"></li></a>
                                    @if ($customer->user_id == $user->id)
                                        <a href="{{url('customer/'.$customer->id.'/edit')}}/"><li class="fa fa-edit"></li></a>
                                    @endif
                                    <a href="{{url('customer/'.$customer->id.'/details')}}"><li class="fa fa-university"></li></a>
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
<form id="deleteCustomer" action="" method="POST">
    {{method_field('DELETE')}}
    {{ csrf_field() }}
</form>
@endsection 
@section('js')
<script src="{{url('assets/js/bootstrap-editable.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<script>
    $('#notes-list').DataTable(
        {
            "ordering": false
        }
    );

    function deleteCustomer(element, event)
    {
        event.preventDefault();
        if (confirm('Are you sure you want to continue?'))
        {
            var url = $(element).attr('href');
            $('#deleteCustomer').attr('action', url);
            $('#deleteCustomer').submit();
        }
    }
</script>
@endsection
