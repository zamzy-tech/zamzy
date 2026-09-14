<?php $__env->startSection('title'); ?>
    Contact  Us
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="breadcrumb">
        <div class="container">
            <div class="contentWrapper">
                <span class="title"> Contact Us </span>
                <span>
                    <a href="<?php echo e(url('/')); ?>" class="home">Home</a>
                    <span><i class="fa-solid fa-caret-right"></i></span>
                    <span class="page">Contact Us</span>
                </span>
            </div>
        </div>
    </div>
    

    <section class="contactUs commonMT commonWaveSect">
        <div class="container">
            <div class="row">

                <div class="col-lg-6">

                    <div class="headlines">
                        <span>Get In Touch</span>
                        <span>Have Any Query?</span>
                    </div>

                    <div class="formWrapper">
                        <form action="<?php echo e(url('school/contact-us')); ?>" class="create-form-with-captcha" method="post">
                            <?php echo csrf_field(); ?>
                            <div class="row">
                                <input type="hidden" name="school_email" value="<?php echo e($schoolSettings['school_email'] ?? ''); ?>">
                                <div class="col-sm-12 col-md-6 col-lg-6">
                                    <div class="d-flex flex-column gap-1">
                                        <label for="First Name">Name</label>
                                        <input type="text" name="name" required placeholder="Enter First Name"></input>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6 col-lg-6">
                                    <div class="d-flex flex-column gap-1">
                                        <label for="email">Email</label>
                                        <input type="email" name="email" required placeholder="Enter Your Email"></input>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="d-flex flex-column gap-1">
                                        <label for="Message">Subject</label>
                                        <input name="subject" id="subject" required placeholder="Subject"></input>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="d-flex flex-column gap-1">
                                        <label for="Message">Message</label>
                                        <textarea name="message" id="message" required cols="30" rows="5"
                                            placeholder="Enter Message"></textarea>
                                    </div>
                                </div>

                                <?php if($schoolSettings['SCHOOL_RECAPTCHA_SITE_KEY'] ?? ''): ?>
                                    <div class="col-12">
                                        <div class="g-recaptcha mt-4" data-sitekey=<?php echo e($schoolSettings['SCHOOL_RECAPTCHA_SITE_KEY']); ?>></div>
                                    </div>    
                                <?php endif; ?>

                                <div class="col-4">
                                    <button type="submit" class="commonBtn">
                                        Send Message
                                    </button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="col-12 infoContainer">
                        <div class="col-12">
                            <div class="mapWrapper commonMT">
                                <div>
                                    <?php echo $schoolSettings['google_map_link'] ?? ''; ?>

                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script async src="https://www.google.com/recaptcha/api.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="<?php echo e(asset('/assets/js/custom/common.js')); ?>"></script>
    <script src="<?php echo e(asset('/assets/js/custom/custom.js')); ?>"></script>
    <script src="<?php echo e(asset('/assets/js/custom/validate.js')); ?>"></script>
    <script src="<?php echo e(asset('/assets/js/custom/function.js')); ?>"></script>
    <script src="<?php echo e(asset('/assets/js/sweetalert2.all.min.js')); ?>"></script>
    <script src="<?php echo e(asset('/assets/js/jquery.validate.min.js')); ?>"></script>
    <script src="<?php echo e(asset('/assets/jquery-toast-plugin/jquery.toast.min.js')); ?>"></script>

    <script src="<?php echo e(asset('assets/home_page/js/owl.carousel.min.js')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.school.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/school-website/contact.blade.php ENDPATH**/ ?>