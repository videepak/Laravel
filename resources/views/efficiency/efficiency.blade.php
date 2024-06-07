@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')
<link href="{{url('assets/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">
<style>
    .highcharts-credits {
        display:none;
    }
    .col-centered {
        float: none;
        margin: 0 auto;
    }
    .error-template {padding: 40px 15px;text-align: center;}
    svg > g > g:last-child { pointer-events: none }

</style>
@endsection
@section('content')
<div class="right_col" role="main">
    <div class="x_panel">
        <div class="x_title">
            <div class="col-md-6">
                <h2>Service Quality Score</h2>
            </div>
            <div class="col-md-6" style="text-align: right;"></div>
            <div class="clearfix"></div>
        </div>
        @if($completed != 0 || $notCompleted != 0)
        <div class="row">
            <div class="col-md-12">
                <div id="piechart_3d" style="width: 900px; height: 500px;"></div>
            </div>
        </div>
        @else
        <div class="row">
            <div class="col-md-12">
                <div class="error-template">
                    <h2>No service score available.</h2>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
<form id="search-filter" action="" method="GET"></form>
@endsection 
@section('js')
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
google.charts.load("current", {packages: ["corechart"]});
google.charts.setOnLoadCallback(drawChart);
function drawChart() {
    var data = google.visualization.arrayToDataTable([
        ['Task', 'Walk Though Calculation'],
        ['Completed', @php echo $completed; @endphp],
        ['Not Completed', @php echo $notCompleted; @endphp],
    ]);
    var options = {
        title: 'Walk Through Completed Or Not',
        is3D: true,
    };
    var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
    chart.draw(data, options);
}
</script>
@endsection