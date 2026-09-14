<?php if(isset($schoolSettings['gallery_status']) && $schoolSettings['gallery_status'] == 1 && count($galleries)): ?> 
    <section class="ourGalleryPhotos commonMT commonWaveSect">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="flex_column_center">
                        <span class="commonTag"> <?php echo e($schoolSettings['gallery_section'] ?? 'Our Photo Gallery'); ?> </span>
                        <span class="commonTitle">

                            <?php echo e($schoolSettings['gallery_title'] ?? 'Tiny Scholars Showcase'); ?>

                        </span>
                        <span class="commonDesc">
                            <?php echo e($schoolSettings['gallery_description'] ?? ''); ?>

                        </span>
                    </div>
                </div>
                <div class="col-12">
                    <div class="row galleryImgsContainer">
                        <div class="col-md-6 col-lg-6 leftImgs">
                            <?php $__currentLoopData = $galleries->take(1); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="bigImg">
                                    <img src="<?php echo e($row->thumbnail); ?>" alt="">
                                    <a href="<?php echo e(url('school/photos',$row->id)); ?>">
                                        <div class="detailsCard">
                                            <img src="<?php echo e(asset('assets/school/images/bx-plus-circle.png')); ?>"
                                                alt="">
                                            <span><?php echo e($row->title); ?></span>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <div class="smallImgs">
                                <?php $__currentLoopData = $galleries->skip(1)->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="leftSmallImg1">
                                        <img src="<?php echo e($row->thumbnail); ?>" alt="">
                                        <a href="<?php echo e(url('school/photos',$row->id)); ?>">
                                            <div class="detailsCard">
                                                <img src="<?php echo e(asset('assets/school/images/bx-plus-circle.png')); ?>"
                                                    alt="">
                                                <span><?php echo e($row->title); ?></span>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>


                        <div class="col-md-6 col-lg-6 rightImgs">
                            <div class="upperImgs">
                                <?php $__currentLoopData = $galleries->skip(3)->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="upperImg1">
                                        <img src="<?php echo e($row->thumbnail); ?>" alt="">
                                        <a href="<?php echo e(url('school/photos',$row->id)); ?>">
                                            <div class="detailsCard">
                                                <img src="<?php echo e(asset('assets/school/images/bx-plus-circle.png')); ?>" alt="">
                                                <span><?php echo e($row->title); ?></span>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>

                            <div class="lowerImgs">
                                <?php $__currentLoopData = $galleries->skip(5)->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="upperImg2">
                                        <img src="<?php echo e($row->thumbnail); ?>" alt="">
                                        <a href="<?php echo e(url('school/photos',$row->id)); ?>">
                                            <div class="detailsCard">
                                                <img src="<?php echo e(asset('assets/school/images/bx-plus-circle.png')); ?>" alt="">
                                                <span><?php echo e($row->title); ?></span>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ourGalleryPhotos ends here  -->
<?php endif; ?>

<?php /**PATH /home/shacartc/school.tehub.in/resources/views/school-website/gallery_section.blade.php ENDPATH**/ ?>