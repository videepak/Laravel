@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')
<!--<link href="{{url('assets/css/bootstrap-editable.css')}}" rel="stylesheet">-->
<link href="{{url('assets/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">
<!-- <link href="{{url('assets/css/responsive.dataTables.min.css')}}" rel="stylesheet"> -->
<style>
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
</style>
@endsection
@section('content')   
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <div class="col-md-6">
                    <h2>Activity Logs</h2>
                </div> 
                <div class="col-md-6">
                    <div id="reportrange" 
                        class="pull-right" 
                        style="background: #fff; 
                            cursor: pointer; 
                            padding: 5px 10px; 
                            border: 1px solid #ccc;margin-bottom: 1%;">
                        <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                    <span style="color: #73879C"></span>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="table-responsive">
                    <table id="activitylog" class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings">                            
                                <th class="column-title">S.No </th>
                                <th class="column-title">Property Detail </th>
                                <th class="column-title">
                                   <select class="form-control input-sm filter" 
                                    id="usename">
                                        <option value="">Username</option>
                                        @foreach($empolyee as $emp)
                                        <option value="{{$emp->id}}">
                                            {{ucwords($emp->name)}}
                                        </option>
                                        @endforeach
                                    </select>
                                </th>
                                <th class="column-title">Email </th>
                                <th class="column-title">Activity</th>
                                <th class="column-title">Info</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
@section('js')
<!--<script src="{{url('assets/js/bootstrap-editable.js')}}"></script>-->
<script src="{{url('assets/trashscanjs/activity.log.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<script> var id = {{$urlId}} </script>
<script>
function init_daterangepicker() {

    if ("undefined" != typeof $.fn.daterangepicker) {
        
        var a = function (a, b, c) {
            $("#reportrange span").html(a.format("MMMM D, YYYY") + " - " + b.format("MMMM D, YYYY"))
        },
        b = {
                    startDate: moment().subtract(29, "days"),
                    endDate: moment(),
                    minDate: "01/01/2012",
                    maxDate: <?php echo date('Y-m-d'); ?>,
                    dateLimit: {
                        days: 60
                    },
                    showDropdowns: false,
                    showWeekNumbers: false,
                    timePicker: false,
                    timePickerIncrement: false,
                    timePicker12Hour: false,
                    ranges: {
                        Today: [moment(), moment()],
                        Yesterday: [moment().subtract(1, "days"), moment().subtract(1, "days")],
                        "Last 7 Days": [moment().subtract(6, "days"), moment()],
                        "Last 30 Days": [moment().subtract(29, "days"), moment()],
                        "This Month": [moment().startOf("month"), moment().endOf("month")],
                        "Last Month": [moment().subtract(1, "month").startOf("month"), moment().subtract(1, "month").endOf("month")]
                    },
                    opens: "left",
                    buttonClasses: ["btn btn-default"],
                    applyClass: "btn-small btn-primary",
                    cancelClass: "btn-small",
                    format: "MM/DD/YYYY",
                    separator: " to ",
                    locale: {
                        applyLabel: "Submit",
                        cancelLabel: "Clear",
                        fromLabel: "From",
                        toLabel: "To",
                        customRangeLabel: "Custom",
                        daysOfWeek: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"],
                        monthNames: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
                        firstDay: 1
                    }
                };

        $("#reportrange span").html(moment().subtract(29, "days").format("MMMM D, YYYY") + " - " + moment().format("MMMM D, YYYY")), 
        $("#reportrange").daterangepicker(b, a), 
        $("#reportrange").on("show.daterangepicker", function () {
            console.log("show event fired")
        }),
        $("#reportrange").on("hide.daterangepicker", function () {
            console.log("hide event fired")
        }), 
        $("#reportrange").on("apply.daterangepicker", function (a, b) {
            $('#activitylog').DataTable().destroy();
            loadTable();
        }), 
        $("#reportrange").on("cancel.daterangepicker", function (a, b) {
            //console.log("cancel event fired")
        }),
        $("#options1").click(function () {
            $("#reportrange").data("daterangepicker").setOptions(b, a)
        }),
        $("#options2").click(function () {
            $("#reportrange").data("daterangepicker").setOptions(optionSet2, a)
        }),
        $("#destroy").click(function () {
            $("#reportrange").data("daterangepicker").remove()
        })
    }
}	
</script>
@endsection