<footer class="">
    <div class="container">
        <div class="row">

            <div class="col-12 infoContainer">
                <div class="row">
                    <div class="col-md-6 col-lg-4 infoDivWrapper">
                        <div class="iconDiv">
                            <span class="iconWrapper"><i class="fa-solid fa-location-dot"></i></span>
                        </div>
                        <div class="textDiv">
                            <span><?php echo e(__('school_address')); ?></span>
                            <span><?php echo e($schoolSettings['school_address'] ?? ''); ?></span>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 infoDivWrapper">
                        <div class="iconDiv">
                            <span class="iconWrapper"><i class="fa-solid fa-envelope"></i></span>
                        </div>
                        <div class="textDiv">
                            <span><?php echo e(__('mail_us')); ?></span>
                            <span><a class="footer-contact" href="mailto:<?php echo e($schoolSettings['school_email'] ?? ''); ?>"><?php echo e($schoolSettings['school_email'] ?? ''); ?></a></span>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 infoDivWrapper">
                        <div class="iconDiv">
                            <span class="iconWrapper"><i class="fa-solid fa-phone-volume"></i></i></span>
                        </div>
                        <div class="textDiv">
                            <span><?php echo e(__('call_us')); ?></span>
                            <span><a class="footer-contact" href="tel:+<?php echo e($schoolSettings['school_phone'] ?? ''); ?>"><?php echo e($schoolSettings['school_phone'] ?? ''); ?></a></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-md-6 col-lg-3">
                <div class="companyInfoWrapper">
                    <div>
                        <a href="<?php echo e(url('/')); ?>">
                            <img src="" class="footer-logo companyLogo" alt="" />
                        </a>
                    </div>
                    <div>
                        <span class="commonDesc">
                            <?php echo e($schoolSettings['short_description'] ?? ''); ?>

                        </span>
                    </div>

                    <div class="socialIcons">
                        <?php if($schoolSettings['facebook'] ?? ''): ?>
                            <span>
                                <a href="<?php echo e($schoolSettings['facebook']); ?>" target="_blank" title="<?php echo e($schoolSettings['facebook_name'] ?? 'Facebook'); ?>">
                                    <i class="<?php echo e($schoolSettings['facebook_icon'] ?? 'fa-brands fa-facebook'); ?>"></i>
                                </a>
                            </span>    
                        <?php endif; ?>

                        <?php if($schoolSettings['instagram'] ?? ''): ?>
                            <span>
                                <a href="<?php echo e($schoolSettings['instagram']); ?>" target="_blank" title="<?php echo e($schoolSettings['instagram_name'] ?? 'Instagram'); ?>">
                                    <i class="<?php echo e($schoolSettings['instagram_icon'] ?? 'fa-brands fa-instagram'); ?>"></i>
                                </a>
                            </span>    
                        <?php endif; ?>

                        <?php if($schoolSettings['linkedin'] ?? ''): ?>
                            <span>
                                <a href="<?php echo e($schoolSettings['linkedin']); ?>" target="_blank" title="<?php echo e($schoolSettings['linkedin_name'] ?? 'LinkedIn'); ?>">
                                    <i class="<?php echo e($schoolSettings['linkedin_icon'] ?? 'fa-brands fa-linkedin'); ?>"></i>
                                </a>
                            </span>    
                        <?php endif; ?>

                        <?php if($schoolSettings['twitter'] ?? ''): ?>
                            <span>
                                <a href="<?php echo e($schoolSettings['twitter']); ?>" target="_blank" title="<?php echo e($schoolSettings['twitter_name'] ?? 'Twitter'); ?>">
                                    <i class="<?php echo e($schoolSettings['twitter_icon'] ?? 'fa-brands fa-twitter'); ?>"></i>
                                </a>
                            </span>    
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-md-6 col-lg-3">
                <div class="linksWrapper usefulLinksDiv">
                    <span class="title"><?php echo e(__('useful_links')); ?></span>
                    <span><a href="<?php echo e(url('/')); ?>"><?php echo e(__('home')); ?></a></span>
                    <span><a href="<?php echo e(url('school/about-us')); ?>"><?php echo e(__('about_us')); ?></a></span>
                    <span><a href="<?php echo e(url('school/photos')); ?>"><?php echo e(__('photos')); ?></a></span>
                    <span><a href="<?php echo e(url('school/videos')); ?>"><?php echo e(__('videos')); ?></a></span>
                    <span><a href="<?php echo e(url('school/contact-us')); ?>"><?php echo e(__('contact_us')); ?></a></span>
                </div>
            </div>

            <div class="col-sm-6 col-md-6 col-lg-2">
                <div class="linksWrapper">
                    <span class="title"><?php echo e(__('quick_links')); ?></span>
                    <span>
                        <a href="<?php echo e(url('login')); ?>"> <?php echo e(__('admin_login')); ?></a>
                    </span>
                    <span>
                        <a href="<?php echo e(url('school/terms-conditions')); ?>"><?php echo e(__('terms_condition')); ?></a>
                    </span>
                    <span>
                        <a href="<?php echo e(url('school/privacy-policy')); ?>"> <?php echo e(__('privacy_policy')); ?></a>
                    </span>

                    <span>
                        <a href="<?php echo e(url('school/refund-cancellation-policy')); ?>"> <?php echo e(__('refund_cancellation')); ?></a>
                    </span>
                </div>
            </div>

            <div class="col-sm-6 col-md-6 col-lg-4">
                <div class="linksWrapper">
                    <span class="title"><?php echo e(__('download_eschool_apps')); ?></span>

                    <div class="appContainer">
                        <a class="appWrapper" href="<?php echo e($systemSettings['app_link'] ?? ''); ?>">
                            <img src="<?php echo e(asset('assets/school/images/PlayStore.png')); ?>" alt="">
                            <span class="appNameWrapper">
                                <span><?php echo e(__('student_parent')); ?></span>
                                <span><?php echo e(__('android_app')); ?></span>
                            </span>
                        </a>

                        <a class="appWrapper" href="<?php echo e($systemSettings['ios_app_link'] ?? ''); ?>">
                            <img src="<?php echo e(asset('assets/school/images/AppStore.png')); ?>" alt="">
                            <span class="appNameWrapper">
                                <span><?php echo e(__('student_parent')); ?></span>
                                <span><?php echo e(__('ios_app')); ?></span>
                            </span>
                        </a>
                    </div>

                    <div class="appContainer mt-4">
                        <a class="appWrapper" href="<?php echo e($systemSettings['teacher_app_link'] ?? ''); ?>">
                            <img src="<?php echo e(asset('assets/school/images/PlayStore.png')); ?>" alt="">
                            <span class="appNameWrapper">
                                <span><?php echo e('staff_teacher'); ?></span>
                                <span><?php echo e(__('android_app')); ?></span>
                            </span>
                        </a>
                        <a class="appWrapper" href="<?php echo e($systemSettings['teacher_ios_app_link'] ?? ''); ?>">
                            <img src="<?php echo e(asset('assets/school/images/AppStore.png')); ?>" alt="">
                            <span class="appNameWrapper">
                                <span><?php echo e('staff_teacher'); ?></span>
                                <span><?php echo e(__('ios_app')); ?></span>
                            </span>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="copyRightText">
        <span class="text-center">
            <?php echo isset($schoolSettings['footer_text']) ? $schoolSettings['footer_text'] : $systemSettings['footer_text']; ?>

        </span>
    </div>
</footer>
<?php /**PATH /home/shacartc/school.tehub.in/resources/views/layouts/school/footer.blade.php ENDPATH**/ ?>