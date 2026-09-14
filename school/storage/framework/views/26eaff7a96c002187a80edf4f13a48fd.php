<?php $__env->startSection('title'); ?>
    <?php echo e(__('Manage Exams')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('Manage Exams')); ?>

            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            <?php echo e(__('List Exams')); ?>

                        </h4>
                        <div class="row" id="toolbar">
                            <div class="form-group col-12 col-sm-12 col-md-3 col-lg-3">
                                <label for="filter_session_year_id" class="filter-menu"><?php echo e(__('Session Year')); ?></label>
                                <select name="filter_session_year_id" id="filter_session_year_id" class="form-control">
                                    <?php $__currentLoopData = $session_year_all; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sessionYear): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($sessionYear->id); ?>"
                                            <?php echo e($sessionYear->default == 1 ? 'selected' : ''); ?>><?php echo e($sessionYear->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="form-group col-12 col-sm-12 col-md-3 col-lg-3">
                                <label for="filter_medium_id" class="filter-menu"><?php echo e(__('medium')); ?></label>
                                <?php echo Form::select('medium_id', $mediums, null, ['class' => 'form-control', 'id' => 'filter_medium_id', 'placeholder' => __('all')]); ?>

                            </div>
                        </div>
                        <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                               data-url="<?php echo e(route('exam.view-marks-list', 1)); ?>" data-click-to-select="true"
                               data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                               data-search="true" data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                               data-fixed-columns="false" data-fixed-number="2" data-fixed-right-number="1"
                               data-trim-on-search="false" data-mobile-responsive="true" data-sort-name="id"
                               data-sort-order="desc" data-maintain-selected="true" data-export-data-type='all'
                               data-export-options='{ "fileName": "exam-list-<?= date(' d-m-y') ?>" ,"ignoreColumn":
                            ["operate"]}' data-show-export="true" data-detail-formatter="examListFormatter"
                               data-query-params="examQueryParams" data-escape="true">
                            <thead>
                            <tr>
                                <th scope="col" data-field="id" data-sortable="true" data-visible="false"><?php echo e(__('id')); ?></th>
                                <th scope="col" data-field="no"><?php echo e(__('no.')); ?></th>
                                <th scope="col" data-field="name" data-sortable="true"><?php echo e(__('name')); ?></th>
                                <th scope="col" data-field="subjectWiseStatus" data-formatter="marksSubmissionStatus"><?php echo e(__('marks_submission_status')); ?></th>
                                <th scope="col" data-field="created_at" data-formatter="dateTimeFormatter" data-sortable="true" data-visible="false"><?php echo e(__('created_at')); ?></th>
                                <th scope="col" data-field="updated_at" data-formatter="dateTimeFormatter" data-sortable="true" data-visible="false"><?php echo e(__('updated_at')); ?></th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            
            <div class="modal fade" id="editModal" tabindex="-1" data-success-function="formSuccessFunction"
                 role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">
                                <?php echo e(__('edit') . ' ' . __('exam')); ?>

                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form class="pt-3" id="edit-form" action="<?php echo e(url('exams')); ?>" novalidate="novalidate" method="POST">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="form-group col-12">
                                        <label><?php echo e(__('Exam Name')); ?> <span class="text-danger">*</span></label>
                                        <?php echo Form::text('name', '', [
                                            'id' => 'edit_name',
                                            'placeholder' => trans('Exam Name'),
                                            'class' => 'form-control',
                                            'required' => true,
                                        ]); ?>

                                    </div>
                                    <div class="form-group col-12">
                                        <label><?php echo e(__('Exam Description')); ?></label>
                                        <?php echo Form::textarea('description', '', [
                                            'id' => 'edit_description',
                                            'placeholder' => trans('Exam Description'),
                                            'class' => 'form-control',
                                            'rows' => '3',
                                        ]); ?>

                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo e(__('close')); ?></button>
                                <input class="btn btn-theme" type="submit" value=<?php echo e(__('submit')); ?> />
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            
            <div class="modal fade" id="marksStatus" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">
                                <?php echo e(__('marks_status')); ?>

                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="row">
                            <div class="form-group col-sm-12 col-md-12">
                                <table class="table table-border">
                                    <tr>
                                        <th><?php echo e(__('no')); ?></th>
                                        <th><?php echo e(__('subject')); ?></th>
                                        <th><?php echo e(__('date_time')); ?></th>
                                        <th><?php echo e(__('subject_teacher')); ?></th>
                                        <th><?php echo e(__('status')); ?></th>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script>
        const formSuccessFunction = () => {
            setTimeout(() => {
                $('#class-id').val('').trigger('change');
            }, 500);
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/exams/view_marks.blade.php ENDPATH**/ ?>