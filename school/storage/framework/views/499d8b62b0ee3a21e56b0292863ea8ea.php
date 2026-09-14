<?php $__env->startSection('title'); ?>
    Videos
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
            <span class="title">
                Gallery
            </span>
            <span>
                <a href="<?php echo e(url('/')); ?>" class="home">Home</a>
                <span><i class="fa-solid fa-caret-right"></i></span>
                <span class="home">Gallery</span>
                <span><i class="fa-solid fa-caret-right"></i></span>
                <span class="page">Videos</span>
            </span>
        </div>
    </div>
</div>

<section class="videosGallery commonWaveSect commonMT">
    <div class="container">
        <div class="row videosGalleryContainer">
    
            <div id="Center">
                <ul id="waterfall">
                </ul>
            </div>
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
        if ($(window).scrollTop() >= $(document).height() - $(window).height() - dist && !loading && count < maxCount)
        {
            loading = true;
            <?php $__currentLoopData = $galleries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                var height = random(200, 400);
                $("#waterfall").append("<li><div class='video1 videos' style='height:" + height + "px'> <a href='<?php echo e(url('school/videos',$row->id)); ?>'> <div class='detailArr'> <img src='<?php echo e(asset('assets/school/images/videoPlayIcon.png')); ?>' alt=''> <span><?php echo e($row->title); ?></span> </div> <img style='height:" + height + "px;width: 100%;' src='<?php echo e($row->thumbnail); ?>' alt=''> </a> </div></li>");

                count++;
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            loading = false;
        }
    }, 60);
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.school.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/school-website/video.blade.php ENDPATH**/ ?>