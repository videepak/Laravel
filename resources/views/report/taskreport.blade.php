@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')
<!-- Datatables -->
<link href="{{url('assets/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">
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
                        <h2>Task Report</h2>
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
                        <table class="table table-striped jambo_table bulk_action" id="example">
                            <thead>
                                <tr class="headings">    
                                    <th class="column-title">S.no </th>
                                    <th class="column-title">Task </th>
                                    <th class="column-title">
                                        <select name='property' 
                                        class="form-control filter-search input-sm"
                                        style="width: 40%;" id="pro-select2">
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
                                    <th class="column-title">Completion Date </th>
                                    <th class="column-title">
                                        <select name='property' 
                                        class="form-control filter-search input-sm"
                                        style="width: 90%;" id="fre-select2">
                                        Frequency
                                        <option value="">Frequency</option>
                                        <option value="1">Daliy</option>
                                        <option value="2">Weekly</option>
                                        <option value="3">Monthly</option>
                                        </select>
                                    </th>
                                    <th class="column-title">
                                    <select class="form-control filter-search input-sm"
                                        style="width: 30%;" id="scanBy">
                                        <option value="">Scan By</option>
                                        @isset($scanBy)
                                            @foreach($scanBy as $scan)
                                                <option value='{{$scan->id}}'>
                                                {{ucwords($scan->name)}}
                                                </option>
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
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.3/js/dataTables.buttons.min.js
"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js
"></script>
<script src="https://cdn.datatables.net/buttons/1.6.3/js/buttons.html5.min.js"></script>
<script>
        $('.excel-option').click(function(){
        $('.buttons-excel').trigger('click');
    }); 
function load_data() {

let range = $("#reportrange").find("span").text().split('-');
let property = $('#pro-select2').val() != "" ? $('#pro-select2').val()
    : new URLSearchParams(window.location.search).get('property');

    $('#example').DataTable({
        dom: 'lBfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                exportOptions: { 
                    format: {
                        header: function ( data, column, row ) {
                            if(column === 1 || column === 5 || column === 7) {
                                return data.substring(data.indexOf("value")+9,data.indexOf("</option"));
                            } else {
                                return data;
                            }
                        }
                    }
                },
                customize: function(xlsx) {
                    $('col', xlsx.xl.worksheets['sheet1.xml']).each(function () {
                        $(this).attr('width', 20);
                    });       
                }
            },
        ],
        "bPaginate": true,
        "ordering": false,
        "bLengthChange": true,
        "pageLength": 10,
        "bFilter": true,
        "bInfo": true,
        language: {
            search: "",
            searchPlaceholder: "Search by task",
            //"processing": "<i class='fa fa-refresh fa-spin'></i>",
            // paginate: {
            //     next: '«',
            //     previous: '»'
            // },
        },
        "bAutoWidth": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "{{ url('report/task-data') }}",
            "type": "POST",
            "data":{
                _token: $('meta[name="csrf_token"]').attr('content'),
                id: property,
                startTime: range[0],
                endTime: range[1],
                scanBy: $('#scanBy').val(),
                fre: $('#fre-select2').val(),
                media: $('#media').val(),
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

function calTotal() {

   let range = $("#reportrange").find("span").text().split('-');
       
   $.ajax({
       type: 'POST',
       url: BaseUrl + '/report/cal-pickup',
       data: {
           _token: $('meta[name="csrf_token"]').attr('content'),
           id: $('#pro-select2').val(),
           startTime: range[0], 
           endTime: range[1],
       },        
       success: function (data) {
           $('.total-bins').text(data.totalBin);
           $('.total-pickup').text(data.totalPickup);
       }
   });
}

$(document).ready(
    function() {
        load_data();
        calTotal();

        $('#example_filter input').addClass('set-width');
    
        $(".filter-search").change(
            function () {
                $('#example').DataTable().destroy();
                load_data();
            }
        );      

        $("#pro-select2").change(function () {
            calTotal();                
        });  

        // #1498: Home Page (Web App): Start
        if (new URLSearchParams(window.location.search).get('property') != null) {
            let urlString = new URLSearchParams(window.location.search).get('property');
            $("#pro-select2").children('[value="' + urlString + '"]').prop("selected", true);
        }
        // #1498: Home Page (Web App): End
    }
);


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
            buttonClasses: ["btn btn-default"],
            applyClass: "btn-small btn-primary",
            cancelClass: "btn-small",
            format: "m-d-Y",
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
            
             $('#example').DataTable().destroy();
             load_data();
             calTotal();
            
            // $.ajax({
            //     type: 'POST',
            //     url: BaseUrl + '/chartajax',
            //     data: {
            //         tokenid: 1, 
            //         formdate: b.startDate.format("MMMM D, YYYY"), 
            //         todate: b.endDate.format("MMMM D, YYYY"), 
            //         _token: $('meta[name="csrf_token"]').attr('content')
            //     },
            //     beforeSend: function( xhr ) {
            //         showLoader();
            //     },         
            //     success: function (data) {
            //         $("#chart_div").html(data);
            //         hideLoader();
            //     }
            // });
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

$(window).resize(function () {
    drawTrendlines();
});
</script>
@endsection
