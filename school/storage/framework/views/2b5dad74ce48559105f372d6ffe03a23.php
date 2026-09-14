<?php $__env->startSection('title'); ?>
    <?php echo e(__('add_bulk_data')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('Manage Students')); ?>

            </h3>
        </div>
        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <form class="pt-3" id="create-form" enctype="multipart/form-data"
                            action="<?php echo e(route('students.store-bulk-data')); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-4">
                                    <label for="session_year_id"><?php echo e(__('Session Year')); ?> <span class="text-danger">*</span></label>
                                    <select name="session_year_id" id="session_year_id" class="form-control select2">
                                        <?php $__currentLoopData = $sessionYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sessionYear): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($sessionYear->id); ?>"
                                                <?php echo e($sessionYear->default == 1 ? 'selected' : ''); ?>><?php echo e($sessionYear->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label for="class_section"><?php echo e(__('class_section')); ?> <span class="text-danger">*</span></label>
                                    <select name="class_section_id" id="class_section" class="form-control select2">
                                        <option value=""><?php echo e(__('select') . ' ' . __('Class') . ' ' . __('section')); ?>

                                        </option>
                                        <?php $__currentLoopData = $class_section; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($section->id); ?>"><?php echo e($section->full_name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-12 col-md-4">
                                    <label for="file-upload-default"><?php echo e(__('file_upload')); ?> <span class="text-danger">*</span></label>
                                    <input type="file" name="file" class="file-upload-default" />
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" id="file-upload-default" disabled="" placeholder="<?php echo e(__('file_upload')); ?>" required="required" />
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme" type="button"><?php echo e(__('upload')); ?></button>
                                        </span>
                                    </div>
                                    <div class="form-check w-fit-content">
                                        <label class="form-check-label user-select-none">
                                            <input type="checkbox" class="form-check-input" name="is_send_notification" id="send_notification">
                                            <?php echo e(__('send_notification')); ?>

                                        </label>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12 col-xs-12">
                                    <input class="btn btn-theme submit_bulk_file float-right" type="submit" value="<?php echo e(__('submit')); ?>"
                                        name="submit" id="submit_bulk_file">
                                </div>
                            </div>
                        </form>
                        <hr>
                        <div class="row form-group col-sm-12 col-md-4 mt-5">
                            <a class="btn btn-theme form-control" href="<?php echo e(route('student.bulk-data-sample')); ?>" download>
                                <strong><?php echo e(__('download_dummy_file')); ?></strong>
                            </a>
                        </div>
                        <div class="row col-sm-12 col-xs-12">
                            <span style="font-size: 14px">
                                <b><?php echo e(__('note')); ?> :- </b><?php echo e(__('First download dummy file, fill in details, and upload it (.csv, .xls, or .xlsx file)')); ?>.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/students/add_bulk_data.blade.php ENDPATH**/ ?>