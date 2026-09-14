<?php $__env->startSection('title'); ?>
    <?php echo e(__('manage') . ' ' . __('exam') . ' ' . __('timetable')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('manage') . ' ' . __('exam') . ' ' . __('timetable')); ?>

            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-end">
                            <a class="btn btn-sm btn-theme" href="<?php echo e(route('exams.index')); ?>"><?php echo e(__('back')); ?></a>
                        </div>
                        <h4 class="page-title mb-4">
                            <?php echo e(__('create') . ' ' . __('exam') . ' ' . __('timetable')); ?>

                        </h4>
                        <div class="form-group">
                            <form class="edit-form" data-success-function="formSuccessFunction" action="<?php echo e(route('exam.timetable.update',$exam->id)); ?>" data-pre-submit-function="classValidation" method="POST">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label><?php echo e(__('exam')); ?> </label>
                                        <?php echo Form::hidden('semester_id', $exam->semester_id ?? null); ?>

                                        <?php echo Form::hidden('session_year_id', $exam->session_year_id); ?>

                                        <?php echo Form::text('', $exam->name, ['readonly' => true ,'class' => 'form-control']); ?>

                                    </div>
                                    <div class="form-group col-md-4">
                                        <label><?php echo e(__('Class')); ?> </label>
                                        <?php echo Form::text('', $exam->class->full_name, ['readonly' => true ,'class' => 'form-control']); ?>

                                    </div>
                                    <div class="form-group col-md-4">
                                        <label><?php echo e(__('Exam Result Submission Date')); ?> <span class="text-danger">*</span></label>
                                            <?php echo Form::text('last_result_submission_date', $last_result_submission_date, ['class' => 'timetable-date form-control', 'placeholder' => __('Exam Result Submission Date'), 'required',]); ?>

                                    </div>
                                </div>

                                <div class="exam-timetable-content">
                                    <div data-repeater-list="timetable">
                                        <div data-repeater-item>
                                            <div class="row">
                                                <?php echo Form::hidden('id', null, ['class' => 'timetable_id']); ?>

                                                <div class="form-group col-md-4">
                                                    <label for="subject_id"><?php echo e(__('subject')); ?> </label>
                                                    <select name="class_subject_id" id="subject_id" class="form-control exam-subjects-options subject" required>
                                                        <?php if(!empty($exam->class->all_subjects)): ?>
                                                            <option value="">-- <?php echo e(__('select')); ?> --</option>
                                                            <?php $__currentLoopData = $exam->class->all_subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <option value="<?php echo e($subject->class_subject_id); ?>"><?php echo e($subject->name_with_type); ?></option>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        <?php else: ?>
                                                            <option value="">-- <?php echo e(__('no_data_found')); ?> --</option>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label><?php echo e(__('total_marks')); ?> <span class="text-danger">*</span></label>
                                                    <?php echo Form::text('total_marks', null, ['class' => 'total-marks form-control', 'placeholder' => __('total_marks'), 'min' => 1, 'required' , "data-convert" => "number"]); ?>

                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label><?php echo e(__('passing_marks')); ?> <span class="text-danger">*</span></label>
                                                    <?php echo Form::text('passing_marks', null, ['class' => 'passing-marks form-control', 'placeholder' => __('passing_marks'), 'min' => 1, 'required', "data-convert" => "number"]); ?>

                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="form-group col-md-4">
                                                    <label><?php echo e(__('start_time')); ?> <span class="text-danger">*</span></label>
                                                    <?php echo Form::text('start_time', null, ['class' => 'start-time form-control', 'placeholder' => __('start_time'), 'autocomplete' => 'off', 'required' , "data-convert" => "time"]); ?>

                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label><?php echo e(__('end_time')); ?> <span class="text-danger">*</span></label>
                                                    <?php echo Form::text('end_time', null, ['class' => 'end-time form-control', 'placeholder' => __('end_time'), 'autocomplete' => 'off', 'required' , "data-convert" => "time"]); ?>

                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label><?php echo e(__('date')); ?> <span class="text-danger">*</span></label>
                                                    <?php echo Form::text('date', null, ['class' => 'timetable-date form-control', 'placeholder' => __('date'), 'required']); ?>

                                                </div>
                                                <div class="form-group col-md-1 pl-0 mt-4" data-repeater-delete>
                                                    <button type="button" <?php echo e($disabled); ?> class="btn btn-inverse-danger btn-icon remove-exam-timetable-content">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </div>
                                                <div class="col-12">
                                                    <hr>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row col-md-4 mt-3 mb-3">
                                        <button type="button" <?php echo e($disabled); ?> class="btn btn-success add-exam-timetable-content" title="Add new row" data-repeater-create>
                                            <?php echo e(__('Add New Data')); ?>

                                        </button>
                                    </div>
                                </div>
                                <input class="btn btn-theme float-right ml-3" id="create-btn" <?php echo e($disabled); ?> type="submit" value=<?php echo e(__('submit')); ?>>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script>
        <?php if(isset($exam->timetable) && $exam->timetable->isNotEmpty()): ?>
            examTimetableRepeater.setList([
                <?php $__currentLoopData = $exam->timetable; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $timetable): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                {
                    id: "<?php echo e($timetable->id); ?>",
                    class_subject_id: "<?php echo e($timetable->class_subject_id); ?>",
                    total_marks: "<?php echo e($timetable->total_marks); ?>",
                    passing_marks: "<?php echo e($timetable->passing_marks); ?>",
                    start_time: "<?php echo e($timetable->start_time); ?>",
                    end_time: "<?php echo e($timetable->end_time); ?>",
                    date: moment("<?php echo e($timetable->date); ?>", 'YYYY-MM-DD').format('DD-MM-YYYY')
                },
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            ])
        <?php else: ?>
            $('.add-exam-timetable-content').trigger('click')
        <?php endif; ?>

        $(document).ready(function () {
            <?php $__currentLoopData = $exam->timetable; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$timetable): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            $('#remove-exam-timetable-' + <?php echo e($key); ?>).attr('data-id', <?php echo e($timetable->id); ?>);
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            $('body').on('focus', ".timetable-date", function () {
                let minDate = moment("<?php echo e($currentSessionYear->start_date); ?>", 'YYYY-MM-DD').format('DD-MM-YYYY') ;
                let maxDate = moment("<?php echo e($currentSessionYear->end_date); ?>", 'YYYY-MM-DD').format('DD-MM-YYYY');

                $(this).datepicker({
                    enableOnReadonly: false,
                    format: "dd-mm-yyyy",
                    todayHighlight: true,
                    startDate: minDate,
                    endDate: maxDate,
                    rtl: isRTL()
                });
            });
        });

        function formSuccessFunction(response) {
            if (!response.error) {
                setTimeout(() => {
                    window.location.href = "<?php echo e(route('exams.index')); ?>"
                }, 1000);
            }
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/exams/timetable.blade.php ENDPATH**/ ?>