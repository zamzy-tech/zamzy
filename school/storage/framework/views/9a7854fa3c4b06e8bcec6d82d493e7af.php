<?php if(isset($schoolSettings['expert_teachers_status']) && $schoolSettings['expert_teachers_status'] == 1 && count($teachers)): ?>
    <section class="ourTeacher commonMT commonWaveSect">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="flex_column_center">
                        <span class="commonTag"> <?php echo e($schoolSettings['expert_teachers_section'] ?? 'Our Teachers'); ?>

                        </span>
                        <span class="commonTitle">
                            <?php echo e($schoolSettings['expert_teachers_title'] ?? 'Our Expert Teachers'); ?>

                        </span>

                        <span class="commonDesc">
                            <?php echo e($schoolSettings['expert_teachers_description'] ?? ''); ?>

                        </span>
                    </div>
                </div>

                <div class="col-12">
                    <div class="commonSlider">
                        <div class="slider-container">
                            <div class="slider-content owl-carousel">
                                <!-- Example slide -->
                                <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="swiperDataWrapper">
                                        <div class="card">
                                            <div>
                                                <img src="<?php echo e($teacher->image); ?>" alt="">
                                            </div>
                                            <div class="teacherDetails">
                                                <span class="name"><?php echo e($teacher->full_name); ?></span>
                                                <?php if($teacher->staff): ?>
                                                    <span class="subject"><?php echo e($teacher->staff->qualification); ?></span>    
                                                <?php endif; ?>
                                                
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <!-- Add more swiperDataWrapper elements here -->
                            </div>
                            <!-- Navigation buttons -->
                            <div class="navigationBtns">
                                <button class="prev commonBtn">
                                    <i class="fa-solid fa-arrow-left"></i>
                                </button>
                                <button class="next commonBtn">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- ourTeacher ends here  --><?php /**PATH /home/shacartc/school.tehub.in/resources/views/school-website/our_teacher_section.blade.php ENDPATH**/ ?>