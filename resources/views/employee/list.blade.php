@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')
<!-- Datatables -->
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
@yield('menu')     
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Employees List</h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li id="show-enties"></li>
                    <li id="newSearchPlace"></li>
                    <li>
                        <a class="btn btn-primary pull-right" 
                           href="{{url('employee/create')}}">
                            + Add Employee 
                        </a>
                    </li>
                    <li><a class="btn btn-primary pull-right excel-option" >
                        <i class="fa fa-file-excel-o" aria-hidden="true"></i> Excel </a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>

            <div class="x_content">
                <div class="table-responsive">
                    <table id="example" class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings">
                                <th class="column-title">S.No</th>
                                <th class="column-title">Permission Type</th>
                                <th class="column-title">Name</th>
                                <th class="column-title">Email</th>
                                <th class="column-title">Mobile</th>
                                <th class="column-title">Last Login</th>
                                <th class="column-title">Action</th>
                            </tr>
                        </thead>
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
<script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<script src="{{url('assets/trashscanjs/employee.list.js')}}"></script>
<script src="https://cdn.datatables.net/buttons/1.6.3/js/dataTables.buttons.min.js
"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js
"></script>
<script src="https://cdn.datatables.net/buttons/1.6.3/js/buttons.html5.min.js"></script>
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
   
</script>
@endsection 

