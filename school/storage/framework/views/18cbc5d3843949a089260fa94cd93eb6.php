<?php $__env->startSection('title'); ?>
    <?php echo e(__('Class Subject')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('manage') . ' ' . __('Class Subject')); ?>

            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="col-md-12">
                            <div class="form-group">
                                <b><?php echo e(__("Class")); ?></b> : <?php echo e($class->full_name); ?>

                            </div>
                            <div class="form-group">
                                <b><?php echo e(__("Semester Included")); ?></b> : <?php echo e($class->include_semesters ? trans("Yes") : trans("No")); ?>

                            </div>
                        </div>
                        <form class="pt-3 edit-class-subject-validate-form" data-success-function="formSuccessFunction" data-pre-submit-function="classValidation" id="edit-form" action="<?php echo e(route('class.subject.update',[$id])); ?>" novalidate="novalidate">
                            <div class="modal-body">
                                <div class="form-group">
                                    <h4 title="<?php echo e(__('Core Subjects are the Compulsory Subject')); ?>." class="mb-3"><?php echo e(__('Core Subjects')); ?><span class="fa fa-info-circle pl-2"></span></h4>
                                    <div class="core-subject-repeater">
                                        <div data-repeater-list="core_subject">
                                            <div class="row" data-repeater-item>
                                                <?php if($class->include_semesters): ?>
                                                    <div class="col-5 semester-div">
                                                        <div class="form-group">
                                                            <label for="semester_id" class="d-none"></label>
                                                            <select name="semester_id" id="semester_id" class="form-control semesters" required>
                                                                <option value="" hidden="">-- <?php echo e(__("Select Semester")); ?> --</option>
                                                                <?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <option value="<?php echo e($semester->id); ?>"><?php echo e($semester->name); ?></option>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </select>

                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <input type="hidden" name="class_subject_id" class="class_subject_id"/>
                                                        <label for="core_subject_id" class="d-none"></label>
                                                        <select name="id" id="core_subject_id" class="form-control subject" required="required">
                                                            <option value=""><?php echo e(__('Select Subject')); ?></option>
                                                            <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <option value="<?php echo e($subject->id); ?>"><?php echo e($subject->name); ?> - <?php echo e(__($subject->type)); ?></option>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-1 pl-0">
                                                    <button data-repeater-delete type="button" class="btn btn-inverse-danger btn-icon" title="<?php echo e(__('Remove Core Subject')); ?>">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="form-group pl-0 mt-4">
                                                <button type="button" class="col-md-3 btn btn-inverse-success" data-repeater-create><?php echo e(__('Core Subjects')); ?> <i class="fa fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>

                                    <h4 class="mb-4" title="<?php echo e(__('Elective Subjects are the subjects where student have the choice to select the subject from the given subjects')); ?>.">
                                        <?php echo e(__('elective_subject')); ?> <span class="fa fa-info-circle pl-2"></span>
                                    </h4>

                                    <div class="row">
                                        <div class="elective-subject-group-repeater col-12 col-sm-12 col-md-12">
                                            <div data-repeater-list="elective_subject_group">
                                                <div data-repeater-item class="elective-subject-group">
                                                    <input type="hidden" name="id" class="class_subject_group_id"/>
                                                    <div class="align-items-center d-flex mb-2">
                                                        <h5 class="mb-0 group-no"><?php echo e(__('Group')); ?></h5>
                                                        <button data-repeater-delete type="button" class="btn p-0 ml-1" title="Delete Subject Group">
                                                            <span class="fa fa-2x fa-times-circle text-danger"></span>
                                                        </button>
                                                    </div>
                                                    <div class="col-3 semester-div p-0">
                                                        <div class="form-group">
                                                            <label for="semester_id" class="d-none"></label>
                                                            <?php if($class->include_semesters): ?>
                                                                <select name="semester_id" id="semester_id" class="form-control semesters" required>
                                                                    <option value="" hidden="">-- <?php echo e(__("Select Semester")); ?> --</option>
                                                                    <?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <option value="<?php echo e($semester->id); ?>"><?php echo e($semester->name); ?></option>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                </select>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="form-group elective-subject-repeater">
                                                        <div data-repeater-list="subject" class="mr-3 row">
                                                            <div data-repeater-item class="elective-subject col-md-4 col-sm-12 col-12">
                                                                <div class="row">
                                                                    <div class="col-md-10 col-sm-10 col-10 mb-5">
                                                                        <input type="hidden" name="class_subject_id" class="class_subject_id"/>
                                                                        <label for="elective_id" class="d-none"></label>
                                                                        <select name="id" id="elective_id" class="form-control subject" required="required">
                                                                            <option value=""><?php echo e(__('Select Subject')); ?></option>
                                                                            <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                <option value="<?php echo e($subject->id); ?>"><?php echo e($subject->name); ?> - <?php echo e(__($subject->type)); ?></option>
                                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                        </select>

                                                                        <button data-repeater-delete type="button" class='btn p-0 remove-elective-subject' title="Delete Subject ">
                                                                            <span class='fa fa-times-circle text-danger'></span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="col-md-2 col-sm-2 col-2 mt-3 or-div">
                                                                        <span class='mt-3'><?php echo e(__('or')); ?></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-1 col-sm-12 col-12 pl-0 mt-3">
                                                            <button data-repeater-create type="button" class="btn btn-inverse-success btn-icon add-new-elective-subject" title="Add New Elective Subject"><i class="fa fa-plus"></i></button>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-3">
                                                            <label for="total_selectable_subjects"><?php echo e(__('total_selectable_subjects')); ?><span class="text-danger">*</span></label>
                                                            <input name="total_selectable_subjects" type="text" id="total_selectable_subjects" placeholder="<?php echo e(__('total_selectable_subjects')); ?>" class="form-control total_selectable_subjects" min="1" max="0" data-convert="number" required/>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                </div>
                                            </div>
                                            <div class="col-md-12 col-sm-12 col-12 pl-0">
                                                <div class="form-group mt-4 col-md-4 col-sm-12 col-12 pl-0">
                                                    <button data-repeater-create type="button" class="col-md-12 btn btn-inverse-success"><?php echo e(__('elective_subject')); ?> <i class="fa fa-plus"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input class="btn btn-theme float-right ml-3" id="create-btn" type="submit" value=<?php echo e(__('submit')); ?>>
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
        $(document).ready(function () {
            <?php if($class->core_subjects): ?>
            coreSubject.setList([
                    <?php $__currentLoopData = $class->core_subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$coreSubject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                {
                    id: "<?php echo e($coreSubject->id); ?>",
                    class_subject_id: "<?php echo e($coreSubject->class_subject_id); ?>",
                    semester_id: "<?php echo e($coreSubject->pivot->semester_id); ?>"
                },
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            ]);
            <?php endif; ?>

            <?php if($class->elective_subject_groups): ?>
            electiveSubjectGroupRepeater.setList([
                    <?php $__currentLoopData = $class->elective_subject_groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                {
                    subject: [
                            <?php $__currentLoopData = $group->subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subjects): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        {
                            id: "<?php echo e($subjects->id); ?>",
                            class_subject_id: "<?php echo e($subjects->class_subject_id); ?>",

                        },
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    ],
                    id: "<?php echo e($group->id); ?>",
                    total_selectable_subjects: "<?php echo e($group->total_selectable_subjects); ?>",
                    semester_id: "<?php echo e($group->semester_id); ?>"
                },
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            ])
            <?php endif; ?>

            /* This line will auto add Group id to subjects */
            $('.semesters').trigger('change');
        });

        function formSuccessFunction() {
            window.location.href = "<?php echo e(route('class.subject.index')); ?>"
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/class-subject/edit.blade.php ENDPATH**/ ?>