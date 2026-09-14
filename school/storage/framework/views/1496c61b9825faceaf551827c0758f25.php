<?php $__env->startSection('title'); ?>
    <?php echo e(__('Class')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('manage') . ' ' . __('Class')); ?>

            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <form class="pt-3 edit-class-subject-validate-form" data-success-function="formSuccessFunction" data-pre-submit-function="classValidation" id="edit-form" action="<?php echo e(route('class.update',[$id])); ?>" novalidate="novalidate">
                            <div class="modal-body">
                                <input type="hidden" name="medium_id" value="<?php echo e($class->medium_id); ?>">

                                <div class="form-group">
                                    <label for="edit_name"><?php echo e(__('name')); ?> <span class="text-danger">*</span></label>
                                    <input name="name" id="edit_name" type="text" placeholder="<?php echo e(__('name')); ?>" class="form-control" value="<?php echo e($class->name); ?>"/>
                                </div>
                                <div class="form-group d-none">
                                     <label for="shift_id"><?php echo e(__('Shift')); ?> <span class="text-info"> (<?php echo e(__("Optional")); ?>)</span></label>
                                     <select name="shift_id" id="shift_id" class="form-control form-control select2-dropdown select2-hidden-accessible" tabindex="-1" aria-hidden="true">
                                         <option value="">--- <?php echo e(__('Select Shift')); ?> ---</option>
                                         <?php $__currentLoopData = $shifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                             <option value="<?php echo e($shift->id); ?>" <?php echo e($shift->id==$class->shift_id?"selected":""); ?>><?php echo e($shift->name); ?></option>
                                         <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                     </select>
                                </div>
                                <div class="form-group">
                                    <label for="stream_id"><?php echo e(__('Stream')); ?> <span class="text-info"> (<?php echo e(__("Optional")); ?>)</span></label>
                                    <select name="stream_id" id="stream_id" class="form-control form-control select2-dropdown select2-hidden-accessible" tabindex="-1" aria-hidden="true">
                                        <option value="">--- <?php echo e(__("No Stream")); ?> ---</option>
                                        <?php $__currentLoopData = $streams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stream): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($stream->id); ?>" <?php echo e($stream->id==$class->stream_id?"selected":""); ?>><?php echo e($stream->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><?php echo e(__('section')); ?> <span class="text-danger">*</span></label>
                                    <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="form-check">
                                            <label class="form-check-label d-inline">
                                                <input type="checkbox" class="form-check-input edit" name="section_id[]" id="edit_section_id" value="<?php echo e($section->id); ?>" <?php echo e($class->sections->contains($section->id) ? "checked disabled" : ""); ?>><?php echo e($section->name); ?>

                                            </label>
                                            <?php if($class_section->contains($section->id)): ?>
                                                <a href="<?php echo e(route('class-section.destroy',[$class->sections->find($section->id)->pivot->id])); ?>" class="text-danger delete-class-section ml-2" style="cursor: pointer" title="<?php echo e(__("Delete")); ?>"><span class="fa fa-times-circle"></span></a>
                                                <a href="<?php echo e(route('class-section.edit',[$class->sections->find($section->id)->pivot->id])); ?>" class="text-primary ml-2" style="cursor: pointer" title="<?php echo e(__("Assign Class Teacher & Subject Teacher")); ?>"><span class="fa fa-pencil-square-o"></span></a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>

                                <hr>
                                <div class="form-group mt-4 d-none">
                                     <div class="form-check">
                                         <?php if(count($semesters)): ?>
                                             <label class="form-check-label d-inline">
                                                 <input type="checkbox" class="form-check-input include_semesters" name="include_semesters" value="<?php echo e($class->include_semesters); ?>" <?php echo e($class->include_semesters ? "checked" : ""); ?>><?php echo e(__('Include Semesters')); ?>

                                             </label>    
                                         <?php endif; ?>
                                         
                                         <br>
                                         <small class="text-danger">* <?php echo e(__("By Changing this Semester setting, your existing data related to this class will be Auto Deleted")); ?></small>
                                         <ol class="text-danger">
                                             <li><?php echo e(__("Class Subject")); ?></li>
                                             <li><?php echo e(__("timetable")); ?></li>
                                             <li><?php echo e(__("Lesson & Topic")); ?></li>
                                             <li><?php echo e(__("Exam & Marks")); ?></li>
                                             <li><?php echo e(__("announcement")); ?></li>
                                         </ol>
                                     </div>
                                </div>
                                <hr>

                                <input class="btn btn-theme float-right ml-3" id="create-btn" type="submit" value=<?php echo e(__('submit')); ?>>
                                <input class="btn btn-secondary float-right" type="reset" value=<?php echo e(__('reset')); ?>>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script>
        function formSuccessFunction() {
            setTimeout(() => {
                window.location.href = "<?php echo e(route('class.index')); ?>"
            }, 3000);
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/class/edit.blade.php ENDPATH**/ ?>