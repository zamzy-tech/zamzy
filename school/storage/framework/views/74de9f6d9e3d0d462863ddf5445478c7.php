<?php $__env->startSection('title'); ?>
    <?php echo e(__('support')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('support')); ?>

            </h3>
        </div>
        <div class="row">
            <?php $__currentLoopData = $support_staffs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $staff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-4 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-row flex-wrap">
                                <img src="<?php echo e($staff->user->image); ?>" class="img-lg rounded" alt="profile image">
                                <div class="mx-3">
                                    <h6><?php echo e($staff->user->full_name); ?></h6>
                                    <p class="text-muted"><?php echo e($staff->user->email); ?></p>
                                    <p class="mt-2 text-info font-weight-bold"><?php echo e($staff->user->mobile); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if(empty($support_staffs) || count($support_staffs) == 0): ?>
                <div class="col-md-4 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-row flex-wrap">
                                <img src="<?php echo e($super_admin->image); ?>" class="img-lg rounded" alt="profile image">
                                <div class="mx-3">
                                    <h6><?php echo e($super_admin->full_name); ?></h6>
                                    <p class="text-muted"><?php echo e($super_admin->email); ?></p>
                                    <p class="mt-2 text-info font-weight-bold"><?php echo e($super_admin->mobile); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/staff/support.blade.php ENDPATH**/ ?>