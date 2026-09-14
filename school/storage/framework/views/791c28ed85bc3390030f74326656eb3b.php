<nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo" href="<?php echo e(URL::to('/')); ?>">
            <img src="<?php echo e($schoolSettings['horizontal_logo'] ?? ''); ?>" alt="logo" data-custom-image="<?php echo e($systemSettings['horizontal_logo'] ?? asset('/assets/horizontal-logo2.svg')); ?>" class="custom-default-image">
        </a>
        <a class="navbar-brand brand-logo-mini" href="<?php echo e(URL::to('/')); ?>">
            <img src="<?php echo e($schoolSettings['vertical_logo'] ?? ''); ?>" alt="logo" data-custom-image="<?php echo e($systemSettings['vertical_logo'] ?? asset('/assets/vertical-logo.svg')); ?>">
        </a>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-stretch">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="fa fa-bars"></span>
        </button>

        <div class="align-items-stretch d-none d-md-block d-sm-block cache-clear">
            <a class="btn btn-sm btn-inverse-info align-self-center" href="<?php echo e(url('cache-flush')); ?>">
                <?php echo e(__('cache_clear')); ?>

            </a>
        </div>

        <?php if($schoolSettings['school_name'] ?? ''): ?>
            <div class="align-items-stretch d-none d-md-block d-sm-block cache-clear">
                <span class="ml-3"><?php echo e($schoolSettings['school_name'] ?? ''); ?></span>
            </div>
        <?php endif; ?>  
        <?php if(isset($systemSettings['email_verified']) && !$systemSettings['email_verified']): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('email-setting-create')): ?>
                <div class="mx-auto order-0">
                    <div class="alert alert-fill-danger my-2" role="alert">
                        <i class="fa fa-exclamation"></i>
                        <?php echo e(__('Email Configuration is not verified')); ?> <a href="<?php echo e(route('system-settings.email.index')); ?>" class="alert-link"><?php echo e(__('Click here to redirect to email configuration')); ?></a>.
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <ul class="navbar-nav navbar-nav-right">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('class-teacher')): ?>
                <li class="nav-item">
                    
                    
                </li>
            <?php endif; ?>

            <?php if(isset($sessionYear) && !Auth::user()->hasRole('Super Admin')): ?>
                <li class="d-none d-md-block d-sm-block nav-item">
                    <div class="text-dark"><?php echo e(__('session_years') . ' : '); ?> <span id="sessionYearNameHeader"><?php echo e($sessionYear->name); ?></span><span id="semesterNameHeader"><?php echo e((isset($semester) ? ', '.$semester->name : null)); ?></span></div>
                </li>
            <?php endif; ?>

            

            <li class="nav-item dropdown">
                <a class="nav-link count-indicator dropdown-toggle" id="messageDropdown" href="#" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-language"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="messageDropdown">
                    <?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a class="dropdown-item preview-item" href="<?php echo e(url('set-language') . '/' . $language->code); ?>">
                            <div class="preview-thumbnail">
                                
                            </div>
                            <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                                <h6 class="preview-subject ellipsis mb-1 font-weight-normal"><?php echo e($language->name); ?></h6>
                                
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </li>
            <?php if(Auth::user()->hasRole('School Admin')): ?>
                <li class="nav-item">
                    <a class="btn btn-sm btn-danger align-self-center mr-2" href="<?php echo e(url('subscriptions')); ?>">
                        <i class="fa fa-upload"></i> <?php echo e(__('Upgrade')); ?>

                    </a>
                </li>
            <?php endif; ?>
            <li class="nav-item nav-profile dropdown">
                <a class="nav-link dropdown-toggle" id="profileDropdown" href="#" data-toggle="dropdown" aria-expanded="true">
                    <div class="nav-profile-img">
                        <img src="<?php echo e(Auth::user()->image); ?>" alt="image">
                    </div>
                    <div class="nav-profile-text">
                        <p class="mb-1 text-black"><?php echo e(Auth::user()->first_name); ?></p>
                    </div>
                </a>
                <div class="dropdown-menu navbar-dropdown" aria-labelledby="profileDropdown">
                    
                        <a class="dropdown-item" href="<?php echo e(route('auth.profile.edit')); ?>"><i class="fa fa-user mr-2"></i><?php echo e(__('profile')); ?></a>
                        <div class="dropdown-divider"></div>
                    
                    <a class="dropdown-item" href="<?php echo e(route('auth.change-password.index')); ?>">
                        <i class="fa fa-refresh mr-2 text-success"></i><?php echo e(__('change_password')); ?></a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?php echo e(route('auth.logout')); ?>">
                        <i class="fa fa-sign-out mr-2 text-primary"></i> <?php echo e(__('signout')); ?>

                    </a>
                </div>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="fa fa-bars"></span>
        </button>
    </div>
</nav>
<?php /**PATH /home/shacartc/school.tehub.in/resources/views/layouts/header.blade.php ENDPATH**/ ?>