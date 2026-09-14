<header class="navbar">
    <div class="container">
        <div class="navbarWrapper">
            <div class="navLogoWrapper">
                <div class="navLogo">
                    <a href="<?php echo e(url('/')); ?>">
                        <img src="" class="nav-logo companyLogo" alt="" />
                    </a>
                </div>
            </div>
            <div class="menuListWrapper">
                <ul class="listItems">
                    <li>
                        <a href="<?php echo e(url('/')); ?>"><?php echo e(__('home')); ?></a>
                    </li>
                    <?php if((isset($schoolSettings['about_us_status']) && $schoolSettings['about_us_status'] == 1) || 
                    (isset($schoolSettings['our_mission_status']) && $schoolSettings['our_mission_status'] == 1)): ?>
                        <li>
                            <a href="<?php echo e(url('school/about-us')); ?>"><?php echo e(__('about_us')); ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if(isset($schoolSettings['gallery_status']) && $schoolSettings['gallery_status'] == 1): ?>
                        <li>
                            <div class="dropdown">
                                <a class="btn btn-secondary dropdown-toggle" href="#" role="button"
                                    id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                                   <?php echo e(__('gallery')); ?>

                                </a>

                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                    <li>
                                        <a class="dropdown-item" href="<?php echo e(url('school/photos')); ?>"><?php echo e(__('photos')); ?></a>
                                    </li>
                                    <hr />
                                    <li>
                                        <a class="dropdown-item" href="<?php echo e(url('school/videos')); ?>"><?php echo e(__('videos')); ?></a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    <?php endif; ?>
                    <?php if(isset($schoolSettings['faqs_status']) && $schoolSettings['faqs_status'] == 1 ): ?>
                        <li>
                            <a href="<?php echo e(url('/#faqs')); ?>"><?php echo e(__('faqs')); ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if(isset($schoolSettings['contact_us_status']) && $schoolSettings['contact_us_status'] == 1 ): ?>     
                        <li>
                            <a href="<?php echo e(url('school/contact-us')); ?>"><?php echo e(__('contact_us')); ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="hamburg">
                    <span data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight"><i
                            class="fa-solid fa-bars"></i></span>
                </div>
            </div>
            <div class="loginWrapper">
                <?php if(isset($schoolSettings['online_registration_status']) && $schoolSettings['online_registration_status'] == 1): ?>
                    <button class="commonBtn admissionBtn">
                        <div class="default-btn">
                            <a href="<?php echo e(route('online-admission.index')); ?>"><?php echo e(__('admission_open')); ?></a>
                        </div>
                        <div class="hover-btn">
                            <a href="<?php echo e(route('online-admission.index')); ?>"><?php echo e(__('apply_now')); ?></a>
                        </div>
                    </button>
                    <?php endif; ?>
                <button class="commonBtn redirect-login"><?php echo e(__('login')); ?><i class="fa-regular fa-user"></i></button>
            </div>
        </div>

        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
            <div class="offcanvas-header">
                <div class="navLogoWrapper">
                    <div class="navLogo">
                        <img src="" alt="" class="nav-logo" />
                    </div>
                </div>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="listItems">
                    <li>
                        <a href="<?php echo e(url('/')); ?>"><?php echo e(__('home')); ?></a>
                    </li>
                    <?php if((isset($schoolSettings['about_us_status']) && $schoolSettings['about_us_status'] == 1) || 
                    (isset($schoolSettings['our_mission_status']) && $schoolSettings['our_mission_status'] == 1)): ?>
                        <li>
                            <a href="<?php echo e(url('school/about-us')); ?>"><?php echo e(__('about_us')); ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if(isset($schoolSettings['gallery_status']) && $schoolSettings['gallery_status'] == 1): ?>
                        <li>
                            <div class="dropdown">
                                <a class="btn btn-secondary dropdown-toggle" href="#" role="button"
                                    id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php echo e(__('gallery')); ?>

                                </a>

                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                    <li>
                                        <a class="dropdown-item" href="<?php echo e(url('school/photos')); ?>"><?php echo e(__('photos')); ?>s</a>
                                    </li>
                                    <hr />
                                    <li>
                                        <a class="dropdown-item" href="<?php echo e(url('school/videos')); ?>"><?php echo e(__('videos')); ?></a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    <?php endif; ?>
                    <?php if(isset($schoolSettings['faqs_status']) && $schoolSettings['faqs_status'] == 1 ): ?>
                        <li>
                            <a href="#faqs"><?php echo e(__('faqs')); ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if(isset($schoolSettings['contact_us_status']) && $schoolSettings['contact_us_status'] == 1 ): ?>    
                        <li>
                            <a href="<?php echo e(url('school/contact-us')); ?>"><?php echo e(__('contact_us')); ?></a>
                        </li>
                    <?php endif; ?>
                    <div class="loginWrapper">
                        <?php if(isset($schoolSettings['online_registration_status']) && $schoolSettings['online_registration_status'] == 1): ?>
                            <button class="commonBtn admissionBtn">
                                <div class="default-btn">
                                    <a href="<?php echo e(route('online-admission.index')); ?>"><?php echo e(__('admission_open')); ?></a>
                                </div>
                                <div class="hover-btn">
                                    <a href="<?php echo e(route('online-admission.index')); ?>"><?php echo e(__('apply_now')); ?></a>
                                </div>
                            </button>
                        <?php endif; ?>
                        <button class="commonBtn redirect-login"><?php echo e(__('login')); ?><i class="fa-regular fa-user"></i></button>
                    </div>
                </ul>
            </div>
        </div>
    </div>
</header>
<?php /**PATH /home/shacartc/school.tehub.in/resources/views/layouts/school/header.blade.php ENDPATH**/ ?>