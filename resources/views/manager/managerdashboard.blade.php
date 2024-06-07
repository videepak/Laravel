<style>
table, th, td {
  border: 1px solid #FFFFFF;
  border-collapse: collapse;
}
th, td {
  padding-top: 10px;
  padding-bottom: 20px;
  padding-left: 30px;
  padding-right: 40px;
}
</style>
<div class="row dashboard-valet-trash" >
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title ">
                <div class="row">
                    <div class="col-sm-7 col-xs-12 p-0-mob">
                        <h2>Dashboard</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="col-sm-5 col-xs-12 form-group pull-right top_search">
                        <div class="input-group">
                            <input type="text" class="form-control" id="tags" placeholder="Search for unit number...">
                            <div id="suggesstion-box"></div>
                            <span class="input-group-btn">
                                <button class="btn btn-success" type="button" style="color:white">
                                    <i class="fa fa-search" aria-hidden="true"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>  
            </div>
            <div class="x_content">
                <div class="col-sm-5 profile_left col-xs-12">
                    <h3>{{ucwords($user->title)}} {{ucwords($user->firstname)}} {{ucwords($user->lastname)}}</h3>
                    <ul class="list-unstyled user_data">
                        <li>
                            <i class="fa fa-envelope user-profile-icon"></i> 
                            {{$user->email}}
                        </li>
                        <li>
                            <i class="fa fa-phone user-profile-icon"></i>
                            {{$user->mobile}}
                        </li>
                    </ul>
                    <br>
                </div>
                <div class=" col-sm-7 col-xs-12">
                    <div class="row">
                        <table border="5">
                            <tr>
                              @if($appPermission == NULL || !empty($appPermission->violation))
                                <th>Violation</th>
                              @endif
                              @if($appPermission == NULL || !empty($appPermission->units_serviced))
                                <th>#Units Serviced</th>
                              @endif
                              @if($appPermission == NULL || !empty($appPermission->checkin_pending))
                                <th>Check-In Pending</th>
                              @endif
                              <th>Route Checkpoints</th>
                              @if($appPermission == NULL || !empty($appPermission->daliy_task_complete))
                                <th>Today's Task Completed</th>
                              @endif
                            </tr>
                            <tr>
                              @if($appPermission == NULL || !empty($appPermission->violation))
                                <th style="font-size: 20px;">
                                    @if(!empty($proViolation)) {{$proViolation}} @else 0 @endif</p>
                                </th>
                              @endif
                              @if($appPermission == NULL || !empty($appPermission->units_serviced))
                                <th style="font-size: 20px;">
                                    @if(!empty($unitservice)) {{$unitservice}} @else 0 @endif
                                </th>
                              @endif
                              @if($appPermission == NULL || !empty($appPermission->checkin_pending))
                                <th style="font-size: 20px;">
                                    @if(!empty($checkInCount)) {{$checkInCount}} @else 0 @endif
                                </th>
                              @endif

                              <th style="font-size: 20px;">
                                @if(!empty($rCheckpoint)) {{ $rCheckpoint }} @else 0 @endif
                              </th>
                              
                              @if($appPermission == NULL || !empty($appPermission->daliy_task_complete))
                                <th style="font-size: 20px;">
                                    @if(!empty($proViolation)) {{$proTask}} @else 0 @endif</p>
                                </th>
                              @endif
                            </tr>
                          </table>                    
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>