<!DOCTYPE html>
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
    <?php echo $__env->make('layouts.gtag', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>
        <?php echo $__env->yieldContent('title'); ?> || 
        
        <?php echo e($schoolSettings['school_name'] ?? 'eSchool - Saas'); ?>

    </title>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <?php echo $__env->make('layouts.school.include', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->yieldContent('css'); ?>
</head>
<body style="background-color: #F2F5F7">
    
    <?php echo $__env->make('layouts.school.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <div class="main">
        <?php echo $__env->yieldContent('content'); ?>
    </div>

    
    <?php echo $__env->make('layouts.school.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('layouts.school.footer_js', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->yieldContent('js'); ?>
<?php echo $__env->yieldContent('script'); ?>
</body>
</html>
<?php /**PATH /home/shacartc/school.tehub.in/resources/views/layouts/school/master.blade.php ENDPATH**/ ?>