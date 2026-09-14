<?php $__env->startSection('title'); ?>
    Home
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    
    <section class="heroSection">
        <div class="owl-carousel owl-theme hero-carousel">
            <?php if(is_object($sliders)): ?>
                <?php $__currentLoopData = $sliders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="item">
                        <img src="<?php echo e($slider->image); ?>" alt="" class="swiperImage" />
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>    
            <?php else: ?>
                <?php $__currentLoopData = $sliders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="item">
                        <img src="<?php echo e($slider); ?>" alt="" class="swiperImage" />
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>  
            <?php endif; ?>
        </div>
    </section>
    <!-- heroSection ends here  -->

    
    <?php echo $__env->make('school-website.about_us_section', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php if(isset($schoolSettings['education_program_status']) && $schoolSettings['education_program_status'] == 1 && count($class_groups)): ?>
        <section class="programs commonMT">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="flex_column_center">
                            <span class="commonTag"> <?php echo e($schoolSettings['education_program_title'] ?? 'Educational Programs'); ?>  </span>
                            <span class="commonTitle">
                                <?php echo e($schoolSettings['education_program_heading'] ?? 'Educational Programs for every Stage'); ?>

                                
                            </span>

                            <span class="commonDesc">
                                <?php echo e($schoolSettings['education_program_description'] ?? ''); ?>

                            </span>
                        </div>
                    </div>

                    <div class="col-12 programsCardWrapper">
                        <div class="commonSlider">

                            <div class="slider-content owl-carousel">

                                <?php $__currentLoopData = $class_groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="swiperDataWrapper">
                                        <div class="card">
                                            <div class="imgDiv">
                                                <img src="<?php echo e($group->image ?? asset('assets/school/images/programImg1.png')); ?>" class="card-img-top" alt="..." />
                                            </div>
                                            <div class="cardDetails">
                                                <span class="cardTitle"><?php echo e($group->name); ?></span>
                                                <span class="cardDesc"><?php echo e($group->description); ?></span>
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
                        <div class="sideImgs">
                            <img src="<?php echo e(asset('assets/school/images/color.png')); ?>" class="colorImg" alt="colorImg" />
                            <img src="<?php echo e(asset('assets/school/images/bag.png')); ?>" class="bagImg" alt="bagImg" />
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- programs ends here  -->
    <?php endif; ?>
    
    
    <?php if(isset($schoolSettings['online_registration_status']) && $schoolSettings['online_registration_status'] == 1): ?>
        <section class="admissionOpenSect commonMT">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="flex_column_center">
                            <span class="adminssionTag"> <?php echo e($schoolSettings['online_registration_title'] ?? 'Where Learning Takes Flight'); ?> </span>
                            <span class="commonTitle"><?php echo e($schoolSettings['online_registration_heading'] ?? 'Cultivate Your Curiosity & Explore Endless Possibilities. Admissions Open Now!'); ?>

                            </span>
            
                            <span class="commonDesc"><?php echo e($schoolSettings['online_registration_description'] ?? 'Our admissions are now open for the 2024-25 school year. We invite curious and enthusiastic students to
                            join our vibrant learning community.'); ?>

                        
                            </span>
                            <button class="commonBtn"><a href="<?php echo e(route('online-admission.index')); ?>"><?php echo e(__('apply_now')); ?></a></button>
                        </div>
                    </div>
                    <div class="col-lg-6 imgDiv">
                    <img src="<?php echo e($schoolSettings['online_registration_image'] ?? asset('assets/school/images/admissionSectImg.png')); ?>" alt="">
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>    
    

    
    <?php if(isset($schoolSettings['announcement_status']) && $schoolSettings['announcement_status'] == 1): ?>
        <section class="events annaouncementSection commonMT commonWaveSect">
            <div class="container">
                <div class="row mainRow">

                    <div class="col-md-12 col-lg-6 imgDiv">
                        <img src="<?php echo e($schoolSettings['announcement_image'] ?? asset('assets/school/images/announcementImg.png')); ?>"
                            alt="announcementImg">
                    </div>

                    <div class="col-12 col-md-12 col-lg-6 ">
                        <div class="upperDiv">

                            <div class="flex_column_center">
                                <span class="commonTag"> <?php echo e($schoolSettings['announcement_section'] ?? 'Announcements'); ?> </span>
                                <span class="commonTitle">
                                    <?php echo e($schoolSettings['announcement_title'] ?? 'Important Updates'); ?>

                                </span>

                                <span class="commonDesc">
                                    <?php echo e($schoolSettings['announcement_description'] ?? ''); ?>

                                </span>
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

                        <div class="eventsCardWrapper">
                            <div class="row">
                                <div class="col-12">

                                    <div class="commonSlider annaouncementSlider">

                                        <div class="slider-content owl-carousel announcementSwiper">
                                            <!-- Example slide -->
                                            <?php $__currentLoopData = $announcements->chunk(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div>
                                                    <?php $__currentLoopData = $announcement; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div class="announcementCardWrapper">
                                                            <div class="card open-modal">
                                                                <span class="rightArr"><i class="fa-solid fa-chevron-right"></i></span>
                                                                <div class="eventDateWrapper">
                                                                    <span class="date"><?php echo e(date('d',strtotime($item->created_at))); ?></span>
                                                                    <span class="month"><?php echo e(date('F',strtotime($item->created_at))); ?></span>
                                                                    <span class="d-none eventDate"><?php echo e(date('d F, Y',strtotime($item->created_at))); ?></span>
                                                                </div>
                                                                <div class="eventDescWrapper">
                                                                    <span class="eventTitle"><?php echo e($item->title); ?></span>
                                                                    <span class="eventDesc">
                                                                        <?php echo e($item->description); ?>

                                                                    </span>
                                                                    <div class="classWrapper">
                                                                        <span class="eventDesc eventClasses"><?php echo e(implode(', ', $item->announcement_class->pluck('class_section.full_name')->toArray())); ?></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <!-- Add more swiperDataWrapper elements here -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Structure -->
                            <div id="announcementModal" class="modal">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <span class="title" id="title"></span>
                                        <span class="closeBtn close">&times;</span>
                                    </div>
                                    <div class="modal-body">
                                        <div class="modalUpperDiv">
                                            <div class="dateStandardWrapper">
                                                <span class="date" id="date"></span>
                                                <span class="standard" id="classes"></span>
                                            </div>
                                            <div class="examImgDescWrapper">
                                                
                                                <span class="commonDesc" id="description"></span>
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- events ends here  -->

        
    <?php endif; ?>

    
    <?php if(isset($schoolSettings['counter_status']) && $schoolSettings['counter_status'] == 1): ?>
        <section class="programs ctcaSection commonMT">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="flex_column_center">
                            <span class="commonTag"> <?php echo e($schoolSettings['counter_section'] ?? 'CTCA - Contes'); ?> </span>
                            <span class="commonTitle">
                                <?php echo e($schoolSettings['counter_title'] ?? 'Educational Programs for every Stage'); ?>

                            </span>

                            <span class="commonDesc">
                                <?php echo e($schoolSettings['counter_description'] ?? ''); ?>

                            </span>
                        </div>
                    </div>

                    <div class="col-12 mt-5">
                        <div class="row ctcaCardsRow">
                            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                                <div class="cardBg">
                                <div class="card">
                                    <div class="imgBg">
                                        <img src="<?php echo e($schoolSettings['counter_teacher'] ?? asset('assets/school/images/teachers.png')); ?>" class="" alt="..." />
                                    </div>
                                    <div class="cardDetails">
                                    <span class="cardTitle">Total Teacher</span>
                                    <span class="cardDesc"><?php echo e(count($teachers)); ?></span>
                                    </div>
                                </div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                                <div class="cardBg">
                                <div class="card">
                                    <div class="imgBg">
                                        <img src="<?php echo e($schoolSettings['counter_student'] ?? asset('assets/school/images/students.png')); ?>"
                                        class="" alt="..." />
                                    </div>
                                    <div class="cardDetails">
                                    <span class="cardTitle">Total Student</span>
                                    <span class="cardDesc"><?php echo e($counters['students'] ?? 0); ?></span>
                                    </div>
                                </div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                                <div class="cardBg">
                                <div class="card">
                                    <div class="imgBg">
                                        <img src="<?php echo e($schoolSettings['counter_class'] ?? asset('assets/school/images/classes.png')); ?>"
                                        class="" alt="..." />
                                    </div>
                                    <div class="cardDetails">
                                    <span class="cardTitle">Total Class</span>
                                    <span class="cardDesc"><?php echo e($counters['classes'] ?? 0); ?></span>
                                    </div>
                                </div>
                                </div>
                            </div>
                            
                            
                            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                                <div class="cardBg">
                                <div class="card">
                                    <div class="imgBg">
                                        <img src="<?php echo e($schoolSettings['counter_stream'] ?? asset('assets/school/images/streams.png')); ?>"
                                        class="" alt="..." />
                                    </div>
                                    <div class="cardDetails">
                                    <span class="cardTitle">Total Stream</span>
                                    <span class="cardDesc"><?php echo e($counters['streams'] ?? 0); ?></span>
                                    </div>
                                </div>
                                </div>
                            </div>
                            
                        </div>
                        <div class="sideImgs">
                            <img src="<?php echo e(asset('assets/school/images/pen.png')); ?>" class="colorImg" alt="colorImg" />
                            <img src="<?php echo e(asset('assets/school/images/triangle.png')); ?>" class="bagImg" alt="bagImg" />
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    
    <?php echo $__env->make('school-website.our_teacher_section', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    
    
    <?php if(isset($schoolSettings['faqs_status']) && $schoolSettings['faqs_status'] == 1 && count($faqs)): ?>
        <section class="faqs commonMT" id="faqs">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="flex_column_center">
                            <span class="commonTag"> <?php echo e($schoolSettings['faqs_section'] ?? 'Frequently Asked Questions'); ?>

                            </span>
                            <span class="commonTitle">
                                <?php echo e($schoolSettings['faqs_title'] ?? 'Know More About eSchool'); ?>


                            </span>

                            <span class="commonDesc">
                                <?php echo e($schoolSettings['faqs_description'] ?? ''); ?>

                            </span>
                        </div>
                    </div>

                    <div class="col-12 lowerDiv">
                        <div class="accordion" id="accordionExample">

                            <?php $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="<?php echo e($faq->id); ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapseOne-<?php echo e($faq->id); ?>" aria-expanded="true"
                                            aria-controls="collapseOne-<?php echo e($faq->id); ?>">
                                            <span> <?php echo e($loop->index + 1); ?>. <?php echo e($faq->title); ?> </span>
                                        </button>
                                    </h2>
                                    <div id="collapseOne-<?php echo e($faq->id); ?>" class="accordion-collapse collapse"
                                        aria-labelledby="<?php echo e($faq->id); ?>" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <span>
                                                <?php echo nl2br(e($faq->description)); ?>

                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div class="sideImgs">
                            <img src="<?php echo e(asset('assets/school/images/ques2.png')); ?>" class="colorImg" alt="colorImg" />
                            <img src="<?php echo e(asset('assets/school/images/ques1.png')); ?>" class="bagImg" alt="bagImg" />
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- faqs ends here  -->
    <?php endif; ?>

    
    <?php if(isset($schoolSettings['gallery_status']) && $schoolSettings['gallery_status'] == 1): ?>
        <?php echo $__env->make('school-website.gallery_section', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>
    
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Function to open the modal
            function openModal(title, date, classes, description) {
                setTimeout(() => {
                    $('#title').html(title);
                    $('#classes').html(classes);
                    $('#description').html(description);
                    $('#date').html(date);
                }, 200);
                
            }

            // Add click event to each card
            document.querySelectorAll('.open-modal').forEach(card => {
                card.addEventListener('click', () => {
                    const title = card.querySelector('.eventTitle').textContent;
                    const date = card.querySelector('.eventDate').textContent;
                    const classes = card.querySelector('.eventClasses').textContent;
                    const description = card.querySelector('.eventDesc').textContent;
                    openModal(title, date, classes, description);
                    
                });
            });

        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.school.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/school-website/index.blade.php ENDPATH**/ ?>