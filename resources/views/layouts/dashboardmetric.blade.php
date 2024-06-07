<!--Only for Violation Role : Start-->
        @permission(['violation'])
       
        <a href="violation/">
            <div class="col-md-3 col-sm-5 col-xs-6 tile_stats_count">
                <span class="count_top">
                    <i class="fa fa-chain-broken"></i> 
                    New Violations
                </span>
                <div class="count">
                @isset($violation)
                 {{$violation}}
                @else
                 0
                @endisset
                </div>
                <span class="count_bottom"><i class="green"></i>
                    New Violations
                </span>
            </div>
        </a>
        @endpermission

          <!--Only for Manage Violation and 
        Manage Employee Role : Start--> 
        @permission(['violation','employees'])
        <a href="{{url('/barcodes/notPickupList')}}">
            <div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count">
                <span class="count_top">
                    <i class="fa fa-truck"></i>
                    Buildings Pending
                </span>
                <div class="count">
                @isset($notPickup)
                    {{$notPickup}}
                @else
                    0
                @endisset
            </div>
                <span class="count_bottom">
                    <i class="red"></i> 
                    Today's Buildings Pending
                </span>
            </div>
        </a>
        @endpermission
        <!--Only for Manage Violation 
        and Manage Employee Role : End-->
        
        <!--Only for Manage Bin Tag,  
        Manage Customer and Manage Employee Role : End-->

        <!--Only for Manage Violation, 
        Manage Bin Tag and Manage Customer Role : Start-->
        <!-- @permission(['employees'])
        <a href="{{url('employee/misspickup')}}" target="_blank">
            <div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count">
                <span class="count_top">
                    <i class="fa fa-user"></i> Missed Pickup Employees
                </span>
                <div class="count">
                @isset($total_employee)
                 {{$total_employee}}
                @else
                 0
                @endisset</div>
                <span class="count_bottom">
                    <i class="green"></i>
                    Today Missed Pickup Employees
                </span>
            </div>
        </a>
        @endpermission -->
        <!--Only for Manage Violation,
        Manage Bin Tag and Manage Customer Role : End-->

        <!--Only for Admin Role : Start-->
        @role('admin')
        <a href="{{url('/activity/all-activity-logs')}}">
            <div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count">
                <span class="count_top">
                    <i class="fa fa-truck"></i> 
                    Total Pickups
                </span>
                <div class="count">
                @isset($pickedup_dates)
                  {{$pickedup_dates}}
                @else
                  0
                @endisset
                </div>
                <span class="count_bottom">
                    <i class="green"></i>
                    Today's Pickups 
                </span>
            </div>
        </a>
        @endrole
        <!--Only for Admin Role : End-->

        @ability('admin', 'report')
        <a href="{{url('/check-in-property-pending')}}"> 
            <div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count">
                <span class="count_top">
                    <i class="fa fa-exclamation"></i> 
                    Check-In Pending
                </span>
                <div class="count">
                  @isset($propertyCheckIn)  
                    {{$propertyCheckIn}}
                  @else
                    0
                  @endisset  
                </div>
                <span class="count_bottom">
                    <i class="green"></i>
                    Today's Pending Check-In
                </span>
            </div>
        </a>
        @endability