<footer class="commonMT">
    <div class="container">
        <div class="row">
            <div class="col-sm-6 col-md-6 col-lg-6">
                <div class="companyInfoWrapper">
                    <div>
                        <a href="<?php echo e(url('/')); ?>">
                            <img src="<?php echo e($settings['horizontal_logo'] ?? asset('assets/landing_page_images/Logo1.svg')); ?>" class="logo" alt="">
                        </a>
                    </div>
                    <div>
                        <span class="commonDesc">
                            <?php echo e($settings['short_description'] ?? ''); ?>

                        </span>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-md-6 col-lg-2">
                <div class="linksWrapper usefulLinksDiv">
                    <span class="title"><?php echo e(__('links')); ?></span>
                    <span><a href="<?php echo e(url('/')); ?>"><?php echo e(__('home')); ?></a></span>
                    <span><a href="<?php echo e(url('/#features')); ?>"><?php echo e(__('features')); ?></a></span>
                    <span><a href="<?php echo e(url('/#faq')); ?>"><?php echo e(__('faqs')); ?></a></span>
                </div>
            </div>

            <div class="col-sm-6 col-md-6 col-lg-2">
                <div class="linksWrapper">
                    <span class="title"><?php echo e(__('info')); ?></span>
                    <span>
                        <a href="<?php echo e(url('/#about-us')); ?>">
                            <?php echo e(__('about_us')); ?>

                        </a>
                    </span>
                    <span>
                        <a href="<?php echo e(url('/#contact-us')); ?>">
                            <?php echo e(__('contact')); ?>

                        </a>
                    </span>

                    <span>
                        <a href="<?php echo e(url('page/type/privacy-policy')); ?>">
                            <?php echo e(__('privacy_policy')); ?>

                        </a>
                    </span>

                    <span>
                        <a href="<?php echo e(url('page/type/terms-conditions')); ?>">
                            <?php echo e(__('terms_condition')); ?>

                        </a>
                    </span>

                    <span>
                        <a href="<?php echo e(url('page/type/refund-cancellation')); ?>">
                            <?php echo e(__('refund_cancellation')); ?>

                        </a>
                    </span>
                </div>
            </div>

            <?php if(isset($settings['facebook']) || isset($settings['instagram']) || isset($settings['linkedin'])): ?>
                    <div class="col-sm-6 col-md-6 col-lg-2">
                        <div class="linksWrapper">
                            <span class="title"><?php echo e(__('follow')); ?></span>

                            <?php if(isset($settings['facebook'])): ?>
                                <span class="iconsWrapper">
                                    <a href="<?php echo e($settings['facebook']); ?>" target="_blank">
                                        <span class="mr-2">
                                            <i class="<?php echo e($settings['facebook_icon'] ?? 'fab fa-facebook'); ?>" style="font-size: 18px; color: var(--secondary-color);"></i>
                                        </span>
                                        <span>
                                            <?php echo e($settings['facebook_name'] ?? __('facebook')); ?>

                                        </span>
                                    </a>
                                </span>    
                            <?php endif; ?>

                            <?php if(isset($settings['instagram'])): ?>
                                <span class="iconsWrapper">
                                    <a href="<?php echo e($settings['instagram']); ?>" target="_blank">
                                        <span class="mr-2">
                                            <i class="<?php echo e($settings['instagram_icon'] ?? 'fab fa-instagram'); ?>" style="font-size: 18px; color: var(--secondary-color);"></i>
                                        </span>
                                        <span>
                                            <?php echo e($settings['instagram_name'] ?? __('instagram')); ?>

                                        </span>
                                    </a>
                                </span>    
                            <?php endif; ?>

                            <?php if(isset($settings['linkedin'])): ?>
                                <span class="iconsWrapper">
                                    <a href="<?php echo e($settings['linkedin']); ?>" target="_blank">
                                        <span class="mr-2">
                                            <i class="<?php echo e($settings['linkedin_icon'] ?? 'fab fa-linkedin'); ?>" style="font-size: 18px; color: var(--secondary-color);"></i>
                                        </span>
                                        <span>
                                            <?php echo e($settings['linkedin_name'] ?? __('linkedin')); ?>

                                        </span>
                                    </a>
                                </span>    
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

            <hr>

            <div class="col-12 copyright">
                <?php if(isset($settings['footer_text']) && $settings['footer_text']): ?>
                    <span class="copyright footer-text"><span class="me-1">&copy; <?php echo e(date('Y')); ?></span> <?php echo $settings['footer_text']; ?></span>
                <?php endif; ?>
            </div>

        </div>
    </div>
</footer><?php /**PATH /home/shacartc/school.tehub.in/resources/views/layouts/home_page/footer.blade.php ENDPATH**/ ?>