@extends('layouts.user_app')
@extends('layouts.user_menu')
@php $offset = paginateOffset($reportIssue->currentPage(),15); @endphp
@section('content')   
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">

            <div class="x_title">
                <h2>Manage Exceptions</h2>
                @role(['admin','property_manager'])
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <a class="btn btn-primary pull-right"
                           href="{{url('report-issue-reason')}}">
                            + Add Exception Type
                        </a>
                    </li>
                </ul>
                @endrole
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings">
                                <th class="column-title" width="5%">S.No</th>
                                <th class="column-title" width="10%">Title</th>
                                <th class="column-title" width="20%">Description</th>
                                <th class="column-title" width="20%">Exception Type</th>
                                <th class="column-title" width="10%">Employee</th>
                                <th class="column-title" width="20%">Property Detail</th>
                                <th class="column-title" width="5%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($reportIssue) && !empty($reportIssue))
                            @foreach($reportIssue as $report)
                            <tr class="even pointer">  
                                <td>{{$offset++}}</td>
                                <td>{{ ucwords($report->title) }}</td>
                                <td width="30%">{{ ucwords($report->description) }}</td>
                                <td>
                                    @isset($report->getReportReason->reason)
                                    {{ ucfirst($report->getReportReason->reason) }}
                                    @endisset
                                </td>
                                <td>

                                    @if(isset($report->getUser->title) && !empty($report->getUser->title))
                                    {{ucwords($report->getUser->title)}}
                                    @endif
                                    @if(isset($report->getUser->firstname) && !empty($reported->getUser->firstname))
                                    {{ucwords($report->getUser->firstname)}}
                                    @endif
                                    @if(isset($report->getUser->lastname) && !empty($report->getUser->lastname))
                                    {{ucwords($report->getUser->lastname)}}
                                    @endif

                                </td>
                                <td>
                                    @if(isset($report->getProperty->name) && !empty($report->getProperty->name))
                                    Property: {{ucwords($report->getProperty->name)}}
                                    @endif 

                                    @if(isset($report->getBuilding->building_name) && !empty($report->getBuilding->building_name))
                                    <br/>Building: {{ucwords($report->getBuilding->building_name)}}
                                    @endif
                                    <br>
                                    Created:  {{\Carbon\Carbon::parse($report->updated_at)->timezone(getUserTimezone())->format('m-d-Y h:i A')}}                   
                                </td>
                                <td>
                                    @if($report->status == 0)
                                    <a href="{{url('mark-issue-exclude/'.$report->id)}}" onclick="return confirm('Are you sure you want to exclude this property?')"  title='Exclude Property'><li class="fa fa-bug"></li></a>@endif
                                </td>
                            </tr>
                            @endforeach
                            @endif 
                        </tbody>
                    </table>
                </div>
                <span style="float: right;">{{ $reportIssue->links() }}</span> 
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

