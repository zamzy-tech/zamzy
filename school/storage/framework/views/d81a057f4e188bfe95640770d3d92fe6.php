<?php $__env->startSection('title'); ?>
    About Us
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="breadcrumb">
        <div class="container">
            <div class="contentWrapper">
                <span class="title"> About Us </span>
                <span>
                    <a href="<?php echo e(url('/')); ?>" class="home">Home</a>
                    <span><i class="fa-solid fa-caret-right"></i></span>
                    <span class="page">About Us</span>
                </span>
            </div>
        </div>
    </div>
    <div class="commonMT">
        <?php echo $__env->make('school-website.about_us_section', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>

    <?php if(isset($schoolSettings['our_mission_status']) && $schoolSettings['our_mission_status'] == 1): ?>
        <section class="whoWeAre commonMT">
            <div class="container">
                <div class="row whoWeAreContentWrapper">
                    <div class="col-lg-6 contentDiv">
                        <div class="flex_column_center">
                            <span class="commonTag"> <?php echo e($schoolSettings['our_mission_section'] ?? 'Our Mission'); ?> </span>
                            <span class="commonTitle">
                                <?php echo e($schoolSettings['our_mission_title'] ?? 'Discover Our Mission for eSchool'); ?>

                            </span>

                            <span class="commonDesc">
                                <?php echo e($schoolSettings['our_mission_description'] ?? ''); ?>

                            </span>
                            <div class="listWrapper row">
                                <?php $__currentLoopData = $schoolSettings['our_mission_points'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="list col-lg-6">
                                        <img src="<?php echo e(asset('assets/school/images/rightIcon.png')); ?>" alt="">
                                        <span><?php echo e($item); ?></span>
                                    </div>    
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>

                        </div>
                    </div>
                    <div class="col-lg-6 whoweAreImgDiv">
                        <div class="">
                            <img src="<?php echo e($schoolSettings['our_mission_image'] ?? asset('assets/school/images/ourMission.png')); ?>"
                                alt="" />
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- whoWeAre ends here  -->
    <?php endif; ?>

    <section class="commonWaveSect ourTeacherAndGallery">
        <?php echo $__env->make('school-website.our_teacher_section', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('school-website.gallery_section', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.school.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/school-website/about_us.blade.php ENDPATH**/ ?>