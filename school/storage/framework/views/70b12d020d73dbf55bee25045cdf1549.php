<?php $__env->startSection('title'); ?>
    <?php echo e(__('Generate Hall Ticket')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('Generate Hall Ticket')); ?>

            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            <?php echo e(__('Search / Filter')); ?>

                        </h4>
                        
                        <!-- Search Form (GET) -->
                        <form method="GET" action="<?php echo e(route('exams.hall-ticket')); ?>" id="search-form">
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-5">
                                    <label for="class_section_id"><?php echo e(__('class_section')); ?> <span class="text-danger">*</span></label>
                                    <select name="class_section_id" id="class_section_id" required class="form-control">
                                        <option value="">-- <?php echo e(__('select_class_section')); ?> --</option>
                                        <?php $__currentLoopData = $classSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($cs->id); ?>" <?php echo e($selectedClassSectionId == $cs->id ? 'selected' : ''); ?>>
                                                <?php echo e($cs->full_name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-12 col-md-5">
                                    <label for="exam_id"><?php echo e(__('exams')); ?> <span class="text-danger">*</span></label>
                                    <select name="exam_id" id="exam_id" required class="form-control">
                                        <option value="">-- <?php echo e(__('select') . ' ' . __('exam')); ?> --</option>
                                        <?php $__currentLoopData = $exams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($exam->id); ?>" <?php echo e($selectedExamId == $exam->id ? 'selected' : ''); ?>>
                                                <?php echo e($exam->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-12 col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-theme w-100"><?php echo e(__('Search')); ?></button>
                                </div>
                            </div>
                        </form>

                        <?php if($selectedClassSectionId && $selectedExamId): ?>
                            <hr class="mt-4 mb-4">
                            
                            <!-- Generation Form (POST) -->
                            <form method="POST" action="<?php echo e(route('exams.hall-ticket.generate')); ?>" target="_blank" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="class_section_id" value="<?php echo e($selectedClassSectionId); ?>">
                                <input type="hidden" name="exam_id" value="<?php echo e($selectedExamId); ?>">

                                <h4 class="card-title mt-4">
                                    <?php echo e(__('Students List')); ?>

                                </h4>

                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th width="40px">
                                                    <div class="form-check m-0">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" id="select-all-students" checked>
                                                            <i class="input-helper"></i>
                                                        </label>
                                                    </div>
                                                </th>
                                                <th><?php echo e(__('no.')); ?></th>
                                                <th><?php echo e(__('admission_no')); ?></th>
                                                <th><?php echo e(__('roll_no')); ?></th>
                                                <th><?php echo e(__('name')); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td>
                                                        <div class="form-check">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" name="student_ids[]" value="<?php echo e($student->id); ?>" class="form-check-input student-checkbox" checked>
                                                                <i class="input-helper"></i>
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td><?php echo e($index + 1); ?></td>
                                                    <td><?php echo e($student->admission_no); ?></td>
                                                    <td><?php echo e($student->roll_number); ?></td>
                                                    <td><?php echo e($student->user->full_name); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center"><?php echo e(__('No students found for this class section')); ?></td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <?php if($students->isNotEmpty()): ?>
                                    <hr class="mt-4 mb-4">
                                    
                                    <h4 class="card-title">
                                        <i class="fa fa-pen-nib"></i> <?php echo e(__('Signature Configuration')); ?>

                                    </h4>
                                    <p class="text-muted" style="font-size: 13px;">Add signature slots to the hall ticket. You can add multiple signatures (e.g., Student, Class Teacher, Principal, HOD). Optionally upload a signature image for each.</p>

                                    <div id="signatures-container">
                                        
                                        <div class="signature-row row mb-3 align-items-end" data-index="0">
                                            <div class="form-group col-md-4 mb-0">
                                                <label><?php echo e(__('Label')); ?> <span class="text-danger">*</span></label>
                                                <input type="text" name="signatures[0][label]" class="form-control" value="Student's Signature" placeholder="e.g. Student's Signature" required>
                                            </div>
                                            <div class="form-group col-md-3 mb-0">
                                                <label><?php echo e(__('Alignment')); ?></label>
                                                <select name="signatures[0][align]" class="form-control">
                                                    <option value="left" selected>Left</option>
                                                    <option value="center">Center</option>
                                                    <option value="right">Right</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4 mb-0">
                                                <label><?php echo e(__('Signature Image')); ?> <small class="text-muted">(Optional)</small></label>
                                                <input type="file" name="signatures[0][image]" class="form-control" accept="image/*">
                                            </div>
                                            <div class="form-group col-md-1 mb-0 text-center">
                                                <button type="button" class="btn btn-sm btn-danger remove-signature-btn" title="Remove">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="signature-row row mb-3 align-items-end" data-index="1">
                                            <div class="form-group col-md-4 mb-0">
                                                <label><?php echo e(__('Label')); ?> <span class="text-danger">*</span></label>
                                                <input type="text" name="signatures[1][label]" class="form-control" value="Class Teacher" placeholder="e.g. Class Teacher" required>
                                            </div>
                                            <div class="form-group col-md-3 mb-0">
                                                <label><?php echo e(__('Alignment')); ?></label>
                                                <select name="signatures[1][align]" class="form-control">
                                                    <option value="left">Left</option>
                                                    <option value="center" selected>Center</option>
                                                    <option value="right">Right</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4 mb-0">
                                                <label><?php echo e(__('Signature Image')); ?> <small class="text-muted">(Optional)</small></label>
                                                <input type="file" name="signatures[1][image]" class="form-control" accept="image/*">
                                            </div>
                                            <div class="form-group col-md-1 mb-0 text-center">
                                                <button type="button" class="btn btn-sm btn-danger remove-signature-btn" title="Remove">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="signature-row row mb-3 align-items-end" data-index="2">
                                            <div class="form-group col-md-4 mb-0">
                                                <label><?php echo e(__('Label')); ?> <span class="text-danger">*</span></label>
                                                <input type="text" name="signatures[2][label]" class="form-control" value="Principal" placeholder="e.g. Principal" required>
                                            </div>
                                            <div class="form-group col-md-3 mb-0">
                                                <label><?php echo e(__('Alignment')); ?></label>
                                                <select name="signatures[2][align]" class="form-control">
                                                    <option value="left">Left</option>
                                                    <option value="center">Center</option>
                                                    <option value="right" selected>Right</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4 mb-0">
                                                <label><?php echo e(__('Signature Image')); ?> <small class="text-muted">(Optional)</small></label>
                                                <input type="file" name="signatures[2][image]" class="form-control" accept="image/*">
                                            </div>
                                            <div class="form-group col-md-1 mb-0 text-center">
                                                <button type="button" class="btn btn-sm btn-danger remove-signature-btn" title="Remove">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" id="add-signature-btn" class="btn btn-sm btn-outline-primary mt-2 mb-4">
                                        <i class="fa fa-plus"></i> <?php echo e(__('Add Signature Slot')); ?>

                                    </button>

                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-success float-right">
                                                <i class="fa fa-print"></i> <?php echo e(__('Generate & Print Hall Tickets')); ?>

                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </form>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script>
        $(document).ready(function() {
            var getExamsUrl = "<?php echo e(route('exams.hall-ticket.get-exams', ':id')); ?>";

            $('#class_section_id').change(function() {
                var classSectionId = $(this).val();
                var examSelect = $('#exam_id');
                
                // Clear existing options
                examSelect.html('<option value="">-- <?php echo e(__("select") . " " . __("exam")); ?> --</option>');
                
                if (classSectionId) {
                    var url = getExamsUrl.replace(':id', classSectionId);
                    $.get(url, function(data) {
                        $.each(data, function(index, exam) {
                            examSelect.append('<option value="' + exam.id + '">' + exam.name + '</option>');
                        });
                    });
                }
            });

            // Select All Checkbox logic
            $('#select-all-students').change(function() {
                $('.student-checkbox').prop('checked', $(this).prop('checked'));
            });

            // If any individual checkbox is unchecked, uncheck the select-all checkbox
            $('.student-checkbox').change(function() {
                if (!$(this).prop('checked')) {
                    $('#select-all-students').prop('checked', false);
                } else {
                    if ($('.student-checkbox:checked').length === $('.student-checkbox').length) {
                        $('#select-all-students').prop('checked', true);
                    }
                }
            });
            // Signature Repeater Logic
            var signatureIndex = 3; // Already have 3 default rows (0, 1, 2)

            $('#add-signature-btn').click(function() {
                var html = `
                    <div class="signature-row row mb-3 align-items-end" data-index="${signatureIndex}">
                        <div class="form-group col-md-4 mb-0">
                            <label><?php echo e(__('Label')); ?> <span class="text-danger">*</span></label>
                            <input type="text" name="signatures[${signatureIndex}][label]" class="form-control" placeholder="e.g. HOD, Invigilator" required>
                        </div>
                        <div class="form-group col-md-3 mb-0">
                            <label><?php echo e(__('Alignment')); ?></label>
                            <select name="signatures[${signatureIndex}][align]" class="form-control">
                                <option value="left">Left</option>
                                <option value="center" selected>Center</option>
                                <option value="right">Right</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4 mb-0">
                            <label><?php echo e(__('Signature Image')); ?> <small class="text-muted">(Optional)</small></label>
                            <input type="file" name="signatures[${signatureIndex}][image]" class="form-control" accept="image/*">
                        </div>
                        <div class="form-group col-md-1 mb-0 text-center">
                            <button type="button" class="btn btn-sm btn-danger remove-signature-btn" title="Remove">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                $('#signatures-container').append(html);
                signatureIndex++;
            });

            // Remove signature row
            $(document).on('click', '.remove-signature-btn', function() {
                var container = $('#signatures-container');
                if (container.find('.signature-row').length > 1) {
                    $(this).closest('.signature-row').remove();
                } else {
                    alert('You must have at least one signature slot.');
                }
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/exams/hall_ticket.blade.php ENDPATH**/ ?>