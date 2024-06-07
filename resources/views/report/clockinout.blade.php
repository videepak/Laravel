@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')
<link href="{{url('assets/css/bootstrap-editable.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.css" rel="stylesheet">
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

</style>
@endsection
@section('content')   
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
              <h2 style="font-size: initial;">
                <i class="fa fa-bar-chart"></i> Report Clock In/Out
              </h2>
              <ul class="nav navbar-right panel_toolbox">
                <li>
                  <a class="btn btn-primary pull-right excel-option" >
                    <i class="fa fa-file-excel-o" aria-hidden="true"></i> Excel
                  </a>
                </li>
                <li>
                  <div id="reportrange" class="pull-right" style="background: #fff;cursor: pointer;padding: 5px 10px;border: 1px solid #ccc;margin-bottom: 1%;">
                        <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                        <span style="color: #73879C"></span>
                    </div>
                </li>
              </ul>
              <div class="clearfix"></div>
          </div> 
          <div class="x_content">
                <div class="table-responsive">
                    <table id="example" class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings"> 
                                <th class="column-title">S.no </th>
                                <th class="column-title">
                                  <select class="form-control input-sm filter"
                                    style="width: 72%;" id="name">
                                        <option value="">Name</option>
                                        @foreach($users as $user)
                                        <option value="{{ucwords($user->id)}}">{{ucwords($user->name)}}</option>
                                        @endforeach
                                  </select> 
                                </th>
                                <th class="column-title">
                                  <select class="form-control input-sm filter"
                                    style="width: 72%;" id="reporting">
                                        <option value="">Reporting Manager</option>
                                        @foreach($reporting as $user)
                                        <option value="{{ucwords($user->id)}}">{{ucwords($user->name)}}</option>
                                        @endforeach
                                  </select>
                                </th>
                                <th class="column-title">Clockin </th>
                                <th class="column-title">Clockout </th>
                                <th class="column-title">Reason </th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div> 
        </div>
    </div>
</div>

@endsection 
@section('js')
<script src="{{url('assets/js/bootstrap-editable.js')}}"></script>


<script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<script src="{{url('assets/trashscanjs/clock.inout.list.js')}}"></script>

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
        $('#example').DataTable().destroy();
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
