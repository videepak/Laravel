@extends('layouts.user_app')
@extends('layouts.menu')
@section('css')
<link href="{{url('assets/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">
<link href="{{url('assets/css/responsive.dataTables.min.css')}}" rel="stylesheet">
<style>
   .form-control-feedback {
    margin-top: 8px;
    height: 23px;
    color: #bbb;
    line-height: 18px;
    font-size: 15px;
}
    table.jambo_table thead:first-child > tr:first-child > th{ background: rgba(52,73,94,1); }
    .btn-primary {
        color: #fff;
        background-color: #3E5566;
        border-color: #3E5566;
    }
    .btn-primary:hover {
        color: #fff;
        background-color: #3E5566;
        border-color: #3E5566;
    }   
    span.tag {
        background: #3E5566;
    }
    .modal-dialog {
        min-height: 40%; 
    }
    .modal-content {
        min-height: 100%; 
    }
    .buttonload {
        background-color: #4CAF50; 
        border: none; 
        color: white; 
        padding: 12px 16px; 
        font-size: 16px 
    }
 .dataTables_processing 
 {
    height: 50px !important; 
 }
 .pagination>.active>a,
 .pagination>.active>a:focus,
 .pagination>.active>a:hover,
 .pagination>.active>span,
 .pagination>.active>span:focus,
 .pagination>.active>span:hover
 {
    color: white !important;
 }
 table.dataTable.dtr-inline.collapsed>tbody>tr>td:first-child:before, 
 table.dataTable.dtr-inline.collapsed>tbody>tr>th:first-child:before {
    background-color: #3F5368;
 }

 .select2-container--default 
 .select2-selection--multiple, 
 .select2-container--default 
 .select2-selection--single {
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 3px;
    min-height: 30px;
}
.select2-container--default 
.select2-selection--single 
.select2-selection__arrow {
    height: 30px;
}
.select2-container--default 
.select2-selection--single 
.select2-selection__rendered {
    color: #444;
    line-height: 21px;
}
span#select2-name-container {
    color: #555;
    font-size: 12px;
}

.dataTables_wrapper>.row {
    overflow: unset!important;
}
td {
    sword-break: break-all !important;
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
                <div class="col-md-5">
                    <h2>Manage Reports</h2>
                </div>
                <div class="col-md-2">
                    <select
                        class="form-control col-md-7 col-xs-12 seleteItem"
                        id="subscriber">
                        <option value="">Select Subscriber</option>
                        @foreach ($subscriber as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select
                        class="form-control col-md-7 col-xs-12 seleteItem"
                        id="reporType">
                        <option value="1">Automated Violation Report</option>
                        <option value="2">Automated Clock In/Out Report</option>
                        <option value="3">Automated Unit Report</option>
                        <option value="4">Automated Service Report</option>
                     </select>  
                </div>
                <div class="col-md-2">
                    <input
                    type="text"
                    class="form-control has-feedback-left seleteItem"
                    id="metric-date"
                    style="padding-left: 53px"
                    readonly
                 >
                 <span class="fa fa-calendar-o form-control-feedback left"
                  aria-hidden="true"></span>
                </div>
                <ul class="nav navbar-right panel_toolbox">
                  <li></li>
                  <li></li>    
                  <li></li>
                </ul>
                <div class="clearfix"></div>
            </div> 
            <div class="x_content">
                <div class="table-responsive">
                    <table id="reportTable" class="table table-striped jambo_table bulk_action"></table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
@section('js')
<script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<script src="{{url('assets/trashscanjs/admin.report.js')}}"></script>
<script>
   $("#metric-date").daterangepicker({
      singleDatePicker: !0,
      singleClasses: "picker_4",
      //timeZone: '{{getUserTimezone()}}',
      //maxDate: moment()
   }).on("change", function () { 
   });
</script>
@endsection

