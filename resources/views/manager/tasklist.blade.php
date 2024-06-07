@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')
<style>  
.parsley-required {
  display:block; 
  padding-left: 15px; 
} 
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
    .select2-container--default
    .select2-selection--multiple
    .select2-selection__choice {
        background-color: #3E5566;
        color: white;
    }
    .badge {
        padding: 2px 5px;
        border: 0px solid #1ABB9C !important;
    }
    .dataTables_wrapper .dt-buttons {
        display: none;
    }
    .select2-container {
        display: block;
    }
    span.select2.select2-container.select2-container--default {
        width: 100% !important;
    }
</style>
@endsection
@section('content')
<div class="right_col" role="main">
	<div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <div class="col-md-6">
                    <h2>Tasks List </h2>
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
                    <table class="table table-striped jambo_table bulk_action" id="task">
                        <thead>
                            <tr class="headings">    
                                <th class="column-title">S.no </th>
                                <th class="column-title">Task </th>
                                <th class="column-title">
                                    <select name='property' 
                                    class="form-control filter-search input-sm"
                                    style="width: 80%;" id="pro-select2">
                                    Properties
                                    <option value="">Property</option>
                                    @isset($properties)
                                        @foreach($properties as $property)
                                            <option value='{{$property["id"]}}'>
                                            {{ucwords($property['name'])}}
                                            </option>
                                        @endforeach
                                    @endisset
                                    </select>
                                </th>
                                <th class="column-title" >Completion Date </th>
                                <th class="column-title">
                                    <select name='property' 
                                    class="form-control filter-search input-sm"
                                    style="width: 60%;" id="fre-select2">
                                    Frequency
                                    <option value="">Frequency</option>
                                    <option value="1">Daliy</option>
                                    <option value="2">Weekly</option>
                                    <option value="3">Monthly</option>
                                    </select>
                                </th>
                                <th class="column-title">
                                <select class="form-control filter-search input-sm"
                                    style="width: 40%;" id="scanBy">
                                    <option value="">Scan By</option>
                                    @isset($scanBy)
                                        @foreach($scanBy as $scan)
                                            @foreach($scan as $sca)
                                                    <option value='{{$sca->id}}'>
                                                    {{ucwords($sca->name)}}
                                                    </option>
                                            @endforeach
                                        @endforeach
                                    @endisset
                                    </select>
                                </th>
                                <th class="column-title">
                                    <select name='property' 
                                    class="form-control filter-search input-sm"
                                    style="width: 90%;" 
                                    id="media">
                                        <option value="">Media</option>
                                        <option value="image">Image</option>
                                        <option value="audio">Audio</option>
                                        <option value="video">Video</option>
                                    </select>
                                </th>
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
<script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<script>
    function dataLoad() {
        var urlParams = new URLSearchParams(window.location.search);
        var dat = urlParams.has('date') ? urlParams.get('date') : '';
        let range = $("#reportrange").find("span").text().split('-');
        
        $('#task').DataTable({
            dom: 'lBfrtip',
            "bPaginate": true,
            "ordering": false,
            "bLengthChange": true,
            "pageLength": 25,
            "bFilter": true,
            "bInfo": true,
            language: {
            search: "",
                searchPlaceholder: "Search",
            },
            "bAutoWidth": false,
            "processing": true,
            "serverSide": true,
            "ajax": {
            "url": BaseUrl + "/property-manager/get-task",
                "type": "POST",
                "data":{ 
                    _token: $('meta[name="csrf_token"]').attr('content'),
                    id: $('.filter-search').val(),
                    date: dat,
                    startTime: range[0], 
                    endTime: range[1]
                }
            },
            "columns": [
                { "data": "sNo" },
                { "data": "task" },            
                { "data": "property_name" },
                { "data": "updated_at" },
                { "data": "status" },
                { "data": "employee_name" },	
                { "data": "action" },	
            ]    
        });
    }

    function init_daterangepicker() {
        if ("undefined" != typeof $.fn.daterangepicker) {
            var a = function (a, b, c) {
                $("#reportrange span").html(a.format("MMMM D, YYYY") + " - " + b.format("MMMM D, YYYY"))
            },
            b = {
                startDate: moment().subtract(29, "days"),
                endDate: moment(),
                minDate: "01/01/2012",
                maxDate: 2023-03-21,
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
                $('#task').DataTable().destroy();
                dataLoad();
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
$(document).ready(function () {
    dataLoad();
});
</script>
@endsection