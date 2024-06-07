@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')
<!-- Datatables -->
<link href="{{url('assets/vendors/bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css')}}" rel="stylesheet">
<style>
   .parsley-required{
   display: block;
   }
   .well {
    padding: 54px !important;
}
</style>
@endsection
@section('content')
<div class="right_col" role="main">
   <div class="col-md-12 col-sm-12 col-xs-12">
      <div class="x_panel">
         <div class="x_title">
            <h2><i class="fa fa-cog"></i> Settings <small></small></h2>
            <div class="clearfix"></div>
         </div>
         <div class="x_content">
            <div class="" role="tabpanel" data-example-id="togglable-tabs">
               <ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
                  @if($user->is_admin == getAdminId())
                     <li role="presentation" class="active"><a href="#tab_content1" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">Employee Schedule</a>
                     </li>
                  @endif
                  @if($user->role_id == getPropertyManageId())   
                     <li role="presentation" class=""><a href="#tab_content2" role="tab" id="profile-tab" data-toggle="tab" aria-expanded="false">Dashboard Metric Setting</a>
                     </li>
                  @endif
                     <li role="presentation" class="@if($user->role_id == 1 && $user->is_admin == 0) active in @endif }}"><a href="#tab_content3" role="tab" id="profile-tab2" data-toggle="tab" aria-expanded="false">Notification Settings</a>
                     </li>
                  @role('admin')
                     <li role="presentation" class=""><a href="#report-tab" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">Automated Service Report </a>
                     </li>
                  @endrole   
               </ul>
               <div id="myTabContent" class="tab-content">
                  @if($user->is_admin == getAdminId())
                     <div role="tabpanel" class="tab-pane fade active in" id="tab_content1" aria-labelledby="home-tab">
                        <form method="post" action="{{url('settings/default-employee-schedule')}}">
                           <div class="well" style="height:200px;">
                              {{csrf_field()}}
                                 <div class="col-md-4">
                                    Service In Time:                        
                                    <div class='input-group date' id='myDatepicker3'>
                                    <input type='text' class="form-control" name="serviceInTime" 
                                          value="{{$subscriber_details->service_in_time}}" 
                                          placeholder="Service In Time" required />
                                       <span class="input-group-addon">
                                          <span class="glyphicon glyphicon-calendar"></span>
                                       </span>
                                    </div>   
                                 </div>
                                 <div class="col-md-4">
                                    Service Out Time:                        
                                    <div class='input-group date' id='myDatepicker4'>
                                       <input type='text' class="form-control"
                                          value="{{$subscriber_details->service_out_time}}"
                                          name="serviceOutTime" placeholder="Service Out Time" required />
                                          <span class="input-group-addon">
                                             <span class="glyphicon glyphicon-calendar"></span>
                                          </span>
                                    </div>
                                 </div>
                                 <div class="col-md-3" style="padding-top: 1.6%;">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                 </div>
                           </div>
                        </form>       
                     </div>
                  @endif
                  @role('admin')
                     <div role="tabpanel" class="tab-pane fade" id="report-tab" aria-labelledby="home-tab">
                        <form method="post" action="{{url('settings/automated-service')}}">
                           <div class="well" style="height:200px;">
                              {{csrf_field()}}
                                 <div class="col-md-12">
                                    <div class="">
                                       <label>
                                          <input type="checkbox" name="onoff" class="js-switch onoff" @if(!empty($serviceReport->day_frequency)) checked @endif)/> Automated Service Report 
                                       </label>
                                       <label style="margin-left: 3%;">
                                          <input type="checkbox" name="onoffExcel" class="js-switch onoff onoffExcel" @if(!empty($serviceUnitReport->day_frequency)) checked @endif)/> Automated Unit Report 
                                       </label>
                                       <label style="margin-left: 3%;">
                                          <input type="checkbox" name="onoffclockIn" class="js-switch onoff onoffclockIn" @if(!empty($clockInOutReport->day_frequency)) checked @endif)/> Automated Clock In/Out Report 
                                       </label>
                                       <label style="margin-left: 3%;">
                                          <input type="checkbox" name="onoffViolation" class="js-switch onoff onoffViolation" @if(!empty($violationReport->day_frequency)) checked @endif)/> Automated Violation Report 
                                       </label>
                                    </div>   
                                 </div>
                                 <div class="col-md-6">
                                    <div class="radio">
                                       <label>
                                          <input type="radio" class="flat frequency day"
                                          data-date="{{\Carbon\Carbon::now()->timezone(getUserTimezone())->addDays(1)->format('F, d Y')}}" 
                                          @if((!empty($serviceReport->day_frequency) && $serviceReport->day_frequency == 1) || (!empty($serviceUnitReport->day_frequency) &&$serviceUnitReport->day_frequency == 1) || (!empty($clockInOutReport->day_frequency) && $clockInOutReport->day_frequency == 1) || (!empty($violationReport->day_frequency) && $violationReport->day_frequency == 1)) checked @endif
                                          name="frequency" value="1"> Daliy
                                       </label>
                                       <label>
                                          <input type="radio" class="flat day"
                                          data-date="{{\Carbon\Carbon::now()->startOfWeek()->timezone(getUserTimezone())->addWeek(1)->addDays(1)->format('F, d Y')}}"
                                          @if((!empty($serviceReport->day_frequency) && $serviceReport->day_frequency == 2) || (!empty($serviceUnitReport->day_frequency) &&$serviceUnitReport->day_frequency == 2) || (!empty($clockInOutReport->day_frequency) && $clockInOutReport->day_frequency == 2) || (!empty($violationReport->day_frequency) && $violationReport->day_frequency == 2)) checked @endif
                                          name="frequency" value="2"> Weekly
                                       </label>
                                       <label>
                                          <input type="radio" class="flat day"
                                          data-date="{{\Carbon\Carbon::now()->timezone(getUserTimezone())->addMonth(1)->startOfMonth()->format('F, d Y')}}" 
                                          @if((!empty($serviceReport->day_frequency) && $serviceReport->day_frequency == 3) || (!empty($serviceUnitReport->day_frequency) &&$serviceUnitReport->day_frequency == 3) || (!empty($clockInOutReport->day_frequency) && $clockInOutReport->day_frequency == 3) || (!empty($violationReport->day_frequency) && $violationReport->day_frequency == 3)) checked @endif
                                          name="frequency" value="3"> Monthly
                                       </label>
                                    </div>
                                 </div>
                                 <div class="col-md-6" id="show-date" style="padding-top: 0.7%;font-size: large;color: green;"></div>
                                 <div class="col-md-12" style="padding-top: 1.6%;">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                 </div>
                           </div>
                        </form>       
                     </div>
                  @endrole
                  @if($user->role_id == getPropertyManageId())   
                     <div role="tabpanel" class="tab-pane fade active in" id="tab_content2" aria-labelledby="profile-tab">
                        <form method="post" action="{{url('settings/dashboard-setting')}}">
                              <div class="well" style="height:150px;">
                                 {{csrf_field()}}
                                 {{-- <div class="col-md-2">
                                    <label>
                                       <input type="checkbox" name="recycling_collected" value="1" class="flat" @if(is_null($appPermission)) checked @elseif(!empty($appPermission->recycling_collected)) checked @endif />
                                             &nbsp; Recycling Collected
                                    <label>
                                 </div> --}}
                                 <div class="col-md-3">
                                    <label>
                                       <input type="checkbox" name="daliy_task_complete" value="1" class="flat" @if(is_null($appPermission)) checked @elseif(!empty($appPermission->daliy_task_complete)) checked @endif />
                                             &nbsp; Today's Task Completed
                                    <label>
                                 </div>
                                 <div class="col-md-2">   
                                    <label>
                                       <input type="checkbox" name="units_serviced" value="1" class="flat" 
                                       @if(is_null($appPermission)) checked @elseif(!empty($appPermission->units_serviced)) checked @endif /> &nbsp; #Units Serviced
                                    <label>
                                 </div>
                                 <div class="col-md-2">   
                                    <label>
                                       <input type="checkbox" name="checkin_pending" value="1" class="flat" @if(is_null($appPermission)) checked @elseif(!empty($appPermission->checkin_pending)) checked @endif /> &nbsp; Check-In Pending
                                    <label>
                                 </div>
                                 <div class="col-md-2">                        
                                    <label>
                                       <input type="checkbox" name="violation" value="1" class="flat" @if(is_null($appPermission)) checked @elseif(!empty($appPermission->violation)) checked @endif /> 
                                             &nbsp; Violation
                                    <label>
                                 </div>
                                 <div class="col-md-3" style="margin-top: -0.6%;">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                 </div>
                              </div>    
                           </form>
                     </div>
                  @endif
                  <div role="tabpanel" class="tab-pane fade @if($user->role_id == 1 && $user->is_admin == 0) active in @endif" id="tab_content3" aria-labelledby="profile-tab">
                     <form method="post" action="{{url('settings/notification-setting')}}">
                        <div class="well" style="height:264px;">
                           {{csrf_field()}}

                           @if ($user->role_id == getPropertyManageId())
                              <div class="col-md-4">
                                 <label>
                                    <input type="checkbox" name="count_violations[email]" value="1" class="flat" 
                                    @if(!isset($notification[1])) checked @elseif(!empty($notification[1]->email)) checked @endif />&nbsp;Daily Count For Violations (Email)<label>
                              </div>
                           
                              <div class="col-md-4">
                                 <label>
                                    <input type="checkbox" name="count_violations[sms]" value="1" class="flat" @if(!isset($notification[1])) checked @elseif(!empty($notification[1]->sms)) checked @endif />&nbsp;Daily Count For Violations (SMS)<label>
                                    <input type="hidden" name="count_violations[type]" value="1" class="flat" />
                              </div>
                           
                           
                           
                              <div class="col-md-4">   
                                 <label>
                                    <input type="checkbox" name="count_notes[email]" value="1" class="flat" @if(!isset($notification[2])) checked @elseif(!empty($notification[2]->email)) checked @endif/> &nbsp;Daily Count For Notes (Email)<label>
                              </div>
                           
                           
                           
                              <div class="col-md-4">   
                                 <label>
                                    <input type="checkbox" name="count_notes[sms]" value="1" class="flat" @if(!isset($notification[2])) checked @elseif(!empty($notification[2]->sms)) checked @endif/> &nbsp;Daily Count For Notes (SMS)<label>
                                    <input type="hidden" name="count_notes[type]" value="2" class="flat" />
                              </div>
                           @endif

                              <!-- Both Admin & property manager -->
                              <div class="col-md-4">   
                                 <label>
                                    <input type="checkbox" name="checkout[email]" value="1" class="flat" @if(!isset($notification[10])) checked @elseif(!empty($notification[10]->email)) checked @endif /> &nbsp;Checkout Notes (Email)
                                 <label>
                                 <input type="hidden" name="checkout[type]" value="10" class="flat" />
                              </div>
                              
                              <div class="col-md-4" >   
                                 <label>
                                    <input type="checkbox" name="first_pickup[sms]" value="1" class="flat" @if(!isset($notification[4])) checked @elseif(!empty($notification[4]->sms)) checked @endif /> &nbsp;First Pickup (SMS)<label>
                              </div>

                              <div class="col-md-4">   
                                 <label>
                                    <input type="checkbox" name="first_pickup[email]" value="1" class="flat" @if(!isset($notification[4])) checked @elseif(!empty($notification[4]->email)) checked @endif /> &nbsp;First Pickup (Email)<label>
                                       <input type="hidden" name="first_pickup[type]" value="4" class="flat" />
                              </div>
                              <!-- Both Admin & property manager -->

                              <!--Admin section  -->
                           @if ($user->hasRole('admin'))
                              <div class="col-md-4">   
                                 <label>
                                    <input type="checkbox" name="checkin[email]" value="1" class="flat" @if(!isset($notification[3])) checked @elseif(!empty($notification[3]->email)) checked @endif /> &nbsp;Property Check-In (Email)
                                 <label>
                              </div>
                              
                              <div class="col-md-4">   
                                 <label>
                                    <input type="checkbox" name="checkin[sms]" value="1" class="flat" @if(!isset($notification[3])) checked @elseif(!empty($notification[3]->sms)) checked @endif/> &nbsp;Property Check-In (SMS)
                                 <label>
                                 <input type="hidden" name="checkin[type]" value="3" class="flat" />
                              </div> 
                           <!-- #1614: Property Checkout Notification -->

                              <div class="col-md-4">   
                                 <label>
                                    <input type="checkbox" name="checkOut[email]" value="1" class="flat" @if(!isset($notification[11])) checked @elseif(!empty($notification[11]->email)) checked @endif /> &nbsp;Property Check-Out (Email)
                                 <label>
                              </div>
                              <div class="col-md-4">   
                                 <label>
                                    <input type="checkbox" name="checkOut[sms]" value="1" class="flat" @if(!isset($notification[11])) checked @elseif(!empty($notification[11]->sms)) checked @endif/> &nbsp;Property Check-Out (SMS)
                                 <label>
                                 <input type="hidden" name="checkOut[type]" value="11" class="flat" />
                              </div> 

                              <!-- #1614: Property Checkout Notification -->

                              <div class="col-md-4">   
                                 <label>
                                    <input type="checkbox" name="create_violation[email]" value="1" class="flat" @if(!isset($notification[5])) checked @elseif(!empty($notification[5]->email)) checked @endif /> &nbsp;Create Violation (Email)
                                 <label>
                                 <input type="hidden" name="create_violation[type]" value="5" class="flat" />
                              </div>
                              <div class="col-md-12">   
                                 <label>
                                    <input type="checkbox" name="clockinout_push[sms]" value="1" class="flat" @if(!isset($notification[6])) checked @elseif(!empty($notification[6]->sms)) checked @endif /> &nbsp;Clock In/Out Delay Push Notification To Reporting Manager
                                 <label>
                                 <input type="hidden" name="clockinout_push[type]" value="6" class="flat" />
                              </div>
                           @endif 
                              <div class="col-md-12" style='margin-top: 2%;'>
                                 <button type="submit" class="btn btn-primary">Submit</button>
                              </div>
                           </div>    
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@endsection 
@section('js')
<script src="{{url('assets/vendors/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js')}}"></script>
<script src="{{url('assets/trashscanjs/app.setting.js')}}"></script>
@endsection