@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('content')
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <div class="col-md-6"><h2>Pickups <small></small></h2></div> 
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings">                            
                                <th class="column-title">S.No </th>
                                <th class="column-title">Property Detail </th>
                                <th class="column-title">User Name </th>
                                <th class="column-title">Email </th>
                                <th class="column-title">Activity</th>
                                <th class="column-title">Info</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($activitylogs) && ($activitylogs->isNotEmpty()))
                            @foreach($activitylogs as $activitylog )
                            <tr class="even pointer">   
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <b>Property Name:</b>
                                    @isset($activitylog->unit->getPropertyDetail->name)
                                    {{ucwords($activitylog->unit->getPropertyDetail->name)}}
                                    @endisset<br/>

                                    <b>Property Address:</b>
                                    @isset($activitylog->unit->getPropertyDetail->address)
                                    {{ucwords($activitylog->unit->getPropertyDetail->address.", ".$activitylog->unit->getPropertyDetail->city.', '.$activitylog->unit->getPropertyDetail->getState->name.', '.$activitylog->unit->getPropertyDetail->zip)}}
                                    @endisset<br/>

                                    @if(isset($activitylog->unit->getPropertyDetail->type) && ($activitylog->unit->getPropertyDetail->type == 1 || $activitylog->unit->getPropertyDetail->type == 4)) 
                                    <b>Unit Address:</b> 
                                    @isset($activitylog->unit->address1) 
                                    {{ ucfirst($activitylog->unit->address1) }}.
                                    @endisset
                                    <br/>

                                    @elseif(isset($activitylog->unit->getPropertyDetail->type) && ($activitylog->unit->getPropertyDetail->type == 3 || $activitylog->unit->getPropertyDetail->type == 2))
                                    <b>Building Name:</b> 
                                    @isset($activitylog->unit->getBuildingDetail->building_name)
                                    {{ ucfirst($activitylog->unit->getBuildingDetail->building_name) }}.
                                    @endisset
                                    <br/>
                                    @isset($activitylog->unit->getBuildingDetail->address)  
                                    <b>Building Address:</b> {{ ucfirst($activitylog->unit->getBuildingDetail->address) }}.<br/>
                                    @endisset
                                    @endif
                                    <b>Unit:</b> 
                                    @isset($activitylog->unit->unit_number)
                                    {{$activitylog->unit->unit_number}}<br/> 
                                    @endisset
                                    <b>Property Type:</b> 
                                    @isset($activitylog->unit->getPropertyDetail->type)
                                    @if($activitylog->unit->getPropertyDetail->type == 1)
                                    Single Family Home 
                                    @elseif($activitylog->unit->getPropertyDetail->type == 2)
                                    Garden Style Apartment
                                    @elseif($activitylog->unit->getPropertyDetail->type == 3)
                                    High Rise Apartment
                                    @elseif($activitylog->unit->getPropertyDetail->type == 4)
                                    Townhome        
                                    @endif
                                    @endisset
                                </td>
                                <td>
                                    @isset($activitylog->logs->firstname)
                                    {{$activitylog->logs->firstname}} {{$activitylog->logs->lastname}}
                                    @endisset
                                </td>
                                <td>
                                    @isset($activitylog->logs->email)
                                    {{$activitylog->logs->email}}
                                    @endisset
                                </td>
                                <td>{{$activitylog->text}}</td>
                                <td>
                                    <b>Created At</b>: {{\Carbon\Carbon::parse($activitylog->created_at)->timezone(getUserTimezone())->format('m-d-Y h:i A')}}<br/>
                                    <b>Updated At:</b> {{\Carbon\Carbon::parse($activitylog->updated_at)->timezone(getUserTimezone())->format('m-d-Y h:i A')}}
                                </td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            {{$activitylogs->links()}}
        </div>
    </div>
</div>
@endsection 