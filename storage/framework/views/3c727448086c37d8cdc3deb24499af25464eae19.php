<?php $__env->startSection('content'); ?>
<!--Added vh-height-->
<div class="container vh-height"> 
    <div class="row">
<!--        Remove all class and added col-sm-12-->
        <div class="col-sm-12">
            <a class="hiddenanchor" id="signup"></a>
            <a class="hiddenanchor" id="signin"></a>

            <div class="login_wrapper">
                <div class="animate form login_form">
<!--                    Added text-center-->
                    <section class="login_content text-center">
                        <form class="form-horizontal" role="form" method="POST" action="<?php echo e(route('login')); ?>">
                            <?php echo e(csrf_field()); ?>

                            <h1>Login to your account</h1>



                            <?php if($errors->has('email')): ?>
                            <div class="alert alert-danger">
                                <strong><?php echo e($errors->first('email')); ?></strong>
                            </div>
                            <?php endif; ?>


                            <?php if($errors->has('password')): ?>
                            <div class="alert alert-danger">
                                <strong><?php echo e($errors->first('password')); ?></strong>
                            </div>
                            <?php endif; ?>

                            <div>
                                <input type="email" class="form-control"  name="email" id="email" placeholder="Email" value="<?php echo e(old('email')); ?>" required autofocus />
                            </div>
                            <div class="<?php echo e($errors->has('password') ? ' has-error' : ''); ?>">
                                <input type="password" class="form-control" placeholder="Password" name="password" required/>
                            </div>
                            <div>
<!--                                Added login-btn-->
                                <button type="submit" class="btn btn-default submit login-btn">
                                    Login
                                </button>
<!--                                Added <br>-->
                                <br>
                                <a class="reset_pass" href="<?php echo e(route('password.request')); ?>">Lost your password?</a>
                            </div>

                            <div class="clearfix"></div>


                        </form>
                    </section>
                </div>
            </div>
        </div>

    </div>
</div>





<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.homeapp', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>