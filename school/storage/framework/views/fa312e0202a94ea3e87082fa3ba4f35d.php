<?php $__env->startSection('content'); ?>
<style>
    :root {
    --primary-color: <?php echo e($settings['theme_primary_color'] ?? '#56cc99'); ?>;
    --secondary-color: <?php echo e($settings['theme_secondary_color'] ?? '#215679'); ?>;
    --secondary-color1: <?php echo e($settings['theme_secondary_color_1'] ?? '#38a3a5'); ?>;
    --primary-background-color: <?php echo e($settings['theme_primary_background_color'] ?? '#f2f5f7'); ?>;
    --text--secondary-color: <?php echo e($settings['theme_text_secondary_color'] ?? '#5c788c'); ?>;
    
}

    /* Pricing Section Styles */
    .pricingSect {
        padding: 60px 0;
    }
    
    .pricingCard {
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 16px;
        padding: 35px 25px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }
    
    .pricingCard:hover {
        transform: translateY(-8px);
        box-shadow: 0 16px 35px rgba(0, 0, 0, 0.08);
        border-color: var(--primary-color);
    }
    
    .pricingCard.highlightedCard {
        border: 2px solid var(--primary-color);
        box-shadow: 0 8px 24px rgba(86, 204, 153, 0.12);
    }
    
    .pricingCard.highlightedCard:hover {
        box-shadow: 0 16px 36px rgba(86, 204, 153, 0.22);
    }
    
    .popularBadge {
        position: absolute;
        top: 18px;
        right: -32px;
        background: var(--primary-color);
        color: #ffffff;
        font-size: 10px;
        font-weight: 700;
        padding: 4px 30px;
        transform: rotate(45deg);
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .pricingCard .cardHeader {
        margin-bottom: 20px;
        text-align: center;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        padding-bottom: 18px;
    }
    
    .pricingCard .planName {
        font-size: 20px;
        font-weight: 700;
        color: var(--secondary-color);
        margin-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .pricingCard .planTagline {
        font-size: 13px;
        color: var(--text--secondary-color);
        margin-bottom: 0;
    }
    
    .pricingCard .cardPrice {
        text-align: center;
        margin-bottom: 25px;
        padding: 10px 0;
    }
    
    .pricingCard .currency {
        font-size: 24px;
        font-weight: 600;
        color: var(--secondary-color);
        vertical-align: top;
        position: relative;
        top: 4px;
    }
    
    .pricingCard .priceAmount {
        font-size: 38px;
        font-weight: 800;
        color: var(--secondary-color);
        line-height: 1;
    }
    
    .pricingCard .priceDuration {
        font-size: 14px;
        color: var(--text--secondary-color);
        font-weight: 500;
    }
    
    .pricingCard .monthlyEquiv {
        font-size: 13px;
        color: var(--primary-color);
        font-weight: 600;
        margin-top: 5px;
    }
    
    .pricingCard .cardBody {
        flex-grow: 1;
        margin-bottom: 30px;
    }
    
    .pricingCard .planFeatures {
        list-style: none;
        padding: 0;
        margin: 0;
        text-align: left;
    }
    
    .pricingCard .planFeatures li {
        font-size: 14px;
        color: #4a5568;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .pricingCard .planFeatures li i {
        font-size: 16px;
        flex-shrink: 0;
    }
    
    .pricingCard .planFeatures li.moreFeatures {
        font-weight: 600;
        color: var(--secondary-color1);
    }
    
    .pricingCard .cardFooter {
        text-align: center;
    }
    
    .pricingCard .choosePlanBtn {
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        font-weight: 600;
        background-color: var(--primary-color);
        color: #ffffff;
        border: none;
        outline: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .pricingCard .choosePlanBtn:hover {
        background-color: var(--secondary-color);
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
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
                        <a href="#home"><?php echo e(__('home')); ?></a>
                    </li>
                    <li>
                        <a href="#features"><?php echo e(__('features')); ?></a>
                    </li>
                    <li>
                        <a href="#pricing"><?php echo e(__('pricing')); ?></a>
                    </li>
                    <li>
                        <a href="#about-us"><?php echo e(__('about_us')); ?></a>
                    </li>

                    <?php if(count($faqs)): ?>
                        <li>
                            <a href="#faq"><?php echo e(__('faqs')); ?></a>
                        </li>    
                    <?php endif; ?>
                    <li>
                        <a href="#contact-us"><?php echo e(__('contact')); ?></a>
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
                <button class="commonBtn" id="trialBtn" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><?php echo e(__('start_trial')); ?></button>
                
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
                        <a href="#home"><?php echo e(__('home')); ?></a>
                    </li>
                    <li>
                        <a href="#features"><?php echo e(__('features')); ?></a>
                    </li>
                    <li>
                        <a href="#pricing"><?php echo e(__('pricing')); ?></a>
                    </li>
                    <li>
                        <a href="#about-us"><?php echo e(__('about_us')); ?></a>
                    </li>

                    <?php if(count($faqs)): ?>
                        <li>
                            <a href="#faq"><?php echo e(__('faqs')); ?></a>
                        </li>    
                    <?php endif; ?>
                    <li>
                        <a href="#contact-us"><?php echo e(__('contact')); ?></a>
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

<div class="main">

    <section class="heroSection" id="home">
        <div class="linesBg">
            <div class="colorBg">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 col-lg-6">
                            <div class="flex_column_start">
                                <span class="commonTitle"><?php echo e($settings['system_name']  ?? 'eSchool SaaS'); ?></span>
                                <span class="commonDesc">
                                    <?php echo e($settings['tag_line']); ?>

                                </span>
                                <span class="commonText">
                                    <?php echo e($settings['hero_description']); ?></span>
                                <div class="d-flex">
                                    <button class="commonBtn" style="margin-right: 40px" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><?php echo e(__('register_your_school')); ?></button>                           
                                    <?php if($isDemoSchool == 1): ?>
                                        <a href="<?php echo e($demoSchoolUrl ?? url('/')); ?>" target="_blank" class="commonBtn"><?php echo e(__('demo_school')); ?></a>
                                    <?php endif; ?>   
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-6 heroImgWrapper">
                            <div class="heroImg">
                                <img src="<?php echo e($settings['home_image'] ?? asset('assets/landing_page_images/heroImg.png')); ?>" alt="">
                                <div class="topRated card">
                                    <div>
                                        <img src="<?php echo e($settings['hero_title_2_image'] ?? asset('assets/landing_page_images/user.png')); ?>" alt="">
                                    </div>
                                    <?php if(!empty($settings['hero_title_2'])): ?>
                                        <div>
                                            <span><?php echo e($settings['hero_title_2']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if(!empty($settings['hero_title_1'])): ?>
                                    <div class="textWrapper">
                                        <span><?php echo e($settings['hero_title_1']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php echo $__env->make('registration_form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    </section>
    <!-- heroSection ends here  -->

    <section class="features commonMT container" id="features">
        <div class="row">
            <div class="col-12">
                <div class="sectionTitle">
                    <span><?php echo e(__('explore_our_top_features')); ?></span>

                </div>
            </div>
            <div class="col-12">
                <div class="row cardWrapper">
                    <?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($key < 9): ?>
                            <div class="col-sm-12 col-md-6 col-lg-4">
                                <div class="card">
                                    <div>
                                        <img src="<?php echo e(asset('assets/landing_page_images/features/')); ?>/<?php echo e($feature->name); ?>.svg" alt="">
                                    </div>
                                    <div><span><?php echo e(__($feature->name)); ?></span></div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="col-sm-12 col-md-6 col-lg-4 default-feature-list" style="display: none">
                                <div class="card">
                                    <div>
                                        <img src="<?php echo e(asset('assets/landing_page_images/features/')); ?>/<?php echo e($feature->name); ?>.svg" alt="">
                                    </div>
                                    <div><span><?php echo e(__($feature->name)); ?></span></div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-12">
                        <button class="commonBtn view-more-feature" value="1"><?php echo e(__('view_more_features')); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- features ends here  -->
    
    <!-- pricing plans start here -->
    <section class="pricingSect commonMT container" id="pricing">
        <div class="row">
            <div class="col-12">
                <div class="sectionTitle">
                    <span><?php echo e(__('pricing_plans')); ?></span>
                </div>
            </div>
            <div class="col-12 mt-4">
                <div class="row justify-content-center">
                    <?php $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $package): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($package->is_trial == 1): ?>
                            <?php continue; ?>
                        <?php endif; ?>
                        <div class="col-sm-12 col-md-6 col-lg-3 mb-4">
                            <div class="pricingCard <?php echo e($package->highlight ? 'highlightedCard' : ''); ?>">
                                <?php if($package->highlight): ?>
                                    <div class="popularBadge"><?php echo e(__('popular')); ?></div>
                                <?php endif; ?>
                                <div>
                                    <div class="cardHeader">
                                        <h3 class="planName"><?php echo e($package->name); ?></h3>
                                        <p class="planTagline"><?php echo e($package->tagline ?? __('school_management_system')); ?></p>
                                    </div>
                                    <div class="cardPrice">
                                        <span class="currency">₹</span>
                                        <span class="priceAmount"><?php echo e(number_format($package->charges)); ?></span>
                                        <span class="priceDuration">/ <?php echo e($package->days); ?> <?php echo e(__('days')); ?></span>
                                        <?php if($package->days > 30 && $package->charges > 0): ?>
                                            <div class="monthlyEquiv">
                                                (₹<?php echo e(number_format(round($package->charges / ($package->days / 30)))); ?>/<?php echo e(__('month')); ?>)
                                            </div>
                                        <?php endif; ?>
                                        <div class="trialBadgeContainer" style="margin-top: 10px;">
                                            <span class="trial-badge" style="display: inline-block; background-color: rgba(86, 204, 153, 0.15); color: var(--primary-color); padding: 5px 12px; border-radius: 20px; font-weight: 700; font-size: 12px;">
                                                <i class="fa-solid fa-gift"></i> 1 Month Free for ₹1
                                            </span>
                                        </div>
                                    </div>
                                    <div class="cardBody">
                                        <ul class="planFeatures">
                                            <li><i class="fa-solid fa-circle-check text-success"></i> <?php echo e(__('students')); ?>: <?php echo e(number_format($package->no_of_students)); ?></li>
                                            <li><i class="fa-solid fa-circle-check text-success"></i> <?php echo e(__('staffs')); ?>: <?php echo e(number_format($package->no_of_staffs)); ?></li>
                                            <li><i class="fa-solid fa-circle-check text-success"></i> Student Management</li>
                                            <li><i class="fa-solid fa-circle-check text-success"></i> Academics Management</li>
                                            <li><i class="fa-solid fa-circle-check text-success"></i> Teacher & Staff Management</li>
                                            <li><i class="fa-solid fa-circle-check text-success"></i> Fees & Expense Management</li>
                                            <li><i class="fa-solid fa-circle-check text-success"></i> Exams & Grades Management</li>
                                            <li><i class="fa-solid fa-circle-check text-success"></i> Attendance & Leaves Management</li>
                                            <li><i class="fa-solid fa-circle-check text-success"></i> Live Classes & Assignments</li>
                                            <li><i class="fa-solid fa-circle-check text-success"></i> Announcements & Holidays</li>
                                            <li class="moreFeatures"><i class="fa-solid fa-circle-plus text-primary"></i> All Premium Features Included</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="cardFooter">
                                    <button class="choosePlanBtn" data-package-id="<?php echo e($package->id); ?>">
                                        <?php echo e($package->is_trial ? __('start_trial') : __('choose_plan')); ?>

                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </section>
    <!-- pricing plans ends here -->

    
    <!-- swiperSect ends here  -->
    
    


    <?php $__currentLoopData = $featureSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(($key + 1) % 2 != 0): ?>

        <section class="left-section-<?php echo e($section->id); ?> commonMT container">
            <div class="row">
                <div class="col-12">
                    <div class="sectionTitle">
                        <span class="greenText"><?php echo e($section->title); ?></span>
                        <span>
                            <?php echo e($section->heading); ?>

                        </span>
    
                    </div>
                </div>
                <div class="col-12 tabsContainer " style="word-break: break-word;">
                    <div class="row">
                        <div class="col-lg-6 tabsMainWrapper" style="word-break: break-all !important;">
                            <div class="tabsWrapper" >
                                <div class="tabs">
                                    <?php $__currentLoopData = $section->feature_section_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section_feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="tab tab-<?php echo e($section_feature->id); ?>-<?php echo e($key); ?>">
                                            <span><?php echo e($section_feature->feature); ?></span>
                                            <span>
                                                <?php echo e($section_feature->description); ?>

                                            </span>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
    
                        </div>
    
                        <div class="col-lg-6 contentWrapper">
                            <div class="content-container">
                                <?php $__currentLoopData = $section->feature_section_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section_feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="content tab-<?php echo e($section_feature->id); ?>-<?php echo e($key); ?>">
                                        <img src="<?php echo e($section_feature->image); ?>" alt="">
                                    </div>    
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
    
                    </div>
                </div>
            </div>
        </section>

        <?php else: ?>

        <section class="right-section-<?php echo e($section->id); ?> right-feature-section commonMT">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="sectionTitle">
                            <span class="greenText"><?php echo e($section->title); ?></span>
                            <span>
                                <?php echo e($section->heading); ?>

                            </span>
    
                        </div>
                    </div>
                    <div class="col-12 tabsContainer">
                        <div class="row reverseWrapper">
                            <div class="col-lg-6 contentWrapper">
                                <div class="content-container">
                                    <?php $__currentLoopData = $section->feature_section_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section_feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="content tab-<?php echo e($section_feature->id); ?>-<?php echo e($key); ?>">
                                            <img src="<?php echo e($section_feature->image); ?>" alt="">
                                        </div>    
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
    
                            <div class="col-lg-6 tabsMainWrapper">
                                <div class="tabsWrapper">
                                    <div class="tabs">
                                        <?php $__currentLoopData = $section->feature_section_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section_feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="tab tab-<?php echo e($section_feature->id); ?>-<?php echo e($key); ?>">
                                                <span><?php echo e($section_feature->feature); ?></span>
                                                <span>
                                                    <?php echo e($section_feature->description); ?>

                                                </span>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
    
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    
        </section>

        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <section class="whyBest container commonMT" id="about-us">
        <div class="row">
            <div class="col-lg-6">
                <div class="whyBestTextWrapper">
                    <p><?php echo e($settings['about_us_title']); ?></p>
                    <p><?php echo e($settings['about_us_heading']); ?></p>
                </div>
                <p class="whyBestPara">
                    <?php echo e($settings['about_us_description']); ?>

                </p>

                <div class="listWrapper">
                    <?php $__currentLoopData = $about_us_lists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span>
                            <i class="fa-regular fa-circle-check"></i>
                            <?php echo e($point); ?>

                        </span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="col-lg-6">
                <img src="<?php echo e($settings['about_us_image'] ?? asset('assets/landing_page_images/whyBestImg.png')); ?>" alt="">
            </div>
        </div>
    </section>
    <!-- whyBest ends here  -->


    <?php if(count($faqs)): ?>
        <section class="faqs commonMT" id="faq">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="sectionTitle">
                            <span><?php echo e(__('frequently_asked_questions')); ?></span>

                        </div>
                    </div>

                    <div class="col-12">
                        <div class="accordion" id="accordionExample">
                            <?php $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapseOne-<?php echo e($faq->id); ?>" aria-expanded="true" aria-controls="collapseOne-<?php echo e($faq->id); ?>">
                                            <span>
                                                <?php echo e($faq->title); ?>

                                            </span>
                                        </button>
                                    </h2>
                                    <div id="collapseOne-<?php echo e($faq->id); ?>" class="accordion-collapse collapse"
                                        aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <span>
                                                <?php echo nl2br(e($faq->description)); ?>

                                            </span>
                                        </div>
                                    </div>
                                </div>  
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>
    <!-- faqs ends here  -->

    <section class="getInTouch commonMT" id="contact-us">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="sectionTitle">
                        <span class="greenText"><?php echo e(__('lets_get_in_touch')); ?></span>
                        <span><?php echo e(__('have_a_question_or_just_want_to_say_hi_Wed_love_to_hear_from_you')); ?>

                        </span>

                    </div>
                    <div class="col-12">
                        <div class="row wrapper">
                            <div class="col-lg-6">
                                <form action="<?php echo e(url('contact')); ?>" method="post" role="form" class="php-email-form mb-5 create-form-with-captcha">
                                    <?php echo csrf_field(); ?>
                                    <div class="card">
                                        <div>
                                            <input type="text" required name="name" id="name" placeholder="<?php echo e(__('enter_your_name')); ?>">
                                        </div>
                                        <div>
                                            <input type="email" required name="email" id="email" placeholder="<?php echo e(__('enter_your_email')); ?>">
                                        </div>
                                        <div>
                                            <textarea name="message" required id="message" cols="30" rows="6"
                                                placeholder="<?php echo e(__('send_your_message')); ?>"></textarea>
                                        </div>
                                        <?php if(config('services.recaptcha.key') ?? ''): ?>
                                            <div>
                                                <div class="g-recaptcha" data-sitekey=<?php echo e(config('services.recaptcha.key')); ?>></div>
                                            </div>    
                                        <?php endif; ?>
                                        <div>
                                            <button class="commonBtn"><?php echo e(__('send')); ?></button>
                                        </div>
                                        <div>
                                            <img src="<?php echo e(asset('assets/landing_page_images/GetInTouchDots.png')); ?>" class="sideImg dots" alt="">
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-lg-6 infoBox">
                                <div class="infoWrapper">
                                    <div>
                                        <span class="icon"><i class="fa-solid fa-phone-volume"></i></span>
                                    </div>
                                    <div>
                                        <span><?php echo e(__('phone')); ?></span>
                                        <span><?php echo e(__('mobile')); ?> : <?php echo e($settings['mobile'] ?? ''); ?></span>
                                    </div>
                                </div>
                                <div class="infoWrapper">
                                    <div>
                                        <span class="icon"><i class="fa-solid fa-envelope-open-text"></i></span>
                                    </div>
                                    <div>
                                        <span><?php echo e(__('email')); ?></span>
                                        <span><?php echo e($settings['mail_send_from'] ?? 'example@gmail.com'); ?></span>
                                    </div>
                                </div>
                                <div class="infoWrapper">
                                    <div>
                                        <span class="icon"><i class="fa-solid fa-location-dot"></i></span>
                                    </div>
                                    <div>
                                        <span><?php echo e(__('location')); ?></span>
                                        <span><?php echo e($settings['address'] ?? ''); ?></span>
                                    </div>
                                </div>
                                <div>
                                    <img src="<?php echo e(asset('assets/landing_page_images/lineCircle.png')); ?>" class="lineCircle sideImg" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <section class="ourApp container commonMT">
        <div class="row">
            <div class="col-lg-6">
                <img src="<?php echo e($settings['download_our_app_image'] ?? asset('assets/landing_page_images/ourApp.png')); ?>" class="ourAppImg" alt="">
            </div>
            <div class="col-lg-6 content">
                <div class="text">
                    <span class="title"><?php echo e(__('download_our_app_now')); ?></span>
                    <span>
                        <?php echo e($settings['download_our_app_description'] ?? ''); ?>

                    </span>
                </div>
                <div class="storeImgs">
                    <a href="<?php echo e($settings['app_link'] ?? ''); ?>" target="_blank"> <img src="<?php echo e(asset('assets/landing_page_images/Google play.png')); ?>" alt=""> </a>
                    <a href="<?php echo e($settings['ios_app_link'] ?? ''); ?>" target="_blank"> <img src="<?php echo e(asset('assets/landing_page_images/iOS app Store.png')); ?>" alt=""> </a>
                </div>
            </div>
        </div>
    </section>
</div>


<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script async src="https://www.google.com/recaptcha/api.js"></script>
    <?php $__currentLoopData = $featureSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const tabs = document.querySelectorAll('.left-section-<?php echo e($section->id); ?> .tab');
                const contents = document.querySelectorAll('.left-section-<?php echo e($section->id); ?> .content');

                function switchTab(event, tabNumber) {
                    tabs.forEach((tab) => {
                        tab.classList.remove('active');
                    });

                    event.target.classList.add('active');

                    contents.forEach((content) => {
                        content.classList.remove('active');
                    });

                    contents[tabNumber - 1].classList.add('active');
                }

                tabs.forEach((tab, index) => {
                    tab.addEventListener('click', (event) => {
                        switchTab(event, index + 1);
                    });
                });

                setTimeout(() => {
                    tabs[0].click();
                }, 1000);
            });

            document.addEventListener('DOMContentLoaded', () => {
                const tabs = document.querySelectorAll('.right-section-<?php echo e($section->id); ?> .tab');
                const contents = document.querySelectorAll('.right-section-<?php echo e($section->id); ?> .content');

                function switchTab(event, tabNumber) {
                    tabs.forEach((tab) => {
                        tab.classList.remove('active');
                    });

                    event.target.classList.add('active');

                    contents.forEach((content) => {
                        content.classList.remove('active');
                    });

                    contents[tabNumber - 1].classList.add('active');
                }

                tabs.forEach((tab, index) => {
                    tab.addEventListener('click', (event) => {
                        switchTab(event, index + 1);
                    });
                });

                setTimeout(() => {
                    tabs[0].click();
                }, 1000);
            });
        </script>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <script>
        $('.redirect-login').click(function (e) { 
            e.preventDefault();
            window.location.href = "<?php echo e(url('login')); ?>"
        });
        
        $('.choosePlanBtn').click(function (e) { 
            e.preventDefault();
            var packageId = $(this).data('package-id');
            $('input[name="trial_package"]').val(packageId);
            $('#staticBackdrop').modal('show');
        });
    </script>
    <script>
        <?php if(Session::has('success')): ?>
        $.toast({
            text: '<?php echo e(Session::get('success')); ?>',
            showHideTransition: 'slide',
            icon: 'success',
            loaderBg: '#f96868',
            position: 'top-right',
            bgColor: '#20CFB5'
        });
        <?php endif; ?>

        <?php if(Session::has('error')): ?>
        $.toast({
            text: '<?php echo e(Session::get('error')); ?>',
            showHideTransition: 'slide',
            icon: 'error',
            loaderBg: '#f2a654',
            position: 'top-right',
            bgColor: '#FE7C96'
        });
        <?php endif; ?>
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.home_page.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/home.blade.php ENDPATH**/ ?>