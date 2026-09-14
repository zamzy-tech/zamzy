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
        
        <?php echo e($systemSettings['system_name'] ?? 'eSchool - Saas'); ?>

    </title>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <?php echo $__env->make('layouts.include', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->yieldContent('css'); ?>
</head>
<body class="sidebar-fixed">
<div class="container-scroller">
    
    <?php echo $__env->make('layouts.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <div class="container-fluid page-body-wrapper">
        
        <?php echo $__env->make('layouts.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <div class="main-panel">
            <?php echo $__env->yieldContent('content'); ?>

            
            <?php echo $__env->make('description_modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            
            <?php echo $__env->make('layouts.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </div>
</div>
<?php echo $__env->make('layouts.footer_js', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->yieldContent('js'); ?>
<?php echo $__env->yieldContent('script'); ?>
</body>
</html>
<?php /**PATH /home/shacartc/school.tehub.in/resources/views/layouts/master.blade.php ENDPATH**/ ?>