<!DOCTYPE html>
<html lang="en">
    <?php
    $lang = Session::get('language');
?>
<?php if($lang): ?>
    <?php if($lang->is_rtl): ?>
        <html lang="en" dir="rtl">
    <?php else: ?>
        <html lang="en" dir="ltl">
    <?php endif; ?>
<?php else: ?>
    <html lang="en" dir="ltl">
<?php endif; ?>
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link href="<?php echo e(asset('/assets/home_page/css/bootstrap.min.css')); ?>" rel="stylesheet">
        <link href="<?php echo e(asset('assets/home_page/css/style.css')); ?>" rel="stylesheet">

    <title><?php echo e(__('verify_code')); ?> || <?php echo e(config('app.name')); ?></title>

    <?php echo $__env->make('layouts.include', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <style>
        :root {
        --primary-color: <?php echo e($systemSettings['theme_primary_color'] ?? '#56cc99'); ?>;
        --secondary-color: <?php echo e($systemSettings['theme_secondary_color'] ?? '#215679'); ?>;
        --secondary-color1: <?php echo e($systemSettings['theme_secondary_color_1'] ?? '#38a3a5'); ?>;
        --primary-background-color: <?php echo e($systemSettings['theme_primary_background_color'] ?? '#f2f5f7'); ?>;
        --text--secondary-color: <?php echo e($systemSettings['theme_text_secondary_color'] ?? '#5c788c'); ?>;
        
    }
    .modal .modal-dialog {
        margin-top: unset !important;
    }
    a {
        color: #007bff !important;
    }
    </style>

</head>

<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper login-d-flex align-items-center auth">
                <div class="row flex-grow">
                    <div class="col-xl-6 mx-auto">
                        <div class="auth-form-light rounded-lg text-left p-5">
                            <div class="brand-logo text-center">
                                <?php if($schoolSettings['horizontal_logo'] ?? ''): ?>
                                    <img class="img-fluid w-25" src="<?php echo e($schoolSettings['horizontal_logo'] ?? ''); ?>" alt="logo">    
                                <?php elseif($systemSettings['login_page_logo'] ?? $systemSettings['horizontal_logo'] ?? ''): ?>
                                    <img class="img-fluid w-25" src="<?php echo e($systemSettings['login_page_logo'] ?? $systemSettings['horizontal_logo'] ?? ''); ?>" alt="logo">
                                <?php else: ?>
                                    <img class="img-fluid w-25" src="<?php echo e(url('assets/horizontal-logo.svg')); ?>" alt="logo">
                                <?php endif; ?>

                            </div>
                            <div class="mt-3">
                                
                                <?php if(\Session::has('emailSuccess')): ?>
                                    <div class="alert alert-success text-center" role="alert">
                                        <?php echo e(\Session::get('emailSuccess')); ?>.
                                    </div>
                                <?php endif; ?>
                                <?php if(\Session::has('success')): ?>
                                    <div class="alert alert-success text-center" role="alert">
                                        <?php echo e(\Session::get('success')); ?>.
                                    </div>
                                    <div class="alert alert-success text-center mt-2" role="alert">
                                        Please ensure you use your registered email for login, and your contact number as the password.
                                    </div>
                                <?php endif; ?>
                                
                                <?php if(\Session::has('emailError')): ?>
                                    <div class="alert alert-danger text-center" role="alert">
                                        <?php echo e(\Session::get('emailError')); ?>.
                                    </div>
                                <?php endif; ?>
                                <?php if(\Session::has('error')): ?>
                                    <div class="alert alert-danger text-center" role="alert">
                                        <?php echo e(\Session::get('error')); ?>.
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="text-center">
                                <h3>2 Factor Authentication</h3>
                            </div>
                            <form action="<?php echo e(route('auth.2fa.code')); ?>" id="frm2FA" method="POST" class="pt-3">
                                <?php echo csrf_field(); ?>
                                <div class="form-group">
                                    <label for="two_factor_secret"><?php echo e(__('enter_the_verification_code')); ?></label>
                                    <input id="two_factor_secret" type="text" class="form-control rounded-lg form-control-lg"
                                        name="two_factor_secret" value="" autocomplete="two_factor_secret"
                                        autofocus placeholder="<?php echo e(__('enter_the_verification_code')); ?>">
                                </div>
                                <div class="mt-3">
                                    <input type="submit" name="btn2FA" id="2FA_btn" value="<?php echo e(__('verify_code')); ?>" class="btn btn-block btn-theme btn-lg font-weight-medium auth-form-btn rounded-lg" />
                                </div>
                            </form>

                            <div class="my-3">
                                <strong>Note:</strong> Please check your email for a verification code. If you still cannot receive the code, try again login with your email and password.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- content-wrapper ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>

    <script src="<?php echo e(asset('/assets/js/vendor.bundle.base.js')); ?>"></script>
    <script src="<?php echo e(asset('/assets/js/jquery.validate.min.js')); ?>"></script>
    <script src="<?php echo e(asset('/assets/jquery-toast-plugin/jquery.toast.min.js')); ?>"></script>
    <script src="<?php echo e(asset('/assets/js/custom/common.js')); ?>"></script>
    <script src="<?php echo e(asset('/assets/js/sweetalert2.all.min.js')); ?>"></script>
    <script src="<?php echo e(asset('/assets/js/custom/function.js')); ?>"></script>
    
    <script src="<?php echo e(asset('/assets/home_page/js/bootstrap.bundle.min.js')); ?>"></script>
</body>

<?php if(Session::has('error')): ?>
    <script type='text/javascript'>
        $.toast({
            text: '<?php echo e(Session::get('error')); ?>',
            showHideTransition: 'slide',
            icon: 'error',
            loaderBg: '#f2a654',
            position: 'top-right'
        });
    </script>
<?php endif; ?>

<?php if($errors->any()): ?>
    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <script type='text/javascript'>
            $.toast({
                text: '<?php echo e($error); ?>',
                showHideTransition: 'slide',
                icon: 'error',
                loaderBg: '#f2a654',
                position: 'top-right'
            });
        </script>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

</html>

<?php $__env->startSection('js'); ?>
<script>
    $(document).ready(function() {
        $('#two_factor_secret').val('');
    });
</script>
<?php $__env->stopSection(); ?>
<?php /**PATH /home/shacartc/school.tehub.in/resources/views/auth/2fa.blade.php ENDPATH**/ ?>