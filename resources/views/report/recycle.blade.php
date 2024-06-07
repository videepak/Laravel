@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')
<link href="{{url('assets/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">
<style>
    #example_processing{
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
</style>
@endsection
@section('content')  
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <div class="col-md-3"><h2>Recycle Reports </h2></div> 
                <div class="col-md-9">
                    <div class="col-md-3 pull-right" >
                        <select name='property' 
                                class="form-control filter-search input-sm pull-right" 
                                style="width: 90%;">Properties
                            <option value="">Select Property</option>
                            @isset($properties)
                            @foreach($properties as $property)
                            <option value='property/{{$property['id']}}' @if($condition_id == "property/".$property['id']) selected="selected" @endif>{{ucwords($property['name'])}}</option>
                            @endforeach
                            @endisset
                        </select>
                    </div>
                    <div class="col-md-6 pull-right" 
                         id="newSearchPlace"></div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action"
                           id="example">
                        <thead>
                            <tr class="headings">    
                                <th class="column-title">
                                    S.no
                                </th>
                                <th class="column-title">
                                    Property Name
                                </th>
                                <th class="column-title">
                                    Building Name
                                </th>
                                <th class="column-title">
                                    Unit
                                </th>
                                <th class="column-title">
                                    Scan Date
                                </th>
                                <th class="column-title">
                                    Status
                                </th>
                                <th class="column-title">
                                    Waste Total
                                </th>
                                <th class="column-title">
                                    Recycle Total
                                </th>
                                <th class="column-title">
                                    Waste Target
                                </th>
                                <th class="column-title">
                                    Employee
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs_array as $key=> $value)
                            <tr class="headings">    
                                <td class="column-title">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="column-title">
                                    {{ $value['property_name'] }}
                                </td>
                                <td class="column-title">
                                    @if(isset($value['building_name']) 
                                    && !empty($value['building_name']) 
                                    && ($value['type'] == 3 
                                    || $value['type'] == 2)) 
                                    {{ $value['building_name'] }}
                                    @else
                                    -
                                    @endif
                                </td>
                                <td class="column-title">{{ $value['unit'] }}</th>
                                <td class="column-title">
                                    {{\Carbon\Carbon::parse($value['updated_at'])
                                                        ->timezone(getUserTimezone())
                                                        ->format('m-d-Y h:i A')}}   
                                </td>
                                <td class="column-title">
                                    {{$value['status']}}
                                </td>
                                <th class="column-title">
                                    {{$value['waste_total']}}
                                </th>
                                <th class="column-title">
                                    {{$value['recycle_total']}}
                                </th>
                                <th class="column-title">
                                    {{$value['waste_target']}}
                                </th>
                                <th class="column-title">
                                    {{$value['employee_name']}}
                                </th>
                            </tr>
                            @endforeach
                        <tbody>   
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 col-sm-12 col-xs-12" id="chartStatus">
        <div id="chart_div" style="width:100%; height: 500px;"></div>
    </div>
</div>

<form id="search-filter" action="" method="GET"></form>
@endsection 
@section('js')
<!-- Datatables -->
<script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<script>
$('#example').DataTable({
    "bPaginate": true,
    "ordering": false,
    "bLengthChange": false,
    "pageLength": 20,
    "bFilter": true,
    "bInfo": false,
    language: {
        search: "",
        searchPlaceholder: "Search by any keyword",
        // paginate: {
        //     next: '«',
        //     previous: '»'
        // }
    },
    "bAutoWidth": false,
});

$("#newSearchPlace").html($(".dataTables_filter"));

$(".filter-search").change(function () {

    var base_url = $(this).val() === ''
            ? BaseUrl + "/recycle-report"
            : BaseUrl + "/fillter/" + $(this).val();

    $("#search-filter").attr('action', base_url);
    $("#search-filter").submit();
});
</script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<script language = "JavaScript">
google.charts.load('current', {packages: ['corechart', 'bar']});
google.charts.setOnLoadCallback(drawTrendlines);
function drawTrendlines() {
    var data = google.visualization.arrayToDataTable(<?php echo $chartarr; ?>);
    var options = {
        legend: {position: 'top', maxLines: 2},
        bar: {groupWidth: '50%'},
        isStacked: true,
        series: {
            2: {type: 'line'},
            3: {type: 'line'},
        }, colors: ['#8FBC8F', '#228B22', '#FFFF00', '#FF0000']
    };

    var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
    chart.draw(data, options);
}
</script>
<script language = "JavaScript">
    function init_daterangepicker() {
        var proid = document.getElementById("property_id").value;
        if ("undefined" != typeof $.fn.daterangepicker) {
            console.log("init_daterangepicker");
            var a = function (a, b, c) {
                console.log(a.toISOString(), b.toISOString(), c), $("#reportrange span").html(a.format("MMMM D, YYYY") + " - " + b.format("MMMM D, YYYY"))
            },
                    b = {
                        startDate: moment().subtract(29, "days"),
                        endDate: moment(),
                        minDate: "01/01/2012",
                        maxDate: <?php echo date('Y-m-d'); ?>,
                        dateLimit: {
                            days: 60
                        },
                        showDropdowns: !0,
                        showWeekNumbers: !0,
                        timePicker: !1,
                        timePickerIncrement: 1,
                        timePicker12Hour: !0,
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
            $("#reportrange span").html(moment().subtract(29, "days").format("MMMM D, YYYY") + " - " + moment().format("MMMM D, YYYY")), $("#reportrange").daterangepicker(b, a), $("#reportrange").on("show.daterangepicker", function () {
                console.log("show event fired")
            }), $("#reportrange").on("hide.daterangepicker", function () {
                console.log("hide event fired")
            }), $("#reportrange").on("apply.daterangepicker", function (a, b) {
                var tokenGen = $('meta[name="csrf_token"]').attr('content');
                $.ajax({
                    type: 'POST',
                    url: 'chartajax',
                    data: {tokenid: 2, property: proid, formdate: b.startDate.format("MMMM D, YYYY"), todate: b.endDate.format("MMMM D, YYYY"), _token: tokenGen},
                    success: function (data) {
                        $("#chart_div").html(data);
                    }
                });
                console.log("apply event fired, start/end dates are " + b.startDate.format("MMMM D, YYYY") + " to " + b.endDate.format("MMMM D, YYYY"))
            }), $("#reportrange").on("cancel.daterangepicker", function (a, b) {
                console.log("cancel event fired")
            }), $("#options1").click(function () {
                $("#reportrange").data("daterangepicker").setOptions(b, a)
            }), $("#options2").click(function () {
                $("#reportrange").data("daterangepicker").setOptions(optionSet2, a)
            }), $("#destroy").click(function () {
                $("#reportrange").data("daterangepicker").remove()
            })
        }
    }

    $(window).resize(function () {
        drawTrendlines();
    });
</script>
@endsection
