<?php $__env->startSection('title'); ?>
    <?php echo e(__('subject')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('manage') . ' ' . __('subject')); ?>

            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            <?php echo e(__('create') . ' ' . __('subject')); ?>

                        </h4>
                        <small class="text-danger"><?php echo e(__("Note : Subject Name,Code & Type should be Unique for Medium")); ?></small>
                        <form class="pt-3 subject-create-form" id="create-form" action="<?php echo e(route('subjects.store')); ?>" method="POST" novalidate="novalidate" enctype="multipart/form-data">
                            <input type="hidden" name="medium_id" value="1">

                            <div class="form-group">
                                <label><?php echo e(__('name')); ?> <span class="text-danger">*</span></label>
                                <input name="name" type="text" placeholder="<?php echo e(__('name')); ?>" class="form-control"/>
                            </div>

                            <div class="form-group">
                                <label><?php echo e(__('type')); ?> <span class="text-danger">*</span></label>
                                <div class="d-flex">
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input type="radio" class="form-check-input" name="type" id="theory" value="Theory"><?php echo e(__("Theory")); ?>

                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input type="radio" class="form-check-input" name="type" id="practical" value="Practical"><?php echo e(__("Practical")); ?>

                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label><?php echo e(__('subject_code')); ?></label>
                                <input name="code" type="text" placeholder="<?php echo e(__('subject_code')); ?>" class="form-control"/>
                            </div>

                            <div class="form-group">
                                <label><?php echo e(__('bg_color')); ?> <span class="text-danger">*</span></label>
                                <input name="bg_color" type="text" placeholder="<?php echo e(__('bg_color')); ?>" class="color-picker" autocomplete="off"/>
                            </div>

                            <div class="form-group">
                                <label><?php echo e(__('image')); ?></label>
                                <input type="file" name="image" class="file-upload-default" accept="image/png,image/jpeg,image/jpg,image/svg+xml,image/svg"/>
                                <div class="input-group col-xs-12">
                                    <input type="text" class="form-control file-upload-info" disabled="" placeholder="<?php echo e(__('image')); ?>"/>
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-theme" type="button"><?php echo e(__('upload')); ?></button>
                                    </span>
                                </div>
                            </div>
                            <input class="btn btn-theme float-right ml-3" id="create-btn" type="submit" value=<?php echo e(__('submit')); ?>>
                            <input class="btn btn-secondary float-right" type="reset" value=<?php echo e(__('reset')); ?>>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title"><?php echo e(__('list') . ' ' . __('subject')); ?></h4>
                            <div class="d-none">
                                <select name="filter_subject_id" id="filter_subject_id" class="form-control">
                                    <option value="">All</option>
                                    <?php $__currentLoopData = $mediums; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $medium): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($medium->id); ?>"><?php echo e($medium->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        <div class="col-12 text-right">
                            <b><a href="#" class="table-list-type active mr-2" data-id="0"><?php echo e(__('all')); ?></a></b> | <a href="#" class="ml-2 table-list-type" data-id="1"><?php echo e(__("Trashed")); ?></a>
                        </div>
                        <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                               data-url="<?php echo e(route('subjects.show',[1])); ?>" data-click-to-select="true"
                               data-side-pagination="server" data-pagination="true"
                               data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true"
                               data-show-refresh="true" data-fixed-columns="false" data-fixed-number="2"
                               data-fixed-right-number="1" data-trim-on-search="false" data-mobile-responsive="true"
                               data-sort-name="id" data-sort-order="desc" data-maintain-selected="true"
                               data-export-data-type='all' data-query-params="SubjectQueryParams"
                               data-toolbar="#toolbar" data-export-options='{ "fileName": "subject-list-<?= date('d-m-y') ?>" ,"ignoreColumn":["operate"]}' data-show-export="true" data-escape="true">
                            <thead>
                            <tr>
                                <th scope="col" data-field="id" data-sortable="true" data-visible="false"><?php echo e(__('id')); ?></th>
                                <th scope="col" data-field="no"><?php echo e(__('no.')); ?></th>
                                <th scope="col" data-field="name" data-sortable="true"><?php echo e(__('name')); ?></th>
                                <th scope="col" data-field="code" data-sortable="true"><?php echo e(__('subject_code')); ?></th>
                                <th scope="col" data-field="bg_color" data-formatter="bgColorFormatter"><?php echo e(__('bg_color')); ?></th>
                                <th scope="col" data-field="medium.name" data-visible="false"><?php echo e(__('medium')); ?></th>
                                <th scope="col" data-field="image" data-formatter="imageFormatter"><?php echo e(__('image')); ?></th>
                                <th scope="col" data-field="type" data-sortable="true"><?php echo e(__('type')); ?></th>
                                <th scope="col" data-field="created_at" data-formatter="dateTimeFormatter" data-sortable="true" data-visible="false"><?php echo e(__('created_at')); ?></th>
                                <th scope="col" data-field="updated_at" data-formatter="dateTimeFormatter" data-sortable="true" data-visible="false"><?php echo e(__('updated_at')); ?></th>
                                <th scope="col" data-field="operate" data-events="subjectEvents" data-escape="false"><?php echo e(__('action')); ?></th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel"><?php echo e(__('edit') . ' ' . __('subject')); ?></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form class="pt-3 subject-edit-form" id="edit-form" action="<?php echo e(url('subject')); ?>"
                              novalidate="novalidate">
                            <div class="modal-body">
                                <input type="hidden" name="edit_id" id="edit_id" value=""/>
                                <input type="hidden" name="medium_id" id="edit_medium_id" value="1">

                                <div class="form-group">
                                    <label><?php echo e(__('name')); ?> <span class="text-danger">*</span></label>
                                    <input name="name" id="edit_name" type="text" placeholder="<?php echo e(__('name')); ?>" class="form-control"/>
                                </div>

                                <div class="form-group">
                                    <label><?php echo e(__('type')); ?> <span class="text-danger">*</span></label>
                                    <div class="d-flex">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input type="radio" class="form-check-input edit" name="type" id="edit_theory" value="Theory">
                                                <?php echo e(__('Theory')); ?>

                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input type="radio" class="form-check-input edit" name="type" id="edit_practical" value="Practical">
                                                <?php echo e(__('Practical')); ?>

                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label><?php echo e(__('subject_code')); ?></label>
                                    <input name="code" id="edit_code" type="text" placeholder="<?php echo e(__('subject_code')); ?>" class="form-control"/>
                                </div>

                                <div class="form-group">
                                    <label><?php echo e(__('bg_color')); ?> <span class="text-danger">*</span></label>
                                    <input name="bg_color" id="edit_bg_color" type="text" placeholder="<?php echo e(__('bg_color')); ?>" class="color-picker" autocomplete="off"/>
                                </div>

                                <div class="form-group">
                                    <label><?php echo e(__('image')); ?></label>
                                    <input type="file" id="edit_image" name="image" class="file-upload-default" accept="image/png,image/jpeg,image/jpg,image/svg+xml,image/svg"/>
                                    <div class="input-group col-xs-12">
                                        <input type="text" id="edit_image" class="form-control" disabled="" value=""/>
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme" type="button"><?php echo e(__('upload')); ?></button>
                                        </span>
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
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/subjects/index.blade.php ENDPATH**/ ?>