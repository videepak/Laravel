@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')
<link href="{{url('assets/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">
<link href="{{url('assets/css/bootstrap-editable.css')}}" rel="stylesheet"> 
<link href="{{url('assets/css/responsive.dataTables.min.css')}}" rel="stylesheet">
<style>  
    #example_processing 
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

    .select2-container--default .select2-selection--multiple, .select2-container--default .select2-selection--single {
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 3px;
    min-height: 30px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 30px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
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
.dataTables_processing 
 {
    height: 50px !important; 
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
                <div class="col-md-6">
                    <h2>Notes</h2>
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
                    <a class="btn btn-primary pull-right excel-option" style="padding: 0.9% 2% 0.9% 2%;">
                            <i class="fa fa-file-excel-o" aria-hidden="true"></i> Excel
                        </a>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="table-responsive">
                    <table id="notes-list" class="table table-striped responsive display nowrap
                           jambo_table cellspacing="0" width="100%">
                        <thead>
                            <tr class="headings">
                                <th class="column-title">S.No</th>
                                <th class="column-title">
                                    <select class="form-control input-sm filter" id="name">
                                        <option value="">Username</option>
                                        @foreach($empolyee as $empol)
                                        <option value="{{$empol->id}}">
                                            {{ucwords($empol->title." ".$empol->firstname." ". $empol->lastname)}}
                                        </option>
                                        @endforeach
                                    </select>
                                </th>
                                <th class="column-title">
                                    <select class="form-control input-sm filter reason" id="reasonSubject">
                                        <option value="">Note Subject</option>
                                        @foreach($reasons as $reason)
                                        <option value="{{$reason->id}}">
                                            {{ucwords($reason->subject)}}
                                        </option>
                                        @endforeach
                                    </select> 
                                </th>
                                <th class="column-title">
                                    <select class="form-control input-sm filter" 
                                        data-col="notesType" id = 'notesType'>
                                        <option value="">Type</option>
                                        <option value="1">Unit Specific Notes</option>
                                        <option value="2">General Notes</option>
                                        <option value="3">Checkout Notes</option>
                                    </select>
                                </th>
                                <th class="column-title">Image</th>
                                <th class="column-title">
                                    <select class="form-control input-sm filter status" 
                                        data-col="status">
                                        <option value="">Status</option>
                                        <option value="0">New</option>
                                        <option value="7">Read</option>
                                        <option value="5">Close</option>
                                        <option value="6">Archived</option>
                                        @if(!$user->hasRole("property_manager"))
                                        <option value="2">Submitted</option>
                                        <option value="8">In Process</option>
                                        <option value="9">On Hold</option>
                                        @else
                                        <option value="10">Sent Notice</option>
                                        @endif
                                    </select>
                                </th>
                                <th class="column-title">Detail</th>
                                <th class="column-title">Action</th>
                            </tr>
                        </thead>                       
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close"
                    data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <strong id="popup-heading">Note Detail</strong>
                </h4>
            </div>
            <div class="modal-body" 
                 style="max-height: 500px;overflow-y: auto;"></div>
            <div class="modal-footer hide-footer"  style="display: none">
                <button type="button" 
                        class="btn btn-success pull-right" 
                        id="violation-edit">
                    <span class="spiner"></span>Submit</button>
            </div>
        </div>
    </div>
</div>
@endsection 
@section('js')
<script src="{{url('assets/js/bootstrap-editable.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<!-- <script src="{{url('assets/js/dataTables.responsive.min.js')}}"></script> -->
<script src="{{url('assets/trashscanjs/notes.list.js')}}"></script>
<script src="https://cdn.datatables.net/buttons/1.6.3/js/dataTables.buttons.min.js
"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js
"></script>
<script src="https://cdn.datatables.net/buttons/1.6.3/js/buttons.html5.min.js"></script>
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

            $('#notes-list').DataTable().destroy();
            load_data();
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
<!--Mantain two diffrent status first one for admin and second one for property manager (Task: 640 comment: #17)): Start -->
@if($user->hasRole("property_manager"))
<script>var noteSubject = '[{"text": "New", "value": 0}, {"text": "Closed","value": 5 }, { "text": "Read", "value": 7}, { "text": "Sent Notice", "value": 10}, { "text": "Archived", "value": 6}]';</script>
@else
<script>var noteSubject = '[{"text": "New", "value": 0}, {"text": "Submitted","value": 2}, {"text": "Closed","value": 5 }, { "text": "Read", "value": 7}, { "text": "In Process", "value": 8}, { "text": "On Hold", "value": 9}, { "text": "Archived", "value": 6}]';</script>
@endif
<!--Mantain two diffrent status first one for admin and second one for property manager (Task: 640 comment: #17)): End -->
@endsection 

