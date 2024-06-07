@extends('layouts.user_app')
@extends('layouts.menu')
@section('css')
<link href="{{url('assets/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">
<link href="{{url('assets/css/responsive.dataTables.min.css')}}" rel="stylesheet">
@php $offset = paginateOffset($reportLogs->currentPage(),10); @endphp
@section('content')   
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">

            <div class="x_title">
                <h2>Report Logs</h2>
                
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings">
                                <th class="column-title" width="5%">S.No</th>
                                <th class="column-title" width="20%">Receiver</th>
                                <th class="column-title" width="20%">Body</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($reportLogs) && !empty($reportLogs))
                            @foreach($reportLogs as $report)
                            <tr class="even pointer">  
                                <td>{{$offset++}}</td>
                                <td>{{ json_decode($report->receiver)->email }}</td>
                                <td width="30%">Subject - {{ json_decode($report->body)->subject }} <br>
                                    Action Text - {{ json_decode($report->body)->actionText }} <br>
                                    Action Url - {{ json_decode($report->body)->actionUrl }}
                                </td>
                            </tr>
                            @endforeach
                            @endif 
                        </tbody>
                    </table>
                </div>
                <span style="float: right;">{{ $reportLogs->links() }}</span> 
            </div>
        </div>
    </div>
</div>
<form id="deleteEmployee" action="" method="POST">
    {{method_field('DELETE')}}
    {{ csrf_field() }}
</form>

@endsection 
@section('js')
<script>
    function deleteEmployee(element, event)
    {
        event.preventDefault();
        if (confirm('Are you sure you want to continue?'))
        {
            var url = $(element).attr('href');
            $('#deleteEmployee').attr('action', url);
            $('#deleteEmployee').submit();
        }
    }

    $('.makeaction').click(function () {
        $('#preloader').show();
    });
</script>
@endsection 

