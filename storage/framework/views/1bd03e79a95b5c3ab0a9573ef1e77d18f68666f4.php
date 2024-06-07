<?php $__env->startSection('content'); ?>
<div class="right_col" role="main">
<?php if (\Entrust::hasRole('property_manager')) : ?>
    <?php echo $__env->make('manager.managerdashboard', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>            
<?php else: ?>
    <!-- Dashboard Metric: Start -->
    <div class="row">
        <div class="col-md-2 col-sm-2 col-xs-12 pull-right">
            <input type="text" class="form-control has-feedback-left" id="metric-date" readonly>
            <span class="fa fa-calendar-o form-control-feedback left"
            style="margin-top: 5px" aria-hidden="true"></span>
        </div>
    </div>
    <div class="row tile_count text-center metric" style='margin-left: 3%;'></div>
    <!-- Dashboard Metric: End -->
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="dashboard_graph">
                <div class="row x_title">
                    <div class="col-md-7">
                        <h3>Daily Reports</h3>
                    </div>
                    <div class="col-md-5">                  
                        <select id="multiple-checkboxes"
                            class="toggle-vis"
                            multiple="multiple">  
                            <option value="2">Units Serviced</option>  
                            <option value="3">Total Active Units</option>  
                            <option value="4">Route Checkpoints Completed</option>  
                            <option value="5">Total Route Checkpoints</option>  
                            <option value="6">Buildings Serviced</option>  
                            <option value="7">Total Buildings</option>  
                            <option value="8">Last Day Serviced Duration</option>  
                            <option value="9">Tasks Completed</option>
                            <option value="10">Total Task</option>
                            <option value="11">Incomplete Service Checkouts</option> 
                        </select>  
                        <div id="report-daliy" class="pull-right" 
                            style="background: #fff;cursor: pointer;padding: 5px 10px;border: 1px solid #ccc;margin-bottom: 1%;">
                            <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                            <span style="color: #73879C"></span>
                        </div>
                        <span id="dialPlanListTable_filter"></span>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div style="width:100%; height: auto;">
                        <div class="table-responsive">
                            <table id="example" class="table table-striped ">
                                <thead>
                                    <tr class="headings">
                                        <th class="column-title">S.No</th>
                                        <th class="column-title">Property</th>
                                        <th class="column-title">Units Serviced</th>
                                        <th class="column-title">Total Active Units</th>
                                        <th class="column-title">Route Checkpoints Completed</th>
                                        <th class="column-title">Total Route Checkpoints</th>
                                        <th class="column-title">Buildings Serviced</th>
                                        <th class="column-title">Total Buildings</th>
                                        <th class="column-title">Last Day Serviced Duration</th>
                                        <th class="column-title">Tasks Completed</th>
                                        <th class="column-title">Total Tasks</th>
                                        <th class="column-title">Incomplete Service Checkouts</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
    <br>



    <div class="row daliy-report" style="display: none;">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="dashboard_graph">

                <div class="row x_title">
                    <div class="col-md-6">
                        <h3 id="heading-graph">Daily Report</h3>
                    </div>
                    <div class="col-md-6">
                        <button 
                            style="float: right;"
                            type="button"
                            id="png"
                            class="btn btn-primary">
                            Print
                        </button>
                    </div>

                    
                </div>
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div id="chart_div" style="width:100%; height: 500px;"></div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
    <br>
    <?php endif; // Entrust::hasRole ?>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css">
<style>
.dropdown-menu>.active>a, .dropdown-menu>.active>a:focus, .dropdown-menu>.active>a:hover {
    background-color: #536A7F !important;
}
.x_title span {
   color: #000000;
}
.pagination>.active>a, .pagination>.active>a:focus, .pagination>.active>a:hover, .pagination>.active>span, .pagination>.active>span:focus, .pagination>.active>span:hover {
    color: #fff !important;
}
.dataTables_processing {
    height: 49px;
}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>   

<?php if (\Entrust::hasRole('property_manager')) : ?>
<!-- This script only for property manager panel: Start -->
<script>
$('#tags').autocomplete({
    serviceUrl: "<?php echo e(url('/property-manager/search-unit')); ?>",
    onSelect: function (suggestion) {
        window.location.href = '<?php echo e(url("/property-manager/unit-history")); ?>/' + suggestion.data;
    }
});
</script>
<!-- This script only for property manager panel: End -->
<?php else: ?>
<script src="<?php echo e(url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')); ?>"></script>
<script src="<?php echo e(url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')); ?>"></script>
<script src="<?php echo e(url('assets/trashscanjs/dashboard.js')); ?>"></script>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>  
<script language = "JavaScript">
var daliyReport = "<?php echo $user->daliy_status; ?>";
//google.charts.load('current', {packages: ['corechart', 'bar']});
//google.charts.setOnLoadCallback(drawTrendlines);

// function drawTrendlines() {
    
//     var data = google.visualization.arrayToDataTable();

//     var options = {

//         legend: {position: 'top', maxLines: 2},
//         bar: {groupWidth: '50%'},
//         isStacked: true,
//         series: {
//             2: {type: 'line'},
//             3: {type: 'line'},
//         },
//         colors: ['#8FBC8F', '#228B22', '#FFFF00', '#FF0000', '#ADD8E6']

//     };

//     var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
//     chart.draw(data, options);
// }

function init_daterangepicker() {

    if ("undefined" != typeof $.fn.daterangepicker) {        
        var a = function (a, b, c) {
            $("#report-daliy span").html(a.format("MMMM D, YYYY") + " - " + b.format("MMMM D, YYYY"))
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

        $("#report-daliy span").html(moment().subtract(29, "days").format("MMMM D, YYYY") + " - " + moment().format("MMMM D, YYYY")), 
        $("#report-daliy").daterangepicker(b, a), 
        $("#report-daliy").on(
            "show.daterangepicker",
            function () {
                //console.log("show event fired")
            }
        ),
        $("#report-daliy").on(
            "hide.daterangepicker",
            function () {
                //console.log("hide event fired")
            }
        ), 
        $("#report-daliy").on(
            "apply.daterangepicker",
            function (endDate) {
                $('#example').DataTable().destroy();
                loadTable();
                $("#chart_div").empty();
                $('.daliy-report').css('display', 'none');
                $('#heading-graph').html('Daily Report <small>(' + $('#report-daliy').find('span').text() + '</span>)');
            }
        ), 
        $("#report-daliy").on(
            "cancel.daterangepicker",
            function (a, b) {
            //console.log("cancel event fired")
            }
        ),
        $("#options1").click(
            function () {
                $("#report-daliy").data("daterangepicker").setOptions(b, a)
            }
        ),
        $("#options2").click(
            function () {
                $("#report-daliy").data("daterangepicker").setOptions(optionSet2, a)
            }
        ),
        $("#destroy").click(
            function () {
                $("#report-daliy").data("daterangepicker").remove()
            }
        )
    }




    // if ("undefined" != typeof $.fn.daterangepicker) {
    //     var a = function (a, b, c) {
    //         $("#reportrange span").html(a.format("MMMM D, YYYY") + " - " + b.format("MMMM D, YYYY"))
    //     },
    //     b = {
    //         startDate: moment().subtract(29, "days"),
    //         endDate: moment(),
    //         minDate: "01/01/2012",
    //         maxDate: <?php echo date('Y-m-d'); ?>,
    //         dateLimit: {
    //             days: 60
    //         },
    //         showDropdowns: false,
    //         showWeekNumbers: false,
    //         timePicker: false,
    //         timePickerIncrement: false,
    //         timePicker12Hour: false,
    //             ranges: {
    //                 Today: [moment(), moment()],
    //                 Yesterday: [moment().subtract(1, "days"), moment().subtract(1, "days")],
    //                 "Last 7 Days": [moment().subtract(6, "days"), moment()],
    //                 "Last 30 Days": [moment().subtract(29, "days"), moment()],
    //                 "This Month": [moment().startOf("month"), moment().endOf("month")],
    //                 "Last Month": [moment().subtract(1, "month").startOf("month"), moment().subtract(1, "month").endOf("month")]
    //             },
    //             opens: "left",
    //             buttonClasses: ["btn btn-default"],
    //             applyClass: "btn-small btn-primary",
    //             cancelClass: "btn-small",
    //             format: "MM/DD/YYYY",
    //             separator: " to ",
    //             locale: {
    //                 applyLabel: "Submit",
    //                 cancelLabel: "Clear",
    //                 fromLabel: "From",
    //                 toLabel: "To",
    //                 customRangeLabel: "Custom",
    //                 daysOfWeek: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"],
    //                 monthNames: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
    //                     firstDay: 1
    //                 }
    //             };
    //         $("#reportrange span").html(moment().subtract(6, "days").format("MMMM D, YYYY") + " - " + moment().format("MMMM D, YYYY")),
    //         $("#reportrange").daterangepicker(b, a),
    //         $("#reportrange").on(
    //             "show.daterangepicker",
    //             function () {}
    //         ),
    //         $("#reportrange").on(
    //             "hide.daterangepicker",
    //             function () {
    //                 console.log("hide event fired")
    //             }
    //         ),
    //         $("#reportrange").on(
    //             "apply.daterangepicker",
    //             function (a, b) {
    //                 var tokenGen = $('meta[name="csrf_token"]').attr('content');
    //                 $.ajax({
    //                     type: 'POST',
    //                     url: 'chartajax',
    //                     data: {
    //                         tokenid: 1, 
    //                         formdate: b.startDate.format("MMMM D, YYYY"), 
    //                         todate: b.endDate.format("MMMM D, YYYY"), 
    //                         _token: $('meta[name="csrf_token"]').attr('content')
    //                     },
    //                     beforeSend: function () {
    //                         $('#chart_div').html(`<div class="row" style='margin: 0% 0% 3% -13%;'><i class="fa fa-spinner fa-spin" style="font-size:24px"></i></div>`);
    //                     },
    //                     success: function (data) {
    //                         $("#chart_div").html(data);
    //                     }
    //                 });
    //     }),
    //     $("#reportrange").on(
    //         "cancel.daterangepicker",
    //         function (a, b) {
    //             console.log("cancel event fired")
    //         }
    //     ),
    //     $("#options1").click(
    //         function () {
    //             $("#reportrange").data("daterangepicker").setOptions(b, a)
    //     }),
    //     $("#options2").click(
    //         function () {
    //             $("#reportrange").data("daterangepicker").setOptions(optionSet2, a)
    //         }
    //     ),
    //     $("#destroy").click(
    //         function () {
    //             $("#reportrange").data("daterangepicker").remove();
    //         }
    //     )
    // }
}

function mertic() {
    
    $.ajax({
        type: 'POST',
        url: '/dashboard/metrix/',
        data: {
            _token: $('meta[name="csrf_token"]').attr('content'),
            range: $('#metric-date').val()
        },
        beforeSend: function () {
            $('.metric').html(`<div class="row" style='margin: 0% 0% 3% -13%;'><i class="fa fa-spinner fa-spin" style="font-size:24px"></i></div>`);
        },
        success: function (data) {
            $('.metric').html(data);
            //hideLoader();
        } 
    });
}

$("#metric-date").daterangepicker({
    singleDatePicker: !0,
    singleClasses: "picker_4",
    timeZone: '<?php echo e(getUserTimezone()); ?>',
    maxDate: moment()
}).on("change", function () { 
    mertic();
});
// .change(function() {
//     mertic();
// });



$(window).load(function () {
    $('.ranges ul li:nth-child(3)').trigger('click');
    mertic();
});

$(window).resize(function () {
    drawTrendlines();
});
</script>
<?php endif; // Entrust::hasRole ?>
<?php $__env->stopSection(); ?>
 
<?php echo $__env->make('layouts.user_menu', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<?php echo $__env->make('layouts.user_app', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>