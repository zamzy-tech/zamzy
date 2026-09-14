<?php $__env->startSection('title'); ?>
    Refund - Cancellation
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="breadcrumb">
        <div class="container">
            <div class="contentWrapper">
                <span class="title"> Refund - Cancellation </span>
                <span>
                    <a href="<?php echo e(url('/')); ?>" class="home">Home</a>
                    <span><i class="fa-solid fa-caret-right"></i></span>
                    <span class="page">Refund - Cancellation</span>
                </span>
            </div>
        </div>
    </div>
    
    <section class="aboutUs commonMT commonWaveSect">
        <div class="container">
            <div class="row aboutWrapper">
                <div class="title text-center">
                    <h1>Refund - Cancellation</h1>
                </div>

                <div class="col-sm-12 col-md-12">
                    <div class="aboutContentWrapper">
                        <span class="commonDesc">
                            <?php echo htmlspecialchars_decode($schoolSettings['refund_cancellation'] ?? ''); ?>

                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.school.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/school-website/refund_cancellation.blade.php ENDPATH**/ ?>