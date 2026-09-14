@extends('layouts.home_page.master')

@section('content')
<style>
    :root {
    --primary-color: {{ $settings['theme_primary_color'] ?? '#56cc99' }};
    --secondary-color: {{ $settings['theme_secondary_color'] ?? '#215679' }};
    --secondary-color1: {{ $settings['theme_secondary_color_1'] ?? '#38a3a5' }};
    --primary-background-color: {{ $settings['theme_primary_background_color'] ?? '#f2f5f7' }};
    --text--secondary-color: {{ $settings['theme_text_secondary_color'] ?? '#5c788c' }};
    
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
<script src="{{ asset('assets/home_page/js/jquery-1-12-4.min.js') }}"></script>

<header class="navbar">
    <div class="container">
        <div class="navbarWrapper">
            <div class="navLogoWrapper">
                <div class="navLogo">
                    <a href="{{ url('/') }}">
                        <img src="{{ $settings['horizontal_logo'] ?? asset('assets/landing_page_images/Logo1.svg') }}" class="logo" alt="">
                    </a>

                </div>
            </div>
            <div class="menuListWrapper">
                <ul class="listItems">
                    <li>
                        <a href="#home">{{ __('home') }}</a>
                    </li>
                    <li>
                        <a href="#features">{{ __('features') }}</a>
                    </li>
                    <li>
                        <a href="#pricing">{{ __('pricing') }}</a>
                    </li>
                    <li>
                        <a href="#about-us">{{ __('about_us') }}</a>
                    </li>

                    @if (count($faqs))
                        <li>
                            <a href="#faq">{{ __('faqs') }}</a>
                        </li>    
                    @endif
                    <li>
                        <a href="#contact-us">{{ __('contact') }}</a>
                    </li>
                    @if (count($guidances))
                        <li>
                            <div class="dropdown">
                                <a class="btn btn-secondary dropdown-toggle" href="#" role="button"
                                    id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ __('guidance') }}
                                </a>                                
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                    @foreach ($guidances as $key => $guidance)
                                        <li><a class="dropdown-item" href="{{ $guidance->link }}">{{ $guidance->name }}</a></li>
                                        @if (count($guidances) > ($key + 1))
                                            <hr>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </li>
                    @endif
                    <li>
                        <div class="dropdown">
                            <a class="btn btn-secondary dropdown-toggle" href="#" role="button"
                                id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ __('language') }}
                            </a>

                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                @foreach ($languages as $key => $language)
                                    <li><a class="dropdown-item" href="{{ url('set-language') . '/' . $language->code }}">{{ $language->name }}</a></li>
                                    @if (count($languages) > ($key + 1))
                                        <hr>
                                    @endif
                                @endforeach
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
                <button class="commonBtn redirect-login">{{ __('login') }}</button>
                <button class="commonBtn" id="trialBtn" data-bs-toggle="modal" data-bs-target="#staticBackdrop">{{ __('start_trial') }}</button>
                {{-- <a href="{{ url('school/registration') }}" class="commonBtn">{{ __('start_trial') }}</a> --}}
            </div>
        </div>

        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight"
            aria-labelledby="offcanvasRightLabel">
            <div class="offcanvas-header">
                <div class="navLogoWrapper">
                    <div class="navLogo">
                        <img src="{{ $settings['horizontal_logo'] ?? asset('assets/landing_page_images/Logo1.svg') }}" alt="">
                    </div>
                </div>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="listItems">
                    <li>
                        <a href="#home">{{ __('home') }}</a>
                    </li>
                    <li>
                        <a href="#features">{{ __('features') }}</a>
                    </li>
                    <li>
                        <a href="#pricing">{{ __('pricing') }}</a>
                    </li>
                    <li>
                        <a href="#about-us">{{ __('about_us') }}</a>
                    </li>

                    @if (count($faqs))
                        <li>
                            <a href="#faq">{{ __('faqs') }}</a>
                        </li>    
                    @endif
                    <li>
                        <a href="#contact-us">{{ __('contact') }}</a>
                    </li>
                    @if (count($guidances))
                        <li>
                            <div class="dropdown">
                                <a class="btn btn-secondary dropdown-toggle" href="#" role="button"
                                    id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ __('guidance') }}
                                </a>                                
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                    @foreach ($guidances as $key => $guidance)
                                        <li><a class="dropdown-item" href="{{ $guidance->link }}">{{ $guidance->name }}</a></li>
                                        @if (count($guidances) > ($key + 1))
                                            <hr>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </li>
                    @endif
                    <li>
                        <div class="dropdown">
                            <a class="btn btn-secondary dropdown-toggle" href="#" role="button"
                                id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ __('language') }}
                            </a>

                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                @foreach ($languages as $key => $language)
                                    <li><a class="dropdown-item" href="{{ url('set-language') . '/' . $language->code }}">{{ $language->name }}</a></li>
                                    @if (count($languages) > ($key + 1))
                                        <hr>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </li>

                </ul>

                {{-- <div class="loginBtnsWrapper"> --}}
                    <button class="commonBtn redirect-login">{{ __('login') }}</button>
                    <button class="commonBtn" data-bs-toggle="modal" data-bs-dismiss="offcanvas" data-bs-target="#staticBackdrop">{{ __('start_trial') }}</button>
                {{-- </div> --}}
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
                                <span class="commonTitle">{{ $settings['system_name']  ?? 'eSchool SaaS' }}</span>
                                <span class="commonDesc">
                                    {{ $settings['tag_line'] }}
                                </span>
                                <span class="commonText">
                                    {{ $settings['hero_description'] }}</span>
                                <div class="d-flex">
                                    <button class="commonBtn" style="margin-right: 40px" data-bs-toggle="modal" data-bs-target="#staticBackdrop">{{ __('register_your_school') }}</button>                           
                                    @if ($isDemoSchool == 1)
                                        <a href="{{ $demoSchoolUrl ?? url('/') }}" target="_blank" class="commonBtn">{{ __('demo_school') }}</a>
                                    @endif   
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-6 heroImgWrapper">
                            <div class="heroImg">
                                <img src="{{ $settings['home_image'] ?? asset('assets/landing_page_images/heroImg.png') }}" alt="">
                                <div class="topRated card">
                                    <div>
                                        <img src="{{ $settings['hero_title_2_image'] ?? asset('assets/landing_page_images/user.png') }}" alt="">
                                    </div>
                                    @if(!empty($settings['hero_title_2']))
                                        <div>
                                            <span>{{ $settings['hero_title_2'] }}</span>
                                        </div>
                                    @endif
                                </div>
                                @if(!empty($settings['hero_title_1']))
                                    <div class="textWrapper">
                                        <span>{{ $settings['hero_title_1'] }}</span>
                                    </div>
                                @endif
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('registration_form')

    </section>
    <!-- heroSection ends here  -->

    <section class="features commonMT container" id="features">
        <div class="row">
            <div class="col-12">
                <div class="sectionTitle">
                    <span>{{ __('explore_our_top_features') }}</span>

                </div>
            </div>
            <div class="col-12">
                <div class="row cardWrapper">
                    @foreach ($features as $key => $feature)
                        @if ($key < 9)
                            <div class="col-sm-12 col-md-6 col-lg-4">
                                <div class="card">
                                    <div>
                                        <img src="{{ asset('assets/landing_page_images/features/') }}/{{ $feature->name }}.svg" alt="">
                                    </div>
                                    <div><span>{{ __($feature->name) }}</span></div>
                                </div>
                            </div>
                        @else
                            <div class="col-sm-12 col-md-6 col-lg-4 default-feature-list" style="display: none">
                                <div class="card">
                                    <div>
                                        <img src="{{ asset('assets/landing_page_images/features/') }}/{{ $feature->name }}.svg" alt="">
                                    </div>
                                    <div><span>{{ __($feature->name) }}</span></div>
                                </div>
                            </div>
                        @endif
                        
                    @endforeach
                    <div class="col-12">
                        <button class="commonBtn view-more-feature" value="1">{{ __('view_more_features') }}</button>
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
                    <span>{{ __('pricing_plans') }}</span>
                </div>
            </div>
            <div class="col-12 mt-4">
                <div class="row justify-content-center">
                    @foreach ($packages as $package)
                        @if ($package->is_trial == 1)
                            @continue
                        @endif
                        <div class="col-sm-12 col-md-6 col-lg-3 mb-4">
                            <div class="pricingCard {{ $package->highlight ? 'highlightedCard' : '' }}">
                                @if($package->highlight)
                                    <div class="popularBadge">{{ __('popular') }}</div>
                                @endif
                                <div>
                                    <div class="cardHeader">
                                        <h3 class="planName">{{ $package->name }}</h3>
                                        <p class="planTagline">{{ $package->tagline ?? __('school_management_system') }}</p>
                                    </div>
                                    <div class="cardPrice">
                                        <span class="currency">₹</span>
                                        <span class="priceAmount">{{ number_format($package->charges) }}</span>
                                        <span class="priceDuration">/ {{ $package->days }} {{ __('days') }}</span>
                                        @if($package->days > 30 && $package->charges > 0)
                                            <div class="monthlyEquiv">
                                                (₹{{ number_format(round($package->charges / ($package->days / 30))) }}/{{ __('month') }})
                                            </div>
                                        @endif
                                        <div class="trialBadgeContainer" style="margin-top: 10px;">
                                            <span class="trial-badge" style="display: inline-block; background-color: rgba(86, 204, 153, 0.15); color: var(--primary-color); padding: 5px 12px; border-radius: 20px; font-weight: 700; font-size: 12px;">
                                                <i class="fa-solid fa-gift"></i> 1 Month Free for ₹1
                                            </span>
                                        </div>
                                    </div>
                                    <div class="cardBody">
                                        <ul class="planFeatures">
                                            <li><i class="fa-solid fa-circle-check text-success"></i> {{ __('students') }}: {{ number_format($package->no_of_students) }}</li>
                                            <li><i class="fa-solid fa-circle-check text-success"></i> {{ __('staffs') }}: {{ number_format($package->no_of_staffs) }}</li>
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
                                    <button class="choosePlanBtn" data-package-id="{{ $package->id }}">
                                        {{ $package->is_trial ? __('start_trial') : __('choose_plan') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    <!-- pricing plans ends here -->

    {{-- @if ($settings['display_school_logos'] ?? '1')
        <section class="swiperSect container commonMT">
            <div class="row">
                <div class="col-12">
                    <div class="commonSlider">
                        <div class="slider-content owl-carousel">
                            <!-- Example slide -->
                            @foreach ($schoolSettings as $school)
                                @if (Storage::disk('public')->exists($school->getRawOriginal('data')) && $school->data)
                                    <div class="swiperDataWrapper">
                                        <div class="card">
                                            <img src="{{ $school->data }}" class="normalImg" alt="">
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                            <!-- Add more swiperDataWrapper elements here -->
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif --}}
    <!-- swiperSect ends here  -->
    {{-- @if ($settings['display_counters'] ?? '1')
        <section class="counterSect commonMT container">
            <div class="">
                <div class="row counterBG">
                    <div class="col-4 col-sm-4 col-md-4 col-lg-4">
                        <div class="card">
                            <div><span class="numb" data-target="{{ $counter['school'] }}">0</span><span>+</span></div>
                            <div><span class="text">{{ __('schools') }}</span></div>
                        </div>
                    </div>
                    <div class="col-4 col-sm-4 col-md-4 col-lg-4">
                        <div class="card">
                            <div><span class="numb" data-target="{{ $counter['teacher'] }}">0</span><span>+</span></div>
                            <div><span class="text">{{ __('teachers') }}</span></div>
                        </div>
                    </div>
                    <div class="col-4 col-sm-4 col-md-4 col-lg-4">
                        <div class="card">
                            <div><span class="numb" data-target="{{ $counter['student'] }}">0</span><span>+</span></div>
                            <div><span class="text">{{ __('students') }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif --}}
    


    @foreach ($featureSections as $key => $section)
        @if (($key + 1) % 2 != 0)

        <section class="left-section-{{ $section->id }} commonMT container">
            <div class="row">
                <div class="col-12">
                    <div class="sectionTitle">
                        <span class="greenText">{{ $section->title }}</span>
                        <span>
                            {{ $section->heading }}
                        </span>
    
                    </div>
                </div>
                <div class="col-12 tabsContainer " style="word-break: break-word;">
                    <div class="row">
                        <div class="col-lg-6 tabsMainWrapper" style="word-break: break-all !important;">
                            <div class="tabsWrapper" >
                                <div class="tabs">
                                    @foreach ($section->feature_section_list as $section_feature)
                                        <div class="tab tab-{{ $section_feature->id }}-{{ $key }}">
                                            <span>{{ $section_feature->feature }}</span>
                                            <span>
                                                {{ $section_feature->description }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
    
                        </div>
    
                        <div class="col-lg-6 contentWrapper">
                            <div class="content-container">
                                @foreach ($section->feature_section_list as $section_feature)
                                    <div class="content tab-{{ $section_feature->id }}-{{ $key }}">
                                        <img src="{{ $section_feature->image }}" alt="">
                                    </div>    
                                @endforeach
                            </div>
                        </div>
    
                    </div>
                </div>
            </div>
        </section>

        @else

        <section class="right-section-{{ $section->id }} right-feature-section commonMT">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="sectionTitle">
                            <span class="greenText">{{ $section->title }}</span>
                            <span>
                                {{ $section->heading }}
                            </span>
    
                        </div>
                    </div>
                    <div class="col-12 tabsContainer">
                        <div class="row reverseWrapper">
                            <div class="col-lg-6 contentWrapper">
                                <div class="content-container">
                                    @foreach ($section->feature_section_list as $section_feature)
                                        <div class="content tab-{{ $section_feature->id }}-{{ $key }}">
                                            <img src="{{ $section_feature->image }}" alt="">
                                        </div>    
                                    @endforeach
                                </div>
                            </div>
    
                            <div class="col-lg-6 tabsMainWrapper">
                                <div class="tabsWrapper">
                                    <div class="tabs">
                                        @foreach ($section->feature_section_list as $section_feature)
                                            <div class="tab tab-{{ $section_feature->id }}-{{ $key }}">
                                                <span>{{ $section_feature->feature }}</span>
                                                <span>
                                                    {{ $section_feature->description }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
    
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    
        </section>

        @endif
    @endforeach

    <section class="whyBest container commonMT" id="about-us">
        <div class="row">
            <div class="col-lg-6">
                <div class="whyBestTextWrapper">
                    <p>{{ $settings['about_us_title'] }}</p>
                    <p>{{ $settings['about_us_heading'] }}</p>
                </div>
                <p class="whyBestPara">
                    {{ $settings['about_us_description'] }}
                </p>

                <div class="listWrapper">
                    @foreach ($about_us_lists as $point)
                        <span>
                            <i class="fa-regular fa-circle-check"></i>
                            {{ $point }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="col-lg-6">
                <img src="{{ $settings['about_us_image'] ?? asset('assets/landing_page_images/whyBestImg.png') }}" alt="">
            </div>
        </div>
    </section>
    <!-- whyBest ends here  -->


    @if (count($faqs))
        <section class="faqs commonMT" id="faq">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="sectionTitle">
                            <span>{{ __('frequently_asked_questions') }}</span>

                        </div>
                    </div>

                    <div class="col-12">
                        <div class="accordion" id="accordionExample">
                            @foreach ($faqs as $faq)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapseOne-{{ $faq->id }}" aria-expanded="true" aria-controls="collapseOne-{{ $faq->id }}">
                                            <span>
                                                {{ $faq->title }}
                                            </span>
                                        </button>
                                    </h2>
                                    <div id="collapseOne-{{ $faq->id }}" class="accordion-collapse collapse"
                                        aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <span>
                                                {!! nl2br(e($faq->description)) !!}
                                            </span>
                                        </div>
                                    </div>
                                </div>  
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    <!-- faqs ends here  -->

    <section class="getInTouch commonMT" id="contact-us">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="sectionTitle">
                        <span class="greenText">{{ __('lets_get_in_touch') }}</span>
                        <span>{{ __('have_a_question_or_just_want_to_say_hi_Wed_love_to_hear_from_you') }}
                        </span>

                    </div>
                    <div class="col-12">
                        <div class="row wrapper">
                            <div class="col-lg-6">
                                <form action="{{ url('contact') }}" method="post" role="form" class="php-email-form mb-5 create-form-with-captcha">
                                    @csrf
                                    <div class="card">
                                        <div>
                                            <input type="text" required name="name" id="name" placeholder="{{ __('enter_your_name') }}">
                                        </div>
                                        <div>
                                            <input type="email" required name="email" id="email" placeholder="{{ __('enter_your_email') }}">
                                        </div>
                                        <div>
                                            <textarea name="message" required id="message" cols="30" rows="6"
                                                placeholder="{{ __('send_your_message') }}"></textarea>
                                        </div>
                                        @if (config('services.recaptcha.key') ?? '')
                                            <div>
                                                <div class="g-recaptcha" data-sitekey={{config('services.recaptcha.key')}}></div>
                                            </div>    
                                        @endif
                                        <div>
                                            <button class="commonBtn">{{ __('send') }}</button>
                                        </div>
                                        <div>
                                            <img src="{{ asset('assets/landing_page_images/GetInTouchDots.png') }}" class="sideImg dots" alt="">
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
                                        <span>{{ __('phone') }}</span>
                                        <span>{{ __('mobile') }} : {{ $settings['mobile'] ?? '' }}</span>
                                    </div>
                                </div>
                                <div class="infoWrapper">
                                    <div>
                                        <span class="icon"><i class="fa-solid fa-envelope-open-text"></i></span>
                                    </div>
                                    <div>
                                        <span>{{ __('email') }}</span>
                                        <span>{{ $settings['mail_send_from'] ?? 'example@gmail.com' }}</span>
                                    </div>
                                </div>
                                <div class="infoWrapper">
                                    <div>
                                        <span class="icon"><i class="fa-solid fa-location-dot"></i></span>
                                    </div>
                                    <div>
                                        <span>{{ __('location') }}</span>
                                        <span>{{ $settings['address'] ?? '' }}</span>
                                    </div>
                                </div>
                                <div>
                                    <img src="{{ asset('assets/landing_page_images/lineCircle.png') }}" class="lineCircle sideImg" alt="">
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
                <img src="{{ $settings['download_our_app_image'] ?? asset('assets/landing_page_images/ourApp.png') }}" class="ourAppImg" alt="">
            </div>
            <div class="col-lg-6 content">
                <div class="text">
                    <span class="title">{{ __('download_our_app_now') }}</span>
                    <span>
                        {{ $settings['download_our_app_description'] ?? '' }}
                    </span>
                </div>
                <div class="storeImgs">
                    <a href="{{ $settings['app_link'] ?? '' }}" target="_blank"> <img src="{{ asset('assets/landing_page_images/Google play.png') }}" alt=""> </a>
                    <a href="{{ $settings['ios_app_link'] ?? ''}}" target="_blank"> <img src="{{ asset('assets/landing_page_images/iOS app Store.png') }}" alt=""> </a>
                </div>
            </div>
        </div>
    </section>
</div>


@endsection

@section('script')
<script async src="https://www.google.com/recaptcha/api.js"></script>
    @foreach ($featureSections as $key => $section)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const tabs = document.querySelectorAll('.left-section-{{ $section->id }} .tab');
                const contents = document.querySelectorAll('.left-section-{{ $section->id }} .content');

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
                const tabs = document.querySelectorAll('.right-section-{{ $section->id }} .tab');
                const contents = document.querySelectorAll('.right-section-{{ $section->id }} .content');

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
    @endforeach
    <script>
        $('.redirect-login').click(function (e) { 
            e.preventDefault();
            window.location.href = "{{ url('login') }}"
        });
        
        $('.choosePlanBtn').click(function (e) { 
            e.preventDefault();
            var packageId = $(this).data('package-id');
            $('input[name="trial_package"]').val(packageId);
            $('#staticBackdrop').modal('show');
        });
    </script>
    <script>
        @if (Session::has('success'))
        $.toast({
            text: '{{ Session::get('success') }}',
            showHideTransition: 'slide',
            icon: 'success',
            loaderBg: '#f96868',
            position: 'top-right',
            bgColor: '#20CFB5'
        });
        @endif

        @if (Session::has('error'))
        $.toast({
            text: '{{ Session::get('error') }}',
            showHideTransition: 'slide',
            icon: 'error',
            loaderBg: '#f2a654',
            position: 'top-right',
            bgColor: '#FE7C96'
        });
        @endif
    </script>
@endsection