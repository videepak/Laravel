@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('content')
@yield('menu')     
<div class="right_col" role="main">
    <div class="row">
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>#{{$unit[0]->unit_number}} - Unit History</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <section class="panel">

                            <div class="panel-body">
                                <h3 class="green"><i class="fa fa-building"></i> 
                                    @isset($unit[0]->getPropertyDetail->name)
                                    {{$unit[0]->getPropertyDetail->name}}
                                    @endisset
                                </h3>

                                <br>
                                <div class="project_detail" style="font-size: 14px;line-height: 28px;">
                                    <p class="title">Resident :</p>
                                    @if(isset($residentDetails) && !empty($residentDetails))
                                    <p><i class="fa fa-user"></i>
                                    {{$residentDetails->name}}
                                    <br/>
                                    <i class="fa fa-envelope"></i>
                                    {{$residentDetails->email}}
                                    <br />
                                    <i class="fa fa-phone"></i>
                                    {{$residentDetails->mobile}}
                                    <br />   
                                    @else
                                    <p><i class="fa fa-user"></i>
                                        {{'Not mentioned'}}
                                    <br/>
                                    <i class="fa fa-envelope"></i>
                                        {{'Not mentioned'}}
                                    <br />
                                    <i class="fa fa-phone"></i>
                                        {{'Not mentioned'}}
                                    <br />   
                                    @endif
                                </div>
                                <div class="project_detail" style="font-size: 14px;line-height: 28px;">
                                    <p class="title">Customer :</p>
                                    <p><i class="fa fa-user"></i> 
                                        @isset($unit[0]->getPropertyDetail->getCustomer->name)
                                        {{$unit[0]->getPropertyDetail->getCustomer->name}}
                                        @endisset
                                        @isset($unit[0]->getPropertyDetail->getCustomer->lastname)
                                        {{$unit[0]->getPropertyDetail->getCustomer->lastname}}
                                        @endisset
                                        <br/>
                                        <i class="fa fa-envelope"></i> 
                                        @isset($unit[0]->getPropertyDetail->getCustomer->email)
                                        {{$unit[0]->getPropertyDetail->getCustomer->email}}
                                        @endisset
                                        <br/>
                                        <i class="fa fa-phone"></i> 
                                        @isset($unit[0]->getPropertyDetail->getCustomer->phone)
                                        {{$unit[0]->getPropertyDetail->getCustomer->phone}}
                                        @endisset
                                        <br/>
                                        <i class="fa fa-home"></i> 
                                        @isset($unit[0]->getPropertyDetail->getCustomer->address)
                                        {{ucwords($unit[0]->getPropertyDetail->getCustomer->address)}},
                                        {{ucwords($unit[0]->getPropertyDetail->getCustomer->city)}},
                                        {{$unit[0]->getPropertyDetail->getCustomer->zip}}
                                        @endisset
                                    </p>
                                    <p class="title">Company :</p>
                                    <p>
                                        @isset($subscriber_details->company_name)
                                        <i class="fa fa-industry"></i> 
                                        {{$subscriber_details->company_name}}
                                        @endisset
                                        <br/>
                                        @isset($subscriber_details->address)
                                        <i class="fa fa-home"></i> 
                                        {{$subscriber_details->address}},
                                        {{$subscriber_details->city}},
                                        {{$subscriber_details->zip}}
                                        @endisset
                                    </p>

                                    <br>
                                    <p class="title">Property Address :</p>

                                    <ul class="list-unstyled project_files">
                                        <li>
                                            <a href="javascript:void(0);"><i class="fa fa-home"></i>
                                                @isset($unit[0]->getPropertyDetail->address)
                                                {{$unit[0]->getPropertyDetail->address}},
                                                @endisset
                                                @isset($unit[0]->getPropertyDetail->city)
                                                {{$unit[0]->getPropertyDetail->city}},
                                                @endisset    
                                                @isset($unit[0]->getPropertyDetail->getState->name)
                                                {{$unit[0]->getPropertyDetail->getState->name}},
                                                @endisset
                                                @isset($unit[0]->getPropertyDetail->zip)
                                                {{$unit[0]->getPropertyDetail->zip}}
                                                @endisset
                                            </a>
                                        </li>
                                    </ul>

                                    @if(@isset($unit[0]->getPropertyDetail->type) && ($unit[0]->getPropertyDetail->type == 2 || $unit[0]->getPropertyDetail->type == 3)) 
                                    <h5>Building Detail:</h5> {{ucwords($unit[0]->getBuilding)}} 
                                    <ul class="list-unstyled project_files"> 
                                        <li>
                                            @isset($unit[0]->getBuildingDetail->building_name)
                                            <i class="fa fa-building"></i>
                                            {{ucwords($unit[0]->getBuildingDetail->building_name)}}
                                            @endisset
                                        </li>
                                        <li>
                                            @isset($unit[0]->getBuildingDetail->address)
                                            <i class="fa fa-home"></i>
                                            {{ucwords($unit[0]->getBuildingDetail->address)}}
                                            @endisset
                                        </li>

                                    </ul>
                                    @endif

                                    @if(@isset($unit[0]->getPropertyDetail->type) && $unit[0]->getPropertyDetail->type == 4)
                                    <p class="title">Unit Detail:</p>
                                    <ul class="list-unstyled project_files">
                                        <li>
                                            <a href=""><i class="fa fa-home"></i>
                                                @isset($unit[0]->address1)
                                                {{ucwords($unit[0]->address1)}},
                                                @endisset
                                            </a>
                                        </li>

                                    </ul>
                                    @endif
                                    <br>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="col-md-9 col-sm-9 col-xs-12">
                        <ul class="stats-overview">
                            <li>
                                <span class="name" style="font-size: initial;"> Activity </span>
                                <span class="value text-success"> 
                                    @isset($unit[0]->getActivityByBarcode)
                                    {{$unit[0]->getActivityByBarcode->count()}}
                                    @endisset
                                </span>
                            </li>
                            <li>
                                <span class="name" style="font-size: initial;"> Recycle Total </span>
                                <span class="value text-success"> {{$recycle_weight}} </span>
                            </li>
                            <li class="hidden-phone">
                                <span class="name" style="font-size: initial;"> Submitted Violation </span>
                                <span class="value text-success"> 
                                    @isset($unit[0]->getViolationByBarcode)
                                        {{$unit[0]->getViolationByBarcode->count()}}
                                    @endisset
                                </span>
                            </li>
                        </ul>
                        <br>
                        <div class="" role="tabpanel" data-example-id="togglable-tabs">
                            <ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
                                <li role="presentation" class=""><a href="#tab_content3" role="tab" id="violation-tab" data-toggle="tab" aria-expanded="false">Current Violation</a> 
                                <li role="presentation" class="active"><a href="#tab_content2" role="tab" id="profile-tab" data-toggle="tab" aria-expanded="false">Previous Violation</a>
                                <li role="presentation" class=""><a href="#tab_content1" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">Recent Activity</a>
                                </li>
                            </ul>
                            <div id="myTabContent" class="tab-content">
                                <div role="tabpanel" class="tab-pane fade" id="tab_content1" aria-labelledby="home-tab">    
                                    @if(isset($unit[0]->getActivityByBarcode) && $unit[0]->getActivityByBarcode->isNotEmpty())
                                    <ul class="messages">

                                        @foreach($unit[0]->getActivityByBarcode as $activity)

                                        <li>

                                            @php
                                            $filename = public_path('uploads/user/' . $activity->getUserDetail->image_name);
                                            @endphp
                                            @if(isset($activity->getUserDetail) && $activity->getUserDetail->image_name == null || !file_exists($filename))
                                            <img src="{{url('/uploads/user/default.png')}}" class="avatar">

                                            @else
                                            <img src="{{url('/uploads/user/'.$activity->getUserDetail->image_name)}}" class="avatar">
                                            @endif


                                            <div class="message_wrapper">
                                                <b style="font-size: 14px;">
                                                    @if(!empty($activity->getUserDetail))
                                                    {{$activity->getUserDetail->title}}
                                                    {{$activity->getUserDetail->firstname}}
                                                    {{$activity->getUserDetail->lastname}}
                                                    @endif
                                                </b><br/>
                                                <blockquote class="message" style="padding-top: 1%;font-size: 107%;">{{$activity->text}}</blockquote>
                                                <br>
                                                <p class="url">
                                                    <span class="fs1 text-info" aria-hidden="true" data-icon=""></span>
                                                    <a href="#"><i class="fa fa-clock-o"></i> 
                                                        {{\Carbon\Carbon::parse($activity->updated_at)->timezone(getUserTimezone())->format('m-d-Y h:i A')}}                     
                                                    </a>
                                                </p>
                                            </div>
                                        </li>

                                        @endforeach


                                    </ul>
                                    @else      
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="error-template centered">
                                                <center><h2>No Record Found</h2></center>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                </div>
                                <div role="tabpanel" class="tab-pane fade active in" id="tab_content2" aria-labelledby="profile-tab">

                                    <table class="data table table-striped no-margin">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Image Count</th>
                                                <th>Employee</th>
                                                <th >Action/Reason</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(@isset($getPreviousViolation) && !empty($getPreviousViolation))
                                            @foreach($getPreviousViolation as $violation)
                                            <tr>
                                                <td>{{$loop->iteration}}</td>
                                                <td>
                                                    @if(isset($violation->images))
                                                    @if(count($violation->images) > 0)
                                                    <a href="javascript:void(0);" data-type="images" data-toggle="modal" data-remote="{{url('violationimages/'.$violation->id)}}" data-target="#img-model">{{count($violation->images)}}</a>
                                                    @else
                                                    {{count($violation->images)}}
                                                    @endif
                                                    @endif
                                                </td>
                                                <td>
                                                    @isset($violation->getUser)
                                                    {{$violation->getUser->title}}
                                                    {{$violation->getUser->firstname}}
                                                    {{$violation->getUser->lastname}}
                                                    @endisset
                                                </td>
                                                <td class="vertical-align-mid">
                                                    @if(!empty($violation->getAction->action))
                                                    <b>Action:</b> {{$violation->getAction->action}}<br/>
                                                    @endif
                                                    @if(!empty($violation->getReason->reason))
                                                    <b>Reason:</b> {{$violation->getReason->reason}}
                                                    @endif
                                                </td>
                                                <td class="hidden-phone">

                                                    {{\Carbon\Carbon::parse($violation->created_at)->timezone(getUserTimezone())->format('m-d-Y h:i A')}}                     
                                                </td>

                                            </tr>
                                            @endforeach
                                            @else
                                            <tr>
                                                <td align='center' colspan="5">No Record Found</td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>

                                </div>

                                <div role="tabpanel" class="tab-pane fade" id="tab_content3" aria-labelledby="violation-tab"> 
                                    <table class="data table table-striped no-margin">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Image Count</th>
                                                <th>Employee</th>
                                                <th >Action/Reason</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>    
                                        @if(isset($getCurrentViolation) && !empty($getCurrentViolation))
                                            @foreach($getCurrentViolation as $currentViolation)
                                            <tr>
                                                <td>{{$loop->iteration}}</td>
                                                <td>
                                                    @if(isset($currentViolation->images))
                                                        @if(count($currentViolation->images) > 0)
                                                            <a 
                                                                href="javascript:void(0);" data-type="images"
                                                                data-toggle="modal"
                                                                data-remote="{{url('violationimages/'.$currentViolation->id)}}" data-target="#img-model"
                                                            >
                                                                {{count($currentViolation->images)}}
                                                            </a>
                                                        @else
                                                            {{count($currentViolation->images)}}
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>
                                                    @isset($currentViolation->getUser)
                                                    {{$currentViolation->getUser->title}}
                                                    {{$currentViolation->getUser->firstname}}
                                                    {{$currentViolation->getUser->lastname}}
                                                    @endisset
                                                </td>
                                                <td class="vertical-align-mid">
                                                    @if(!empty($currentViolation->getAction->action))
                                                    <b>Action:</b> {{$currentViolation->getAction->action}}<br/>
                                                    @endif
                                                    @if(!empty($currentViolation->getReason->reason))
                                                    <b>Reason:</b> {{$currentViolation->getReason->reason}}
                                                    @endif
                                                </td>
                                                <td class="hidden-phone">

                                                    {{\Carbon\Carbon::parse($currentViolation->created_at)->timezone(getUserTimezone())->format('m-d-Y h:i A')}}                     
                                                </td>

                                            </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td align='center' colspan="5">No Record Found</td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>

                    </div>



                </div>
            </div>
        </div>
    </div>



</div>
<!--Image Model :Start-->
<div id="img-model" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Image View</h4>
            </div>
            <div class="modal-body">
                <p><img src="" id="img-show-model" class="img-responsive" style="margin: 0 auto;"></p>
            </div>
        </div>
    </div>
</div>   
<!--Image Model :End-->
@endsection 