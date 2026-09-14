


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

    <title><?php echo e(__('Reset Password')); ?> || <?php echo e(config('app.name')); ?></title>

    <?php echo $__env->make('layouts.include', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

</head>

<body>
<div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper login-d-flex align-items-center auth">
            <div class="row flex-grow">
                <div class="col-lg-4 mx-auto">
                    <div class="auth-form-light text-left p-5">
                        <div class="brand-logo text-center">
                            
                            
                            <img src="<?php echo e($systemSettings['horizontal_logo'] ?? url('assets/horizontal-logo.svg')); ?>" alt="logo">
                        </div>
                        <?php if(session('status')): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo e(session('status')); ?>

                            </div>
                        <?php endif; ?>
                        <form method="POST" action="<?php echo e(route('password.email')); ?>">
                            <?php echo csrf_field(); ?>

                            <div class="form-group">
                                <label><?php echo e(__('school_code')); ?></label>
                                <input id="school_code" type="school_code" class="form-control form-control-lg" name="school_code" value="<?php echo e(old('school_code')); ?>" autocomplete="school_code" autofocus placeholder="<?php echo e(__('school_code')); ?>">
                            </div>

                            <div class="form-group">

                                <label><?php echo e(__('email')); ?></label>
                                <input id="email" type="email" class="form-control form-control-lg" name="email" value="<?php echo e(old('email')); ?>" required autocomplete="email" autofocus placeholder="<?php echo e(__('email')); ?>">
                            </div>
                            <div class="mt-3">
                                <input type="submit" value="<?php echo e(__('Send Password Reset Link')); ?>" class="btn btn-block btn-theme"/>
                            </div>
                        </form>
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

<script type='text/javascript'>
    $("#frmLogin").validate({
        rules: {
            username: "required",
            password: "required",
        },
        errorPlacement: function (label, element) {
            label.addClass('mt-2 text-danger');
            label.insertAfter(element);
        },
        highlight: function (element, errorClass) {
            $(element).parent().addClass('has-danger')
            $(element).addClass('form-control-danger')
        }
    });
</script>
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
<?php /**PATH /home/shacartc/school.tehub.in/resources/views/auth/passwords/email.blade.php ENDPATH**/ ?>