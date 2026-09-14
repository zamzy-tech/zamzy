<?php $__env->startSection('title'); ?>
    Photos
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <style>
       ul {
            padding-left: unset !important;
       }
        
    </style>
    <div class="breadcrumb">
        <div class="container">
            <div class="contentWrapper">
                <span class="title"> Gallery </span>
                <span>
                    <a href="<?php echo e(url('/')); ?>" class="home">Home</a>
                    <span><i class="fa-solid fa-caret-right"></i></span>
                    <span class="home">Gallery</span>
                    <span><i class="fa-solid fa-caret-right"></i></span>
                    <span class="page">Photos</span>
                </span>
            </div>
        </div>
    </div>




    <section class="photosGallery commonMT commonWaveSect">
        <div class="container">
            <div id="Center">
                <ul id="waterfall"></ul>
            </div>
        </div>
    </section>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
<script type="text/javascript">
        
    $(document).ready(function ()
    {
        $('#waterfall').NewWaterfall({
            width: 360,
            delay: 100,
        });
    });

    function random(min, max)
    {
        return min + Math.floor(Math.random() * (max - min + 1))
    }
    var loading = false;
    var dist = 600;
    var num = 1;
    var count = 0; // Current count of loaded items
    var maxCount = <?php echo e(count($galleries)); ?>;
    setInterval(function ()
    {       
        if ($(window).scrollTop() >= ($(document).height() - $(window).height() - 500) - dist && !loading && count < maxCount)
        {
            loading = true;
            <?php $__currentLoopData = $galleries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                var height = random(200, 400);
                $("#waterfall").append("<li><a href='<?php echo e(url('school/photos',$row->id)); ?>'><div class='m-2 upperBigImg1' style='height:" + height + "px'><img style='height:" + height + "px;width: 100%;' src='<?php echo e($row->thumbnail); ?>'><div class='detailArr'> <img src='<?php echo e(asset('assets/school/images/bx-plus-circle.png')); ?>' alt=''> <span><?php echo e($row->title); ?></span></div></div></a></li>");

                count++;
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            loading = false;
        }
    }, 60);
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.school.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/school-website/photo.blade.php ENDPATH**/ ?>