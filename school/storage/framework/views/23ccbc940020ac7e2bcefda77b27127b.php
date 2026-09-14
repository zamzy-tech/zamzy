<?php $__env->startSection('title'); ?>
    <?php echo e(__($type ?? '')); ?> || 
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<style>
    :root {
    --primary-color: <?php echo e($settings['theme_primary_color'] ?? '#56cc99'); ?>;
    --secondary-color: <?php echo e($settings['theme_secondary_color'] ?? '#215679'); ?>;
    --secondary-color1: <?php echo e($settings['theme_secondary_color_1'] ?? '#38a3a5'); ?>;
    --primary-background-color: <?php echo e($settings['theme_primary_background_color'] ?? '#f2f5f7'); ?>;
    --text--secondary-color: <?php echo e($settings['theme_text_secondary_color'] ?? '#5c788c'); ?>;
    
}
</style>
<script src="<?php echo e(asset('assets/home_page/js/jquery-1-12-4.min.js')); ?>"></script>
<header class="navbar">
    <div class="container">
        <div class="navbarWrapper">
            <div class="navLogoWrapper">
                <div class="navLogo">
                    <a href="<?php echo e(url('/')); ?>">
                        <img src="<?php echo e($settings['horizontal_logo'] ?? asset('assets/landing_page_images/Logo1.svg')); ?>" class="logo" alt="">
                    </a>

                </div>
            </div>
            <div class="menuListWrapper">
                <ul class="listItems">
                    <li>
                        <a href="<?php echo e(url('/')); ?>"><?php echo e(__('home')); ?></a>
                    </li>
                    <li>
                        <a href="<?php echo e(url('/#features')); ?>"><?php echo e(__('features')); ?></a>
                    </li>
                    <li>
                        <a href="<?php echo e(url('/#about-us')); ?>"><?php echo e(__('about_us')); ?></a>
                    </li>
                    <li>
                        <a href="<?php echo e(url('/#pricing')); ?>"><?php echo e(__('pricing')); ?></a>
                    </li>
                    <?php if(count($faqs)): ?>
                        <li>
                            <a href="<?php echo e(url('/#faq')); ?>"><?php echo e(__('faqs')); ?></a>
                        </li>    
                    <?php endif; ?>
                    <li>
                        <a href="<?php echo e(url('/#contact-us')); ?>"><?php echo e(__('contact')); ?></a>
                    </li>
                    <?php if(count($guidances)): ?>
                        <li>
                            <div class="dropdown">
                                <a class="btn btn-secondary dropdown-toggle" href="#" role="button"
                                    id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php echo e(__('guidance')); ?>

                                </a>                                
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                    <?php $__currentLoopData = $guidances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $guidance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><a class="dropdown-item" href="<?php echo e($guidance->link); ?>"><?php echo e($guidance->name); ?></a></li>
                                        <?php if(count($guidances) > ($key + 1)): ?>
                                            <hr>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        </li>
                    <?php endif; ?>
                    <li>
                        <div class="dropdown">
                            <a class="btn btn-secondary dropdown-toggle" href="#" role="button"
                                id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo e(__('language')); ?>

                            </a>

                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                <?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><a class="dropdown-item" href="<?php echo e(url('set-language') . '/' . $language->code); ?>"><?php echo e($language->name); ?></a></li>
                                    <?php if(count($languages) > ($key + 1)): ?>
                                        <hr>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    </li>

                </ul>
                <div class="hamburg">
                    <span data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
                        aria-controls="offcanvasRight"><i class="fa-solid fa-bars"></i></span>
                </div>
            </div>

            <div class="loginBtnsWrapper">
                <button class="commonBtn redirect-login"><?php echo e(__('login')); ?></button>
                <button class="commonBtn" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><?php echo e(__('start_trial')); ?></button>
            </div>
        </div>

        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight"
            aria-labelledby="offcanvasRightLabel">
            <div class="offcanvas-header">
                <div class="navLogoWrapper">
                    <div class="navLogo">
                        <img src="<?php echo e($settings['horizontal_logo'] ?? asset('assets/landing_page_images/Logo1.svg')); ?>" alt="">
                    </div>
                </div>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="listItems">
                    <li>
                        <a href="<?php echo e(url('/')); ?>"><?php echo e(__('home')); ?></a>
                    </li>
                    <li>
                        <a href="<?php echo e(url('/#features')); ?>"><?php echo e(__('features')); ?></a>
                    </li>
                    <li>
                        <a href="<?php echo e(url('/#about-us')); ?>"><?php echo e(__('about_us')); ?></a>
                    </li>
                    <li>
                        <a href="<?php echo e(url('/#pricing')); ?>"><?php echo e(__('pricing')); ?></a>
                    </li>
                    <?php if(count($faqs)): ?>
                        <li>
                            <a href="<?php echo e(url('/#faq')); ?>"><?php echo e(__('faqs')); ?></a>
                        </li>    
                    <?php endif; ?>
                    <li>
                        <a href="<?php echo e(url('/#contact-us')); ?>"><?php echo e(__('contact')); ?></a>
                    </li>
                    <?php if(count($guidances)): ?>
                        <li>
                            <div class="dropdown">
                                <a class="btn btn-secondary dropdown-toggle" href="#" role="button"
                                    id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php echo e(__('guidance')); ?>

                                </a>                                
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                    <?php $__currentLoopData = $guidances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $guidance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><a class="dropdown-item" href="<?php echo e($guidance->link); ?>"><?php echo e($guidance->name); ?></a></li>
                                        <?php if(count($guidances) > ($key + 1)): ?>
                                            <hr>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        </li>
                    <?php endif; ?>
                    <li>
                        <div class="dropdown">
                            <a class="btn btn-secondary dropdown-toggle" href="#" role="button"
                                id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo e(__('language')); ?>

                            </a>

                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                <?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><a class="dropdown-item" href="<?php echo e(url('set-language') . '/' . $language->code); ?>"><?php echo e($language->name); ?></a></li>
                                    <?php if(count($languages) > ($key + 1)): ?>
                                        <hr>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    </li>

                </ul>

                
                    <button class="commonBtn redirect-login"><?php echo e(__('login')); ?></button>
                    <button class="commonBtn" data-bs-toggle="modal" data-bs-dismiss="offcanvas" data-bs-target="#staticBackdrop"><?php echo e(__('start_trial')); ?></button>
                
            </div>
        </div>
    </div>
</header>

<!-- navbar ends here  -->


<section class="features commonMT container" id="features">
    <div class="row">
        <div class="col-12">
            <div class="sectionTitle">
                <span><?php echo e(__($type ?? '')); ?></span>

            </div>
        </div>
        <div class="col-12">
            <div class="row cardWrapper">
                <?php if($type == 'privacy-policy'): ?>
                    <?php echo htmlspecialchars_decode($settings['privacy_policy'] ?? ''); ?>

                <?php endif; ?>
                <?php if($type == 'terms-conditions'): ?>
                    <?php echo htmlspecialchars_decode($settings['terms_condition'] ?? ''); ?>

                <?php endif; ?>
                <?php if($type == 'refund-cancellation'): ?>
                    <?php echo htmlspecialchars_decode($settings['refund_cancellation'] ?? ''); ?>

                <?php endif; ?>
            </div>
        </div>
    </div>
</section>



<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.home_page.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/terms_conditions.blade.php ENDPATH**/ ?>