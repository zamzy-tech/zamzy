<?php $__env->startSection('title'); ?>
    <?php echo e(__('gallery')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('manage') . ' ' . __('gallery')); ?>

            </h3>
        </div>

        <div class="row">
            <?php if(Auth::user()->can('gallery-create')): ?>
                <div class="col-lg-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">
                                <?php echo e(__('create') . ' ' . __('gallery')); ?>

                            </h4>
                            <form class="create-form pt-3" id="create-form" action="<?php echo e(route('gallery.store')); ?>"
                                  method="POST" novalidate="novalidate" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>
                                <div class="row">
                                    <div class="form-group col-sm-12 col-md-6">
                                        <label><?php echo e(__('title')); ?> <span class="text-danger">*</span></label>
                                        <?php echo Form::text('title', null, ['required', 'placeholder' => __('title'), 'class' => 'form-control']); ?>

                                    </div>
                                    <div class="form-group col-sm-6 col-md-6">
                                        <label><?php echo e(__('description')); ?></label>
                                        <?php echo Form::textarea('description', null, [
                                            'rows' => '2',
                                            'placeholder' => __('description'),
                                            'class' => 'form-control',
                                        ]); ?>

                                    </div>
                                    <div class="form-group col-sm-6 col-md-6">
                                        <label><?php echo e(__('thumbnail')); ?> <span class="text-danger">*</span> <span class="text-small text-info">( jpg,svg,jpeg,png )</span></label>
                                        <input type="file" required name="thumbnail" id="thumbnail"
                                               class="file-upload-default" accept="image/*"/>
                                        <div class="input-group col-xs-12">
                                            <input type="text" class="form-control file-upload-info" disabled=""
                                                   placeholder="<?php echo e(__('thumbnail')); ?>" required aria-label=""/>
                                            <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-theme"
                                                        type="button"><?php echo e(__('upload')); ?></button>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-6 col-md-6">
                                        <label><?php echo e(__('images')); ?> <span class="text-small text-info"> (<?php echo e(__('upload_multiple_images')); ?>)</span></label>
                                        <input type="file" multiple name="images[]" id="uploadInput"
                                               class="file-upload-default" accept="image/*"/>
                                        <div class="input-group col-xs-12">
                                            <input type="text" class="form-control file-upload-info" disabled=""
                                                   placeholder="<?php echo e(__('images')); ?>" required aria-label=""/>
                                            <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-theme"
                                                        type="button"><?php echo e(__('upload')); ?></button>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="form-group col-sm-12 col-md-6">
                                        <label for=""><?php echo e(__('youtube_links')); ?> <span class="text-small text-info">(<?php echo e(__('please_use_commas_or_press_enter_to_add_multiple_links')); ?>)</span></label>
                                        <input name="youtube_links" id="tags" class="form-control" value=""/>
                                    </div>

                                    <div class="form-group col-sm-12 col-md-3">
                                        <label for="session_year_id"><?php echo e(__('session_year')); ?></label>
                                        <select name="session_year_id" class="form-control">
                                            <?php $__currentLoopData = $sessionYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sessionYear): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($sessionYear->id); ?>"
                                                    <?php echo e($sessionYear->default == 1 ? 'selected' : ''); ?>>
                                                    <?php echo e($sessionYear->name); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>
                                <input class="btn btn-theme float-right ml-3" id="create-btn" type="submit" value=<?php echo e(__('submit')); ?>>
                                <input class="btn btn-secondary float-right" type="reset" value=<?php echo e(__('reset')); ?>>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if(Auth::user()->can('gallery-list')): ?>
                <div class="col-lg-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">
                                <?php echo e(__('list') . ' ' . __('gallery')); ?>

                            </h4>
                            <div class="row" id="toolbar">
                                <div class="form-group col-12 col-sm-12 col-md-3 col-lg-3">
                                    <label for="filter_session_year_id"
                                           class="filter-menu"><?php echo e(__('session_year')); ?></label>
                                    <select name="filter_session_year_id" id="filter_session_year_id" class="form-control">
                                        <?php $__currentLoopData = $sessionYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sessionYear): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($sessionYear->id); ?>"
                                                <?php echo e($sessionYear->default == 1 ? 'selected' : ''); ?>>
                                                <?php echo e($sessionYear->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                                           data-url="<?php echo e(route('gallery.show', 1)); ?>" data-click-to-select="true"
                                           data-side-pagination="server" data-pagination="true"
                                           data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                           data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                                           data-fixed-columns="false" data-fixed-number="2" data-fixed-right-number="1"
                                           data-trim-on-search="false" data-mobile-responsive="true" data-sort-name="id" data-show-export="true"
                                           data-sort-order="desc" data-maintain-selected="true" data-export-data-type='all'
                                           data-export-options='{ "fileName": "gallery-list-<?= date('d-m-y') ?>
                                               ","ignoreColumn": ["operate"]}'
                                           data-query-params="galleryQueryParams">
                                        <thead>
                                        <tr>
                                            <th scope="col" data-field="id" data-sortable="true" data-visible="false"> <?php echo e(__('id')); ?> </th>
                                            <th scope="col" data-width="80" data-field="no"> <?php echo e(__('no.')); ?> </th>
                                            <th scope="col" data-width="200" data-formatter="imageFormatter" data-field="thumbnail"><?php echo e(__('thumbnail')); ?> </th>
                                            <th scope="col" data-field="title"><?php echo e(__('title')); ?> </th>
                                            <th scope="col" data-field="description"><?php echo e(__('description')); ?></th>
                                            <?php if(Auth::user()->can('gallery-edit') || Auth::user()->can('gallery-delete')): ?>
                                                <th data-width="200" data-events="galleryEvents" data-width="150" scope="col" data-field="operate"><?php echo e(__('action')); ?></th>
                                            <?php endif; ?>
                                        </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        
        <div class="modal fade" id="editModal" data-backdrop="static" tabindex="-1" role="dialog"
             aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"> <?php echo e(__('edit') . ' ' . __('gallery')); ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"><i class="fa fa-close"></i></span>
                        </button>
                    </div>
                    <form id="formdata" class="edit-form" action="<?php echo e(url('gallery')); ?>" novalidate="novalidate">
                        <?php echo csrf_field(); ?>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="id">
                            <div class="row form-group">
                                <div class="col-sm-12 col-md-12">
                                    <label><?php echo e(__('title')); ?> <span class="text-danger">*</span></label>
                                    <?php echo Form::text('title', null, [
                                        'required',
                                        'placeholder' => __('title'),
                                        'class' => 'form-control',
                                        'id' => 'edit-title',
                                    ]); ?>

                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="col-sm-12 col-md-12">
                                    <label><?php echo e(__('description')); ?></label>
                                    <?php echo Form::textarea('description', null, [
                                        'placeholder' => __('description'),
                                        'class' => 'form-control',
                                        'id' => 'edit-description',
                                    ]); ?>

                                </div>
                            </div>

                            <div class="row form-group">
                                <div class="col-sm-12 col-md-12">
                                    <label for="session_year_id"><?php echo e(__('session_year')); ?></label>
                                    <select name="session_year_id" id="edit_session_year_id" class="form-control">
                                        <?php $__currentLoopData = $sessionYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sessionYear): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($sessionYear->id); ?>">
                                                <?php echo e($sessionYear->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>


                            <div class="row form-group">
                                <div class="col-sm-12 col-md-12">
                                    <label><?php echo e(__('thumbnail')); ?> </label>
                                    <input type="file" name="thumbnail" id="thumbnail"
                                           class="file-upload-default"/>
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" disabled=""
                                               placeholder="<?php echo e(__('thumbnail')); ?>" aria-label=""/>
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme"
                                                    type="button"><?php echo e(__('upload')); ?></button>
                                        </span>
                                    </div>
                                    <img src="" id="edit-thumbnail" class="img-lg mt-2" alt="">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo e(__('Cancel')); ?></button>
                            <input class="btn btn-theme" type="submit" value=<?php echo e(__('submit')); ?>>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script>
        const uploadInput = document.getElementById('uploadInput');

        // Event listener to handle file selection
        uploadInput.addEventListener('change', function () {
            // Update file counter with the number of selected files
            $(this).parent().find('.form-control').val(this.files.length + (this.files.length === 1 ? ' file selected' : ' files selected'));
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/gallery/index.blade.php ENDPATH**/ ?>