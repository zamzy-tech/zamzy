<?php
    $lang = Session::get('language');
?>
<link rel="stylesheet" href="<?php echo e(asset('/assets/css/materialdesignicons.min.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('/assets/css/vendor.bundle.base.css')); ?>">

<link rel="stylesheet" href="<?php echo e(asset('/assets/fonts/font-awesome.min.css')); ?>"/>
<link rel="stylesheet" href="<?php echo e(asset('/assets/select2/select2.min.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('/assets/jquery-toast-plugin/jquery.toast.min.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('/assets/color-picker/color.min.css')); ?>">
<?php if($lang): ?>
    <?php if($lang->is_rtl): ?>
        <link rel="stylesheet" href="<?php echo e(asset('/assets/css/rtl.min.css')); ?>">
        <link rel="stylesheet" href="<?php echo e(asset('/assets/css/custom-rtl.css')); ?>">
    <?php else: ?>
        <link rel="stylesheet" href="<?php echo e(asset('/assets/css/style.min.css')); ?>">
        <link rel="stylesheet" href="<?php echo e(asset('/assets/css/custom.css')); ?>">
    <?php endif; ?>
<?php else: ?>
    <link rel="stylesheet" href="<?php echo e(asset('/assets/css/style.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('/assets/css/custom.css')); ?>">
<?php endif; ?>

<link rel="stylesheet" href="<?php echo e(asset('/assets/css/comman.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('/assets/css/datepicker.min.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('/assets/css/daterangepicker.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('/assets/css/ekko-lightbox.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('/assets/css/jquery.tagsinput.min.css')); ?>">


<link rel="stylesheet" href="<?php echo e(asset('/assets/bootstrap-table/bootstrap-table.min.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('/assets/bootstrap-table/fixed-columns.min.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('/assets/bootstrap-table/reorder-rows.css')); ?>">

<script src="<?php echo e(asset('/assets/js/vendor.bundle.base.js')); ?>"></script>
<script src='<?php echo e(asset('/assets/js/fullcalendar.js')); ?>'></script>


<link rel="shortcut icon" href="<?php echo e($schoolSettings['favicon'] ?? $systemSettings['favicon'] ?? url('assets/vertical-logo.svg')); ?>"/>




<script src="<?php echo e(url('/js/lang')); ?>"></script>
<style>
    :root {
        --theme-color: <?=$systemSettings['theme_color']??"#22577A" ?>;
    }
</style>
<script>
    const baseUrl = "<?php echo e(URL::to('/')); ?>";

    // Function to handle image errors
    function handleImageError(image) {
        image.classList.contains('custom-default-image')
        if (image.getAttribute('data-custom-image') != null) {
            image.src = image.getAttribute('data-custom-image');
        } else {
            image.src = "<?php echo e(asset('/assets/no_image_available.jpg')); ?>";
        }
    }

    // Create a MutationObserver to watch for DOM changes
    const observer = new MutationObserver((mutationsList) => {
        mutationsList.forEach((mutation) => {
            if (mutation.addedNodes) {
                mutation.addedNodes.forEach((node) => {
                    // Check if the added node is an image element
                    if (node instanceof HTMLImageElement) {
                        node.addEventListener('error', () => {
                            handleImageError(node);
                        });
                    }
                });
            }
        });
    });

    // Start observing changes in the DOM
    observer.observe(document, {childList: true, subtree: true});

    const onErrorImage = (e) => {
        e.target.src = "<?php echo e(asset('/assets/no_image_available.jpg')); ?>";
    };

    const onErrorImageSidebarHorizontalLogo = (e) => {
        e.target.src = "<?php echo e(asset('/assets/vertical-logo.svg')); ?>";
    };
</script>
<?php /**PATH /home/shacartc/school.tehub.in/resources/views/layouts/include.blade.php ENDPATH**/ ?>