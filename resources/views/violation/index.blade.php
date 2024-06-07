@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')
<link href="{{url('assets/css/bootstrap-editable.css')}}" rel="stylesheet">
<link href="{{url('assets/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">
<link href="{{url('assets/css/responsive.dataTables.min.css')}}" rel="stylesheet">
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
.dataTables_wrapper .dt-buttons {
        display: none;
}
.sendResident{
        color: red;
    }
.resemailDisable{
    cursor: not-allowed;
    filter: blur(1px);
}
</style>
@endsection
@section('content')
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <div class="col-lg-4">
                    <h2>Manage Violations </h2>
                </div>
                <div class="col-md-8">
                    <div id="reportrange" class="pull-right" style="background: #fff;cursor: pointer;padding: 5px 10px;border: 1px solid #ccc;margin-bottom: 1%;">
                        <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                        <span style="color: #73879C"></span>
                    </div>
                    
                    <a class="btn btn-primary pull-right excel-option" >
                        <i class="fa fa-file-excel-o" aria-hidden="true"></i> Excel 
                    </a>

                    <div class="bulk-actions" style="display: none">
                        <a class="btn btn-primary pull-right print-view" href="javascript:void(0);">
                            <i class="fa fa-print" aria-hidden="true"></i> Print
                        </a>
                        <a class="btn btn-primary pull-right send-mail" href="javascript:void(0);">
                        <i class="fa fa-mail-forward" aria-hidden="true"></i> Send Email
                        </a>
                        <a class="pull-right" href="javascript:void(0);"
                            style="margin: 0.5% 1% 0% 0%;">
                            <select class="form-control input-sm multiple-status">
                                <option selected value="">Change Status</option>
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
                        </a>
                    </div>

                </div>
                <div class="clearfix"></div>
            </div>
            <div class="table-responsive">
                <table id="violations-list" class="table table-striped responsive display nowrap
                    jambo_table cellspacing="0" width="100%">
                        <thead>
                            <tr class="headings">
                                <th><input type="checkbox" id="check-all" class="datatable-checkbox" style='cursor: pointer' disabled></th>
                                <!-- <th class="column-title violation-head">S.No</th> -->
                                <th class="column-title violation-head">
                                    <select class="form-control input-sm filter" 
                                    style="min-width: 82%;" id="usename">
                                        <option value="">Username</option>
                                        @foreach($empolyee as $emp)
                                        <option value="{{$emp->id}}">
                                            {{ucwords($emp->name)}}
                                        </option>
                                        @endforeach
                                    </select>
                                </th>
                                <th class="column-title violation-head">
                                    <select class="form-control input-sm filter"
                                    style="min-width: 100%;" id="properties">
                                        <option value="">Property</option>
                                        @foreach($propertyList as $property)
                                        <option value="{{$property->id}}">
                                            {{ucwords($property->name)}}
                                        </option>
                                        @endforeach
                                    </select>
                                </th>
                                <th class="column-title violation-head">
                                    <select class="form-control input-sm filter"
                                    style="min-width: 72%;" id="rule">
                                        <option value="">Rule</option>
                                        @foreach($reasons as $reason)
                                        <option value="{{$reason['value']}}">
                                            {{ucwords($reason['text'])}}
                                        </option>
                                        @endforeach
                                    </select>
                                </th>
                                <th class="column-title violation-head">
                                    <select class="form-control input-sm filter"
                                    style="min-width: 100%;" id="action">
                                        <option value="">Action</option>
                                        @foreach($actions as $action)
                                        <option value="{{$action['value']}}">
                                            {{ucwords($action['text'])}}
                                        </option>
                                        @endforeach
                                    </select>
                                </th> 
                                <th class="column-title violation-head">
                                    <select class="form-control input-sm filter"
                                    style="min-width: 110%;" id="status">
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
                                <th class="column-title violation-head">Images</th>
                                <th class="column-title violation-head">Special Notes</th>
                                <th class="column-title violation-head">Building Name</th>
                                <th class="column-title violation-head">Created At</th>
                                <th class="column-title violation-head">Action</th>
                            </tr>
                        </thead>
                    </table>
            </div>
        </div>
    </div>
</div>

<!--Model For Violation Detail: Start-->
<div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">

        </div>
    </div>
</div>
<!--Model For Violation Detail: End-->

<!--Model For Violation Detail: Start-->
<div id="violationDetails" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">

        </div>
    </div>
</div>
<!--Model For Violation Detail: End-->

