<?php $__env->startSection('title'); ?>
    <?php echo e(__('certificate')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('manage_certificate') . ' ' . __('template')); ?>

            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            <?php echo e(__('create_certificate') . ' ' . __('template')); ?>

                        </h4>
                        <form class="pt-3 subject-create-form" id="create-form" action="<?php echo e(url('certificate-template')); ?>" method="POST" novalidate="novalidate" enctype="multipart/form-data">
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-4">
                                    <label><?php echo e(__('name')); ?> <span class="text-danger">*</span></label>
                                    <input name="name" type="text" placeholder="<?php echo e(__('name')); ?>" class="form-control"/>
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label><?php echo e(__('type')); ?> <span class="text-danger">*</span></label>
                                    <div class="col-12 d-flex row">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input type="radio" checked class="form-check-input certificate_type" name="type" value="Student" required="required">
                                                <?php echo e(__('student')); ?>

                                            </label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input type="radio" class="form-check-input certificate_type" name="type" value="Staff" required="required">
                                                <?php echo e(__('staff')); ?>

                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label><?php echo e(__('page_layout')); ?> <span class="text-danger">*</span></label>
                                    <?php echo Form::select('page_layout', ['A4 Landscape' => 'A4 Landscape','A4 Portrait' => 'A4 Portrait','Custom' => 'Custom'], 'A4 Landscape', ['class' => 'form-control page_layout']); ?>

                                </div>

                                <div class="form-group col-sm-12 col-md-2">
                                    <label><?php echo e(__('height')); ?> <span class="text-small text-info">(<?php echo e(__('mm')); ?>)</span> <span class="text-danger">*</span></label>
                                    <input name="height" min="50" type="number" required placeholder="<?php echo e(__('height')); ?>" class="form-control height"/>
                                </div>

                                <div class="form-group col-sm-12 col-md-2">
                                    <label><?php echo e(__('width')); ?> <span class="text-small text-info">(<?php echo e(__('mm')); ?>)</span> <span class="text-danger">*</span></label>
                                    <input name="width" min="50" type="number" required placeholder="<?php echo e(__('width')); ?>" class="form-control width"/>
                                </div>

                                <div class="form-group col-sm-12 col-md-4">
                                    <label><?php echo e(__('user_image_shape')); ?> <span class="text-danger">*</span></label>
                                    <?php echo Form::select('user_image_shape', ['Round' => 'Round','Square' => 'Square'], 'Round', ['class' => 'form-control']); ?>

                                </div>

                                <div class="form-group col-sm-12 col-md-4">
                                    <label><?php echo e(__('image_size')); ?> <span class="text-small text-info">(<?php echo e(__('px')); ?>)</span><span class="text-danger">*</span></label>
                                    <input name="image_size" min="50" required type="number" placeholder="<?php echo e(__('image_size')); ?>" class="form-control"/>
                                </div>

                                <div class="form-group col-sm-12 col-md-4">
                                    <label><?php echo e(__('background_image')); ?> </label>
                                    <input type="file" name="background_image" id="thumbnail" class="file-upload-default" accept="image/*"/>
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" disabled=""
                                                placeholder="<?php echo e(__('thumbnail')); ?>" required aria-label=""/>
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme"
                                                    type="button"><?php echo e(__('upload')); ?></button>
                                        </span>
                                    </div>
                                </div>

                                <div class="form-group col-sm-12 col-md-12">
                                    <label><?php echo e(__('description')); ?> <span class="text-danger">*</span></label>
                                    <textarea id="tinymce_message" name="description" id="description" required placeholder="<?php echo e(__('description')); ?>"></textarea>
                                </div>

                                <div class="form-group col-sm-12 col-md-12">
                                    <?php echo $__env->make('certificate.tags', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
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
                        <h4 class="card-title"><?php echo e(__('list') . ' ' . __('certificate')); ?> <?php echo e(__('template')); ?></h4>
                        <div id="toolbar">
                            
                        </div>
                        
                        <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table" data-url="<?php echo e(route('certificate-template.show',[1])); ?>" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true" data-show-refresh="true" data-fixed-columns="false" data-trim-on-search="false" data-mobile-responsive="true" data-sort-name="id" data-sort-order="desc" data-maintain-selected="true" data-export-data-type='all' data-query-params="certificateTemplateQueryParams" data-toolbar="#toolbar" data-export-options='{ "fileName": "subject-list-<?= date('d-m-y') ?>" ,"ignoreColumn":["operate"]}' data-show-export="true" data-escape="true">
                            <thead>
                            <tr>
                                <th scope="col" data-field="id" data-sortable="true" data-visible="false"><?php echo e(__('id')); ?></th>
                                <th scope="col" data-field="no"><?php echo e(__('no.')); ?></th>
                                <th scope="col" data-field="name"><?php echo e(__('name')); ?></th>
                                <th scope="col" data-field="type"><?php echo e(__('type')); ?></th>
                                <th scope="col" data-field="page_layout"><?php echo e(__('page_layout')); ?></th>
                                <th scope="col" data-field="background_image" data-formatter="imageFormatter"><?php echo e(__('background_image')); ?></th>
                                <th scope="col" data-field="style" data-formatter="layoutFormatter"><?php echo e(__('layout')); ?></th>
                                <th scope="col" data-field="operate" data-events="certificateTemplateEvents" data-escape="false"><?php echo e(__('action')); ?></th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script>
        window.onload = setTimeout(() => {
            $('.page_layout').trigger('change');
            $('.certificate_type').trigger('change');
        }, 500);
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/certificate/template.blade.php ENDPATH**/ ?>