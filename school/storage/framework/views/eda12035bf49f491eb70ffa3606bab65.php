<!DOCTYPE html>
<?php
    $lang = Session::get('language');
?>
<?php if($lang): ?>
    <?php if($lang->is_rtl): ?>
        <html lang="en" dir="rtl">
            <link href="<?php echo e(asset('assets/home_page/css/style-rtl.css')); ?>" rel="stylesheet">
    <?php else: ?>
        <html lang="en">
    <?php endif; ?>
<?php else: ?>
    <html lang="en">
<?php endif; ?>

<head>
    <?php echo $__env->make('layouts.gtag', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta property="og:title" content="<?php echo e($systemSettings['system_name'] ?? 'eSchool - Saas'); ?>">
    <meta property="og:image" content="<?php echo e($systemSettings['horizontal_logo'] ?? asset('assets/home_page/images/logo.png')); ?>">
    <meta property="og:url" content="<?php echo e(url('/')); ?>">
    <meta property="og:description" content="<?php echo e($systemSettings['tag_line'] ?? 'eSchool - Saas'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $__env->yieldContent('title'); ?>
        
        <?php echo e($systemSettings['system_name'] ?? 'eSchool - Saas'); ?>

    </title>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <?php echo $__env->make('layouts.home_page.include', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->yieldContent('css'); ?>
</head>

<body class="sidebar-fixed">
    <div class="container-scroller">

        <?php echo $__env->yieldContent('content'); ?>

        <?php echo $__env->make('layouts.home_page.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
    <?php echo $__env->make('layouts.home_page.footer_js', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->yieldContent('js'); ?>
    <?php echo $__env->yieldContent('script'); ?>
</body>

</html>
<?php /**PATH /home/shacartc/school.tehub.in/resources/views/layouts/home_page/master.blade.php ENDPATH**/ ?>