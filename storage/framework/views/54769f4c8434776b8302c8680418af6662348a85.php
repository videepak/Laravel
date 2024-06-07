<?php $__env->startSection('menu'); ?>
<div class="col-md-3 left_col menu_fixed mCustomScrollbar _mCS_1 mCS-autoHide">
    <!--cus-scroll-->
    <div class="left_col scroll-view"> 
        <div class="navbar nav_title" style="border: 0;">&nbsp;</div>
        <div class="navbar nav_title" style="border: 0;">
            <a href="<?php echo e(url('home')); ?>">
                <div class="">
                    <?php if(isset($subscriber_details) && ($subscriber_details->company_logo == null || Storage::disk('s3')->exists('uploads/user/' . $subscriber_details->company_logo))): ?>
                        <img 
                            src="<?php echo e(url('/uploads/user/' . $subscriber_details->company_logo)); ?>" 
                            class="img-thumbnail img-responsive center-block logo-redirect" 
                            width="100" 
                            style="cursor: pointer;"
                        />
                    <?php else: ?>
                        <img 
                            src="<?php echo e(url('/uploads/user/no-image-available.png')); ?>" 
                            class="img-thumbnail img-responsive center-block logo-redirect" 
                            width="100" 
                            style="cursor: pointer;"
                        />
                    <?php endif; ?>
                </div>
            </a>
        </div>
        <div class="clearfix"></div>
        <hr class="logo-line">
        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
            <div class="menu_section">
			
			<?php if(isset($subscriber_details)): ?>
                <?php
				$currdate = date('Y-m-d');
				$currday = date('d');
                $getenddays = date('d',strtotime($subscriber_details->sub_end_date));
                $days_ago = date('d', strtotime('-5 days', strtotime($subscriber_details->sub_end_date)));
                $countday =  date('d',strtotime($subscriber_details->sub_end_date)) - $currday ;
				
                ?>
				
				<?php if(Auth::user()->subscriber_menu=='yes'): ?>
				<ul class="nav side-menu">
                    <li>
                        <a href="<?php echo e(url('subscriber-profile')); ?>"> <i class="fa fa-file-o"></i>
                            Subscription  </a>
                        
                    </li>                   
                </ul>
				<?php endif; ?>
			<?php endif; ?>
			

		<?php
        //trail user
        $auth = Auth::user();
        $sub = true;
        if ($auth->trial == 'yes') {
            if (date('Y-m-d') > $auth->trial_end) { //free expired
                $current = DB::table('subscribers')->where('user_id', $auth->id)->first();
                if ($current->payment == '1') {
                    if (date('Y-m-d') > $current->sub_end_date) {
                        $sub = false;
                    }
                } else {
                    $sub = false;
                }
            }
        }

        if ($sub == true) { ?>
                <?php if (\Entrust::can('violation')) : ?>
                <ul class="nav side-menu">
                    <li>
                        <a>
                            <i class="fa fa-chain-broken"></i> 
                                 Violations
                                <span class="fa fa-chevron-down"></span>
                        </a>
                        <ul class="nav child_menu">
                            <li>
                                <a href="<?php echo e(url('violation')); ?>">
                                    Manage Violations
                                </a>
                            </li>
                            <?php if (\Entrust::hasRole('admin')) : ?>
                            <li>
                                <a href="<?php echo e(url('reason')); ?>">
                                    Manage Violation Rules
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(url('manage-violation-action')); ?>">
                                    Manage Violation Action
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(url('violation-templates')); ?>">
                                    Manage Templates
                                </a>
                            </li>
                            <?php endif; // Entrust::hasRole ?>
                            <?php if (\Entrust::hasRole('property_manager')) : ?>
                            <li>
                                <a href="<?php echo e(url('property-manager/resident-templates')); ?>">
                                    Resident Templates
                                </a>
                            </li>
                            <?php endif; // Entrust::hasRole ?>
                            <li>
                                <a href="<?php echo e(url('top-violation')); ?>">
                                    Top Violators
                                </a>
                            </li>
                            <!-- <li>
                                <a href="<?php echo e(url('routecheck-point/violation')); ?>">
                                    Route Checkpoint Violation
                                </a>
                            </li> -->
                        </ul>
                    </li>                   
                </ul>
                <?php endif; // Entrust::can ?>

                 <!--Menu for property manager: End -->
                 <?php if (\Entrust::hasRole('admin')) : ?>          
                 <ul class="nav side-menu">
                     <li>
                         <a>
                             <i class="fa fa-tasks"></i>
                                Tasks 
                             <span class="fa fa-chevron-down"></span>
                         </a>
                         <ul class="nav child_menu">
                             <li>
                                 <a href="<?php echo e(url('/tasks/')); ?>">
                                     Manage Tasks
                                 </a>
                             </li>
                         </ul>
                     </li>                   
                 </ul>
                 <?php endif; // Entrust::hasRole ?>

                <?php if (\Entrust::can('report')) : ?>
                    <ul class="nav side-menu">
                        <li>
                            <a>
                                <i class="fa fa-file-o"></i> 
                                Service Notes
                                <span class="fa fa-chevron-down"></span>
                            </a>
                        <ul class="nav child_menu">
                            <li>
                                <a href="<?php echo e(url('note')); ?>">
                                    Manage Service Notes
                                </a>
                            </li>
                            <?php if (\Entrust::hasRole('admin')) : ?>
                            <li>
                                <a href="<?php echo e(url('note-reason')); ?>">
                                    Manage Note Types
                                </a>
                            </li>
                            <?php endif; // Entrust::hasRole ?>
                         </ul>
                     </li>                   
                 </ul>
                <?php endif; // Entrust::can ?> 

                <?php if (\Entrust::can('report')) : ?>
                <ul class="nav side-menu">
                    <li>
                        <a>
                            <i class="fa fa-bar-chart"></i> 
                            Management Reports
                            <span class="fa fa-chevron-down"></span>
                        </a>
                        <ul class="nav child_menu">
                            <li>
                                <a href="<?php echo e(url('report/manage-routecheckpoints')); ?>">
                                    Manage Route Checkpoints
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(url('delivery-report')); ?>">
                                    Service Report
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(url('check-in-property-pending')); ?>">
                                    Property Check In/Out  Log
                                </a>
                            </li>
                            <?php if (\Entrust::hasRole('admin')) : ?>    
                            <li>
                                <a href="<?php echo e(url('/clockinout/report/')); ?>">
                                    Employee Clock In/Out Log
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(url('report/manage-task')); ?>">
                                    Task Status Report
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(url('report/historical-report')); ?>">
                                    Check In/Out Historical Report
                                </a>
                            </li>
                            <?php endif; // Entrust::hasRole ?>



                            <li>
                                <a href="<?php echo e(url('recycle-report')); ?>">
                                    Recycle Reports
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(url('efficiency')); ?>">
                                    Service Quality Score
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(url('reported-issue')); ?>"> 
                                    Manage Exceptions
                                </a>
                            </li>                     
                        </ul>
                    </li>                   
                </ul>
                <?php endif; // Entrust::can ?>
            
                <?php if($user->can('employees') || $user->hasRole('admin')): ?>
                <ul class="nav side-menu">
                    <li>
                        <a>
                            <i class="fa fa-user"></i>
                                Employees
                                <span class="fa fa-chevron-down"></span>
                            </a>
                        <ul class="nav child_menu">
                            <li>
                                <a href="<?php echo e(url('employee')); ?>">
                                    Manage Employee
                                </a>
                            </li>
                            <?php if (\Entrust::hasRole('admin')) : ?> 
                            <li>
                                <a href="<?php echo e(url('role')); ?>">
                                    Manage Roles
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(url('/clockinout/report/')); ?>">
                                    Employee Clock In/Out Log
                                </a>
                            </li>
                            <?php endif; // Entrust::hasRole ?> 
                        </ul>
                    </li>                   
                </ul>
                <?php endif; ?>

                <?php if (\Entrust::can('activitylogs')) : ?>
                <ul class="nav side-menu">
                    <li>
                        <a>
                            <i class="fa fa-history"></i>
                            View Activity logs 
                            <span class="fa fa-chevron-down"></span>
                        </a>
                        <ul class="nav child_menu">
                            <li>
                                <a href="<?php echo e(url('activitylogs')); ?>">
                                    View Activity logs
                                </a>
                            </li>
                        </ul>
                    </li>                   
                </ul>
                <?php endif; // Entrust::can ?>
                

                <!--Menu for property manager: Start -->
                <?php if (\Entrust::hasRole('property_manager')) : ?>  
                <!-- <ul class="nav side-menu">
                    <li>
                        <a><i class="fa fa-chain-broken"></i>
                            Manage Violations <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                            <li><a href="<?php echo e(url('property-manager/violation')); ?>">Manage Violations</a></li>
                            <li><a href="<?php echo e(url('property-manager/top-violation')); ?>">Top Violation</a></li>
                        </ul>
                    </li>                   
                </ul> -->
                <ul class="nav side-menu">
                    <li>
                        <a>
                            <i class="fa fa-file-o"></i>
                                Manage Notes 
                                <span class="fa fa-chevron-down"></span>
                            </a>
                        <ul class="nav child_menu">
                            <li>
                                <a href="<?php echo e(url('note')); ?>">
                                    Manage Notes
                                </a>
                            </li>
                        </ul>
                    </li>                   
                </ul>
                <ul class="nav side-menu">
                    <li>
                        <a>
                            <i class="fa fa-file-o"></i>
                                Manage Tasks
                                <span class="fa fa-chevron-down"></span>
                            </a>
                        <ul class="nav child_menu">
                            <li>
                                <a href="<?php echo e(url('property-manager/tasks')); ?>">
                                    Manage Tasks
                                </a>
                            </li>
                        </ul>
                    </li>                   
                </ul>
                <?php endif; // Entrust::hasRole ?>

                <?php if (\Entrust::can('customers')) : ?>
                <ul class="nav side-menu">
                    <li>
                        <a>
                            <i class="fa fa-users"></i>
                                Customers
                                <span class="fa fa-chevron-down"></span>
                        </a>
                        <ul class="nav child_menu">
                            <li>
                                <a href="<?php echo e(url('customer')); ?>">
                                    Manage Customers
                                </a>
                            </li>
                            <?php if (\Entrust::hasRole('admin')) : ?>
                            <li>
                                <a href="<?php echo e(url('property-manager')); ?>">
                                    Manage Property Managers
                                </a>
                            </li>
                            <?php endif; // Entrust::hasRole ?>
                        </ul>
                    </li>    
                </ul>
                <?php endif; // Entrust::can ?>

                <?php if(Auth::user()->can('properties', 'barcodes')): ?>
                <ul class="nav side-menu">
                    <li>
                        <a>
                            <i class="fa fa-university"></i>
                                Properties
                            <span class="fa fa-chevron-down"></span>
                        </a>
                        <ul class="nav child_menu">
                            <?php if (\Entrust::can('properties')) : ?>
                            <li>
                                <a href="<?php echo e(url('property')); ?>">
                                    Manage Properties
                                </a>
                            </li>
                            <?php endif; // Entrust::can ?>
                            <?php if (\Entrust::can('barcodes')) : ?>
                            <li>
                                <a href="<?php echo e(url('barcode')); ?>">
                                    Manage Bin Tags
                                </a>
                            </li>
                            <?php endif; // Entrust::can ?>
                        </ul>
                    </li>                   
                </ul>
                <?php endif; ?>
                <?php if (\Entrust::hasRole('property_manager')) : ?>
                <ul class="nav side-menu">
                    <li>
                        <a>
                            <i class="fa fa-users"></i>
                                Manage Residents
                                <span class="fa fa-chevron-down"></span>
                        </a>
                        <ul class="nav child_menu">
                            <li>
                                <a href="<?php echo e(url('property-manager/resident')); ?>">
                                    Manage Resident
                                </a>
                            </li>
                        </ul>
                    </li>    
                </ul>
                <?php endif; // Entrust::hasRole ?>
		<?php } ?>
            </div>
        </div>
        <div class="sidebar-footer hidden-small">
            <a data-toggle="tooltip" data-placement="top" 
               title="" href="<?php echo e(url('home')); ?>" 
               data-original-title="Dashboard">
                <span class="fa fa-dashboard" aria-hidden="true"></span>
            </a>
            <a data-toggle="tooltip" data-placement="top" 
               href="<?php echo e(url('changepassword')); ?>" 
               data-original-title="Change Password">
                <span class="fa fa-key" aria-hidden="true"></span>
            </a>
            <a data-toggle="tooltip" data-placement="top" 
               href="<?php echo e(url('profile')); ?>" data-original-title="Profile">
                <span class="fa fa-user" aria-hidden="true"></span>
            </a>
            <a href="<?php echo e(route('logout')); ?>" 
               onclick="event.preventDefault();
                   document.getElementById('logout-form').submit();" 
               data-toggle="tooltip" data-placement="top" 
               data-original-title="Logout">
                <span class="fa fa-sign-out" aria-hidden="true"></span>
            </a>
        </div>
    </div>
