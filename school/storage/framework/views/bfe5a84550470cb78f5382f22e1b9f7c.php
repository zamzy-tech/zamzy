<?php $__env->startSection('title'); ?>
    <?php echo e(__('system_update')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title"><?php echo e(__('system_update')); ?>

                <small class="theme-color"><?php echo e(isset($system_version['data']) ? __('current_version').$system_version['data'] :''); ?></small>
            </h3>

        </div>

        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <form class="pt-3" action="<?php echo e(url('system-update')); ?>" id="system-update" method="POST" novalidate="novalidate">
                            <?php echo csrf_field(); ?>
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-12">
                                    <label><?php echo e(__('Purchase Code')); ?> <span class="text-danger">*</span></label>
                                    <input type="text" name="purchase_code" class="form-control"/>
                                </div>
                                <div class="form-group col-sm-12 col-md-12">
                                    <label><?php echo e(__('files')); ?> <span class="text-danger">* <small>(<?php echo e(__('Only Zip File is allowed')); ?>)</small></span></label>
                                    <input type="file" name="file" class="form-control" multiple/>
                                    <small class="theme-color"><?php echo e(__('Your Current Version is')); ?> <?php echo e(isset($system_version['data']) ? $system_version['data'] :''); ?>, <?php echo e(__('Please update nearest version here if available')); ?></small>
                                </div>
                            </div>
                            <input class="btn btn-theme float-right" type="submit" value=<?php echo e(__('submit')); ?>>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/system-update/index.blade.php ENDPATH**/ ?>