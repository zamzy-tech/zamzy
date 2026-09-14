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
                        <h4 class="card-title">
                            <?php echo e(__('create') . ' ' . __('Class')); ?>

                        </h4>
                        <form class="pt-3 class-create-form" id="create-form" action="<?php echo e(route('class.store')); ?>" method="POST" data-pre-submit-function="classValidation" data-success-function="successFunction" novalidate="novalidate">
                            <input type="hidden" name="medium_id" value="1">

                            <div class="form-group">
                                <label for="name"><?php echo e(__('name')); ?> <span class="text-danger">*</span></label>
                                <input name="name" id="name" type="text" placeholder="<?php echo e(__('name')); ?>" class="form-control" required="required"/>
                            </div>
                            <div class="form-group d-none">
                                <label for="shift_id"><?php echo e(__('Shift')); ?> <span class="text-info"> (<?php echo e(__("Optional")); ?>)</span></label>
                                <select name="shift_id" id="shift_id" class="form-control select2-dropdown select2-hidden-accessible" tabindex="-1" aria-hidden="true">
                                    <option value="">--- <?php echo e(__('Select Shift')); ?> ---</option>
                                    <?php $__currentLoopData = $shifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($shift->id); ?>"><?php echo e($shift->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="stream_id"><?php echo e(__('Stream')); ?> <span class="text-info"> (<?php echo e(__("Optional")); ?>)</span></label>
                                <select name="stream_id[]" id="stream_id" class="stream_id form-control select2-dropdown select2-hidden-accessible" tabindex="-1" aria-hidden="true" data-placeholder=" --- <?php echo e(__("Select Stream")); ?> --- " multiple>
                                    <?php $__currentLoopData = $streams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stream): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($stream->id); ?>"><?php echo e($stream->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="form-group" id="default-section-div">
                                <label class="mb-0 mt-3"><?php echo e(__('section')); ?> <span class="text-danger">*</span></label>
                                <div class="d-flex">
                                    <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="form-check w-fit-content ml-3">
                                            <label class="form-check-label ml-4">
                                                <input type="checkbox" class="form-check-input" name="section_id[0][]" value="<?php echo e($section->id); ?>" required="required"><?php echo e($section->name); ?>

                                            </label>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                <?php if(count($semesters) > 0): ?>
                                    <div class="form-check w-fit-content ml-3 mt-0 d-none">
                                        <label class="form-check-label d-inline">
                                            <input type="checkbox" class="form-check-input include_semesters[0][]" name="include_semesters[0]" value="1"><?php echo e(__('Include Semesters')); ?>

                                        </label>
                                    </div>
                                <?php endif; ?>

                            </div>
                            <div class="form-group" id="stream-wise-section-div" style="display: none;">
                                <?php $__currentLoopData = $streams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stream): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div id="<?php echo e(preg_replace('/\s+/', '-', trim($stream->name))); ?>-section-div" class="stream-divs" style="display: none;">
                                        <label class="mb-0 mt-3"><?php echo e(__('section').' ('.$stream->name.')'); ?> <span class="text-danger">*</span></label>
                                        <div class="d-flex">
                                            <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="form-check w-fit-content ml-3">
                                                    <label class="form-check-label ml-4">
                                                        <input type="checkbox" class="form-check-input" name="section_id[<?php echo e($stream->id); ?>][]" value="<?php echo e($section->id); ?>" required="required"><?php echo e($section->name); ?>

                                                    </label>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                        <?php if(count($semesters) > 0): ?>
                                            <div class="form-check w-fit-content ml-3 mt-0 d-none">
                                                <label class="form-check-label d-inline">
                                                    <input type="checkbox" class="form-check-input include_semesters" name="include_semesters[<?php echo e($stream->id); ?>]" value="1"><?php echo e(__('Include Semesters')); ?>

                                                </label>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <hr>
                            <div class="row mt-4">
                                <div class="col-md-12 col-sm-12 col-12">
                                    <input class="btn btn-theme float-right ml-3" id="create-btn" type="submit" value=<?php echo e(__('submit')); ?>>
                                    <input class="btn btn-secondary float-right" type="reset" value=<?php echo e(__('reset')); ?>>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            <?php echo e(__('list') . ' ' . __('Class')); ?>

                        </h4>
                        <div class="d-block">
                            <div class="">

                                <div class="col-12 text-right d-flex justify-content-end text-right align-items-end">
                                    <b><a href="#" class="table-list-type active mr-2" data-id="0"><?php echo e(__('all')); ?></a></b> | <a href="#" class="ml-2 table-list-type" data-id="1"><?php echo e(__("Trashed")); ?></a>
                                </div>
                            </div>
                        </div>
                        <div id="toolbar">
                            <div class="d-none">
                                <label for="filter_medium_id" class="filter-menu"><?php echo e(__("Medium")); ?></label>
                                <select name="medium_id" id="filter_medium_id" class="form-control">
                                    <option value=""><?php echo e(__('all')); ?></option>
                                    <?php $__currentLoopData = $mediums; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $medium): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($medium->id); ?>"><?php echo e($medium->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                               data-url="<?php echo e(route('class.show',[1])); ?>" data-click-to-select="true" data-side-pagination="server"
                               data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                               data-show-columns="true" data-show-refresh="true" data-fixed-columns="false"
                               data-fixed-number="2" data-fixed-right-number="1" data-trim-on-search="false"
                               data-mobile-responsive="true" data-sort-name="id" data-toolbar="#toolbar" data-sort-order="desc"
                               data-maintain-selected="true" data-export-data-type='all'
                               data-export-options='{ "fileName": "class-list-<?= date('d-m-y') ?>" ,"ignoreColumn":["operate"]}'
                               data-show-export="true" data-query-params="classQueryParams"
                               data-escape="true">
                            <thead>
                            <tr>
                                <th scope="col" data-field="id" data-sortable="false" data-visible="false"><?php echo e(__('id')); ?></th>
                                <th scope="col" data-field="no"><?php echo e(__('no.')); ?></th>
                                <th scope="col" data-field="full_name" data-sortable="false"><?php echo e(__('name')); ?></th>
                                <th scope="col" data-field="shift.name" data-visible="false"><?php echo e(__('Shift')); ?></th>
                                <th scope="col" data-field="include_semesters" data-formatter="yesAndNoStatusFormatter" data-visible="false"><?php echo e(__('Semester')); ?></th>
                                <th scope="col" data-field="section_names"><?php echo e(__('section')); ?></th>
                                <th scope="col" data-field="created_at" data-formatter="dateTimeFormatter" data-sortable="false" data-visible="false"><?php echo e(__('created_at')); ?></th>
                                <th scope="col" data-field="updated_at" data-formatter="dateTimeFormatter" data-sortable="false" data-visible="false"><?php echo e(__('updated_at')); ?></th>
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
<?php $__env->startSection('js'); ?>
    <script>
        function successFunction() {
            $('#default-section-div').show();
            $('#stream-wise-section-div').hide();
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/class/index.blade.php ENDPATH**/ ?>