</div>
<div class="top_nav">
    <div class="nav_menu">
        <nav>
            <div class="nav toggle">
                <a id="menu_toggle"><i class="fa fa-bars"></i></a>
            </div>
            <ul class="nav navbar-nav navbar-right" style="width: 25%;">
                <li class="">
                    <a href="javascript:;" class="user-profile dropdown-toggle" 
                       data-toggle="dropdown" aria-expanded="false">

                        <?php if(isset($u_image->image_name) && !is_null($u_image->image_name == null)): ?>
                            <?php if(Storage::disk('s3')->exists('uploads/user/' . $user->image_name)): ?>
                                <img 
                                    src="<?php echo e(url('uploads/user/' . $user->image_name)); ?>" 
                                    alt="<?php echo e(ucwords(Auth::user()->firstname)); ?> <?php echo e(ucwords(Auth::user()->lastname)); ?>"
                                >
                            <?php else: ?>
                                <img 
                                    src="<?php echo e(url("/uploads/user/no-image-available.png")); ?>" 
                                    alt="<?php echo e(ucwords(Auth::user()->firstname)); ?> <?php echo e(ucwords(Auth::user()->lastname)); ?>"
                                >
                            <?php endif; ?>
                        <?php else: ?>
                            <i class="fa fa-user"></i>
                        <?php endif; ?>

                        <?php echo e(ucwords(Auth::user()->firstname)); ?> <?php echo e(ucwords(Auth::user()->lastname)); ?>

                        <span class=" fa fa-angle-down"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-usermenu pull-right">
                        <li>
                            <a href="<?php echo e(url('changepassword')); ?>" >
                                <i class="fa fa-key pull-right"></i> 
                                Change Password</a></li>
                        <li>
                            <a href="<?php echo e(url('profile')); ?>" >
                                <i class="fa fa-user pull-right"></i> Profile</a>
                        </li>
                        <li>
                            <a href="<?php echo e(url('settings/app-setting')); ?>">
                                <i class="fa fa-cog pull-right"></i> Setting</a>
                        </li>      
                        <li>
                            <a href="<?php echo e(route('logout')); ?>" onclick="event.preventDefault();
                                   document.getElementById('logout-form').submit();">
                                <i class="fa fa-sign-out pull-right"></i>
                                Log Out
                            </a>
                        </li>
                    </ul>
                </li>

                <?php if(isset($subscriber_details)): ?>
                <?php $currdate = date('Y-m-d');   $currday = date('d');
                $getenddays = date('d',strtotime($subscriber_details->sub_end_date));
                $days_ago = date('d', strtotime('-5 days', strtotime($subscriber_details->sub_end_date)));
                $countday =  date('d',strtotime($subscriber_details->sub_end_date)) - $currday ;
                ?>

                <li role="presentation" class="" style="display: none;">
                    <a href="javascript:;"
                       class="dropdown-toggle info-number" 
                       data-toggle="dropdown"
                       aria-expanded="false">
                        <i class="fa fa-envelope-o"></i>
                        <?php if($user->is_admin == 1): ?>    
                        <?php if($subscriber_details->payment == 1): ?>
                        <?php if($currdate <= $subscriber_details->sub_end_date): ?>
                        <?php if($currday >= $days_ago && date('m')
                        == date('m',
                        strtotime($subscriber_details->sub_end_date))): ?> 
                        <span class="badge bg-green">1</span>
                        <?php endif; ?>
                        <?php endif; ?>
                        <?php else: ?>
                        <span class="badge bg-green">1</span>
                        <?php endif; ?>
                        <?php endif; ?>
                    </a>
                    <?php if($user->is_admin == 1): ?>    
                    <?php if($subscriber_details->payment == 1): ?>
                    <ul id="menu1" class="dropdown-menu list-unstyled msg_list" role="menu">
                        <?php if($currdate <=$subscriber_details->sub_end_date): ?>
                        <?php if($currday >= $days_ago && date('m') == date('m',strtotime($subscriber_details->sub_end_date))): ?> 
                        <li>
                            <a>
                                <span>
                                    <span><?php echo e(ucwords(Auth::user()->firstname)); ?> <?php echo e(ucwords(Auth::user()->lastname)); ?></span>
                                    <span class="time"></span>
                                </span>
                                <span class="message">
                                    Your Subscription expire in <?php if($countday==0): ?>Today <?php else: ?>  <?php echo e($countday); ?> days <?php endif; ?>
                                </span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php else: ?> 
                        <?php endif; ?> 
                    </ul>
                    <?php else: ?>
                    <ul id="menu1" class="dropdown-menu list-unstyled msg_list" role="menu">
                        <li class="hidden-xs hidden-sm hidden-md">
                            <a>
                                <?php if($user->is_admin == 1): ?>    
                                <?php if($subscriber_details->payment == 1): ?>
                                <?php else: ?>
                                <span>
                                    <span><?php echo e(ucwords(Auth::user()->firstname)); ?> <?php echo e(ucwords(Auth::user()->lastname)); ?></span>
                                    <span class="time"></span>
                                </span>
                                <span class="message">
                                    Please Upgrade Your Account..
                                    <form action="<?php echo e(url('pay/subscription')); ?>" method="POST">

                                        <input name="uid" type="hidden" value='<?php echo $user->id; ?>'>
                                        <input name="email" type="hidden" value='<?php echo $user->email; ?>'>
                                        <input name="amt" type="hidden" value='<?php echo $subscribtion_details->price; ?>'>
                                        <input name="pack_name" type="hidden" value='<?php echo $subscribtion_details->package_offering; ?>'>
                                        <input name="subscription_id" type="hidden" value='<?php echo $subscriber_details->subscription_id; ?>'>

                                        <?php echo e(csrf_field()); ?>

                                        <script
                                            src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                                            data-key="pk_test_wlR8UBPOmQo5qfO4NzNUIPVo"
                                            data-amount= <?php echo e($subscribtion_details->price); ?>

                                   data - name = "Stripe Demo"
                                           data - description = "Online course about integrating Stripe"
                                           data - image = "https://stripe.com/img/documentation/checkout/marketplace.png"
                                           data - locale = "auto"
                                           data - currency = "usd" >
                                        </script>
                                    </form>
                                </span>
                                <?php endif; ?>
                                <?php endif; ?>
                            </a>
                        </li>
                    </ul>
                    <?php endif; ?>
                    <?php endif; ?>
                </li>
                <?php endif; ?>
                <li class="hidden-xs hidden-sm hidden-md" style="display: none;">
                    <?php if($user->is_admin == 1): ?>    
                        <?php if($subscriber_details->payment == 1): ?>
                        <a href="javascript:void(0);">
                            <span class="label label-success">Payment Done</span>
                        </a>
                        <?php else: ?>
                        <form action="<?php echo e(url('pay/subscription')); ?>" method="POST" style="margin-top: 13px;">
                            <input name="uid" type="hidden" value='<?php echo $user->id; ?>'>
                            <input name="email" type="hidden" value='<?php echo $user->email; ?>'>
                            <input name="amt" type="hidden" value='<?php echo $subscribtion_details->price; ?>'>
                            <input name="pack_name" type="hidden" value='<?php echo $subscribtion_details->package_offering; ?>'>
                            <input name="subscription_id" type="hidden" value='<?php echo $subscriber_details->subscription_id; ?>'>
                            <?php echo e(csrf_field()); ?>

                            <script
                                src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                                data-key="pk_test_wlR8UBPOmQo5qfO4NzNUIPVo"
                                data-amount= <?php echo e($subscribtion_details->price); ?>

                                    data - name = "Stripe Demo"
                                    data - description = "Online course about integrating Stripe"
                                    data - image = "https://stripe.com/img/documentation/checkout/marketplace.png"
                                    data - locale = "auto"
                                    data - currency = "usd" >
                            </script>
                        </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
		
        <div class="col-lg-7 col-md-7 hidden-sm hidden-xs pull-right" 
             style="margin-top: 1.5%;font-size: larger;
             font-weight: 800;text-align: center;">
			
            <a href="<?php echo e(url('/home')); ?>" >
                <?php if(isset($subscriber_details) 
                && !empty($subscriber_details->company_name)): ?>
                <?php echo e(ucwords($subscriber_details->company_name)); ?>

                <?php else: ?> 
                Trash Scan 
                <?php endif; ?>
            </a>
        </div>
    </div>
	<?php if(isset($subscriber_details)): ?>
	<?php
    $currday = date('Y-m-d');
    $expire = false;

    if (date('Y-m-d') > Auth::user()->trial_end) {
        $expire = true;
    } else {
        $counter = strtotime(Auth::user()->trial_end) - time();
        $countday2 = round($counter / (60 * 60 * 24));
    }

    if ($expire == false && date('Y-m-d') >= \Carbon\Carbon::parse(\Auth::user()->trial_end)->subDays(30)->format('Y-m-d')) { ?>
	
    <div class="nav_menu">
        <div class="col-lg-12 col-md-12 hidden-sm hidden-xs" style="margin-top: 1.5%;background-color:yellow">
            <marquee width = "50%"><b>
                <?php echo $countday2; ?> Days left for completion free Trial.</b>
            </marquee>
	    </div>
    </div>
	<?php }

    if (Auth::user()->is_admin != 0) {
        if ($expire == true) {
            $p = DB::table('subscribers')->where('id', Auth::user()->subscriber_id)->first();

            if (!is_null($p->sub_end_date) && date('Y-m-d') > $p->sub_end_date) {
                $packname = DB::table('subscriptions')->where('id', $p->subscription_id)->first(); ?>
        <div class="nav_menu">
           	<div class="col-lg-12 col-md-12 hidden-sm hidden-xs" style="margin-top: 1.5%; background-color:yellow;text-align:center">
		    <b><?php echo $packname->package_offering; ?> has expired. Click on Manage Plan to upgrade subscription. </b> 
		    </div>
		</div>
		<?php
            }
        }
    } ?>
	<?php endif; ?>
	
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
##parent-placeholder-93f8bb0eb2c659b85694486c41717eaf0fe23cd4##
<script src='https://js.stripe.com/v2/' type='text/javascript'></script>
<script src="<?php echo e(url('assets/js/payment.js')); ?>"></script>
<?php $__env->stopSection(); ?>
