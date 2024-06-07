<!--Only for Violation Role : Start-->
        <?php if (\Entrust::can(['violation'])) : ?>
       
        <a href="violation/">
            <div class="col-md-3 col-sm-5 col-xs-6 tile_stats_count">
                <span class="count_top">
                    <i class="fa fa-chain-broken"></i> 
                    New Violations
                </span>
                <div class="count">
                <?php if(isset($violation)): ?>
                 <?php echo e($violation); ?>

                <?php else: ?>
                 0
                <?php endif; ?>
                </div>
                <span class="count_bottom"><i class="green"></i>
                    New Violations
                </span>
            </div>
        </a>
        <?php endif; // Entrust::can ?>

          <!--Only for Manage Violation and 
        Manage Employee Role : Start--> 
        <?php if (\Entrust::can(['violation','employees'])) : ?>
        <a href="<?php echo e(url('/barcodes/notPickupList')); ?>">
            <div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count">
                <span class="count_top">
                    <i class="fa fa-truck"></i>
                    Buildings Pending
                </span>
                <div class="count">
                <?php if(isset($notPickup)): ?>
                    <?php echo e($notPickup); ?>

                <?php else: ?>
                    0
                <?php endif; ?>
            </div>
                <span class="count_bottom">
                    <i class="red"></i> 
                    Today's Buildings Pending
                </span>
            </div>
        </a>
        <?php endif; // Entrust::can ?>
        <!--Only for Manage Violation 
        and Manage Employee Role : End-->
        
        <!--Only for Manage Bin Tag,  
        Manage Customer and Manage Employee Role : End-->

        <!--Only for Manage Violation, 
        Manage Bin Tag and Manage Customer Role : Start-->
        <!-- <?php if (\Entrust::can(['employees'])) : ?>
        <a href="<?php echo e(url('employee/misspickup')); ?>" target="_blank">
            <div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count">
                <span class="count_top">
                    <i class="fa fa-user"></i> Missed Pickup Employees
                </span>
                <div class="count">
                <?php if(isset($total_employee)): ?>
                 <?php echo e($total_employee); ?>

                <?php else: ?>
                 0
                <?php endif; ?></div>
                <span class="count_bottom">
                    <i class="green"></i>
                    Today Missed Pickup Employees
                </span>
            </div>
        </a>
        <?php endif; // Entrust::can ?> -->
        <!--Only for Manage Violation,
        Manage Bin Tag and Manage Customer Role : End-->

        <!--Only for Admin Role : Start-->
        <?php if (\Entrust::hasRole('admin')) : ?>
        <a href="<?php echo e(url('/activity/all-activity-logs')); ?>">
            <div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count">
                <span class="count_top">
                    <i class="fa fa-truck"></i> 
                    Total Pickups
                </span>
                <div class="count">
                <?php if(isset($pickedup_dates)): ?>
                  <?php echo e($pickedup_dates); ?>

                <?php else: ?>
                  0
                <?php endif; ?>
                </div>
                <span class="count_bottom">
                    <i class="green"></i>
                    Today's Pickups 
                </span>
            </div>
        </a>
        <?php endif; // Entrust::hasRole ?>
        <!--Only for Admin Role : End-->

        <?php if (\Entrust::ability('admin', 'report')) : ?>
        <a href="<?php echo e(url('/check-in-property-pending')); ?>"> 
            <div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count">
                <span class="count_top">
                    <i class="fa fa-exclamation"></i> 
                    Check-In Pending
                </span>
                <div class="count">
                  <?php if(isset($propertyCheckIn)): ?>  
                    <?php echo e($propertyCheckIn); ?>

                  <?php else: ?>
                    0
                  <?php endif; ?>  
                </div>
                <span class="count_bottom">
                    <i class="green"></i>
                    Today's Pending Check-In
                </span>
            </div>
        </a>
        <?php endif; // Entrust::ability ?>