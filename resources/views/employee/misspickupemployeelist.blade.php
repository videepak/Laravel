@extends('layouts.user_app')
@extends('layouts.user_menu')
@php $offset = paginateOffset($employees->currentPage(),10); @endphp
@section('content')       
@yield('menu')     
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Missed Pickup Employees List</h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <a class="btn btn-primary pull-right" 
                           href="{{url('employee/create')}}">
                            + Add Employee 
                        </a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>

            <div class="x_content">
                <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings">
                                <th class="column-title">S.No</th>
                                <th class="column-title">Permission Type</th>
                                <th class="column-title">Name</th>
                                <th class="column-title">Email</th>
                                <th class="column-title">Mobile</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($employees))
                            @foreach($employees as $key=>  $employee)
                            <tr class="even pointer">    
                                <td>{{$offset++}}</td>
                                <td>{{ucwords($employee['rolename'])}}</td>
                                <td>{{ucwords($employee['firstname'])}} {{ucwords($employee['lastname'])}}</td>
                                <td>{{$employee['email']}}</td>
                                <td>{{$employee['mobile']}}</td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                <span style="float: right;">{{ $employees->links() }}</span>
            </div>
        </div>
    </div>
</div>   
<form id="deleteEmployee" action="" method="POST">
    {{method_field('DELETE')}}
    {{ csrf_field() }}
</form>



<!-- Modals Start 
<div id="assign_user_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">

<!-- Modal content-->
<!--<div class="modal-content">
  <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">&times;</button>
      <h4 class="modal-title">Assign properties</h4>
  </div>
  <form action="{{url('/assign/employee/property')}}" id="add_emp_property" name="add_emp_property" data-parsley-validate="" class="form-horizontal form-label-left" novalidate="" method="post">

      <div class="modal-body">
          <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}"/>
          <p class="user_properties"></p>
          
      </div>

      <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Add</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
  </form>
</div>

</div>
</div>-->




@endsection 
@section('js')
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

    /*model js
     $(document).on('click','.user-property',function(){
     $('#assign_user_modal').modal({
     show:true
     });
     
     var empId = $(this).data('empid');
     var token = $('#_token').val();
     
     
     
     $.ajax({
     url:"employee/get/assigned/property", 
     type:"post",
     data:{_token:token,empId:empId},
     success:function(data){
     alert(data);
     var details = data;
     $('.user_properties').html(data);
     
     
     
     }
     
     
     });
     
     
     
     });*/
    $('.makeaction').click(function () {
        $('#preloader').show();
    });
</script>
@endsection 