<!--Model For Mail Send: Start-->
<div id="send-mail-popup" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Send Email</h4>
            </div>
            <div class="modal-body">

                <span id="send-mail-message"></span>

                <form id="demo-form2"  class="form-horizontal form-label-left">
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12"  for="To"> To: 
                            <span class="required" style='color:red'>*</span>
                        </label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                            <input id="tagsemail" type="email" required="required" class="form-control tagsemail">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="Cc" class="control-label col-md-3 col-sm-3 col-xs-12"> Cc:
                            <span class="required"style='color:red'>&nbsp;</span></label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                            <input id="cc-mail" type="email" required="required" class="form-control tagsemail">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="Cc" class="control-label col-md-3 col-sm-3 col-xs-12">Cc Me:
                            <span class="required"style='color:red'>&nbsp;</span></label>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="is-check">
                            </label>
                        </div>
                    </div> 
                    @if(!$user->hasRole('property_manager'))
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Template:</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                            <select class="form-control" id="get-template">
                                <option value="">Select Email Template</option>
                                @if(!empty($allTemplate))    
                                @foreach($allTemplate as $all)
                                <option value="{{$all->content}}|||{{$all->subject}}" @if($all->status) selected @endif>
                                        {!! \Illuminate\Support\Str::words($all->name, 5, ' ...') !!}
                            </option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                @endif
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12"  for="To"> Subject: 
                    </label>
                    <div class="col-md-9 col-sm-9 col-xs-12">
                        <input type="text" id="to-subject" value="@if(!$user->hasRole('property_manager')){{$templateSubject}}@endif"
                               class="form-control col-md-7 col-xs-12">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12"  for="To">Body: </label>
                    <div class="col-md-9 col-sm-9 col-xs-12">
                        <textarea id="to-body" class="form-control col-md-7 col-xs-12" rows="10" cols="50">@if(!$user->hasRole('property_manager') && isset($defaultTemplate)){{$defaultTemplate}}@endif</textarea>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="hidden" id='violation-id'>

                    </div>
                </div>
            </form>

        </div>
        <div class="modal-footer">

            <button id="send-mail-violation" type="submit" class="btn btn-primary"> Submit</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>

</div>
</div>
<!--Model For Mail Send: End-->
<!--Model For Resident Mail Send: Start-->
<div id="violation-mail-popup" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Send Email To Resident</h4>
            </div>
            <div class="modal-body">
                <form id="demo-form2 violation-mail-popup"  class="form-horizontal form-label-left">
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12"  for="template"> Template: 
                            <span class="required" style='color:red'>*</span>
                        </label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                            <select class="form-control col-md-7 col-xs-12 violation-template-data" id="violation-template-data" required="required" name="template">
                                <option>Select</option> 
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12"  for="To"> To: 
                            <span class="required" style='color:red'>*</span>
                        </label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                            <input id="viotagsemail" type="email" name="toresidentemail" class="form-control viotagsemail">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="Cc" class="control-label col-md-3 col-sm-3 col-xs-12"> Cc:
                            <span class="required"style='color:red'>&nbsp;</span></label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                            <input id="violation-cc-mail" name="ccEmail" type="email" class="form-control viotagsemail">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12"  for="subject"> Subject: 
                        </label>
                        <div class="col-md-9 col-sm-9 col-xs-12 ">
                            <input type="text" 
                                id="violation-subject" 
                                value=""
                                class="form-control col-md-7 col-xs-12 violation-subject" 
                                name="subject"
                                required="required">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12"  for="body">Body: </label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                            <textarea id="violation-body" name="body" class="form-control col-md-7 col-xs-12 violation-body" rows="10" cols="50" required="required"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        
                        <button id="send-residentmail-violation" type="button" class="btn btn-primary send-residentmail-violation"> Send</button>
                        <button id="send-mail-close" type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--Model For Resident Mail Send: End-->
@endsection 
@section('js')
<script src="{{url('assets/js/bootstrap-editable.js')}}"></script>
<script src="{{url('assets/trashscanjs/violation.list.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<script src="https://cdn.datatables.net/buttons/1.6.3/js/dataTables.buttons.min.js
"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js
"></script>
<script src="https://cdn.datatables.net/buttons/1.6.3/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js" integrity="sha512-rstIgDs0xPgmG6RX1Aba4KV5cWJbAMcvRCVmglpam9SoHZiUCyQVDdH2LPlxoHtrv17XWblE/V/PP+Tr04hbtA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<!-- <script src="{{url('assets/js/dataTables.responsive.min.js')}}"></script> -->
<script>var reasonEdit = @php echo $reasons; @endphp;</script>
<script>var actionEdit = @php echo $actions; @endphp;</script>
<script>var templateSubject = {!! json_encode($violationEmailSubject); !!};</script>
<script>var defaultTemplate = {!! json_encode($violationEmailBody); !!};</script>

@if($user->hasRole("property_manager"))
<script>var vioStatus = '[{"text": "New", "value": 0}, {"text": "Closed","value": 5 }, { "text": "Read", "value": 7}, { "text": "Sent Notice", "value": 10}, { "text": "Archived", "value": 6}]';</script>
@else
<script>var vioStatus = '[{"text": "New", "value": 0}, {"text": "Submitted","value": 2}, {"text": "Closed","value": 5 }, { "text": "Read", "value": 7}, { "text": "In Process", "value": 8}, { "text": "On Hold", "value": 9}, { "text": "Archived", "value": 6}]';</script>
@endif

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

            $('#violations-list').DataTable().destroy();
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
@endsection
