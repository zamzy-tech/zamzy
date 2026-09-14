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
                        <h4 class="card-title">
                            <?php echo e(__('list') . ' ' . __('Class Subject')); ?>

                        </h4>
                        <div class="d-block">
                            <div class="">
                                <div class="col-12 text-right d-flex justify-content-end text-right align-items-end">
                                    <b><a href="#" class="table-list-type active mr-2" data-id="0"><?php echo e(__('all')); ?></a></b> | <a href="#" class="ml-2 table-list-type" data-id="1"><?php echo e(__("Trashed")); ?></a>
                                </div>
                            </div>
                        </div>
                        <div id="toolbar">
                            <label for="filter_medium_id" class="filter-menu"><?php echo e(__("Medium")); ?></label>
                            <select name="medium_id" id="filter_medium_id" class="form-control">
                                <option value=""><?php echo e(__('all')); ?></option>
                                <?php $__currentLoopData = $mediums; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $medium): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($medium->id); ?>"><?php echo e($medium->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                               data-url="<?php echo e(route('class.subject.list')); ?>" data-click-to-select="true" data-side-pagination="server"
                               data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                               data-show-columns="true" data-show-refresh="true" data-fixed-columns="false"
                               data-fixed-number="2" data-fixed-right-number="1" data-trim-on-search="false"
                               data-mobile-responsive="true" data-sort-name="id" data-toolbar="#toolbar" data-sort-order="desc"
                               data-maintain-selected="true" data-export-data-type='all'
                               data-export-options='{ "fileName": "class-subject-list-<?= date('d-m-y') ?>" ,"ignoreColumn":["operate"]}'
                               data-show-export="true" data-detail-filter="classSubjectsDetailFilter" data-detail-view="true" data-detail-formatter="classSubjectsDetailFormatter" data-query-params="classQueryParams"
                               data-escape="true">
                            <thead>
                            <tr>
                                <th scope="col" data-field="id" data-visible="false"><?php echo e(__('id')); ?></th>
                                <th scope="col" data-field="no"><?php echo e(__('no.')); ?></th>
                                <th scope="col" data-field="full_name"><?php echo e(__('name')); ?></th>
                                <th scope="col" data-field="shift.name"><?php echo e(__('Shift')); ?></th>
                                <th scope="col" data-field="include_semesters" data-formatter="yesAndNoStatusFormatter"><?php echo e(__('Semester')); ?></th>
                                <th scope="col" data-field="medium.name"><?php echo e(__('medium')); ?></th>
                                <th scope="col" data-field="section_names"><?php echo e(__('section')); ?></th>
                                <th scope="col" data-field="core_subjects" data-formatter="coreSubjectFormatter"><?php echo e(__('Core Subjects')); ?></th>
                                <th scope="col" data-field="elective_subject_groups" data-formatter="electiveSubjectFormatter"><?php echo e(__('elective_subject')); ?></th>
                                <th scope="col" data-field="created_at" data-formatter="dateTimeFormatter" data-visible="false"><?php echo e(__('created_at')); ?></th>
                                <th scope="col" data-field="updated_at" data-formatter="dateTimeFormatter" data-visible="false"><?php echo e(__('updated_at')); ?></th>
                                <th scope="col" data-field="operate" data-escape="false"><?php echo e(__('action')); ?></th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/class-subject/index.blade.php ENDPATH**/ ?>