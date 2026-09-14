<?php $__env->startSection('title'); ?>
    <?php echo e(__('manage') . ' ' . __('form-fields')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('manage') . ' ' . __('form-fields')); ?>

            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <?php echo e(__('create') . ' ' . __('form-fields')); ?>

                        </h4>
                        <form class="pt-3 mt-6 create-form" method="POST" data-success-function="formSuccessFunction" action="<?php echo e(route('school-custom.store')); ?>">
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-5">
                                    <label><?php echo e(__('name')); ?> <span class="text-danger">*</span></label>
                                    <input type="text" name="name" onkeypress="validateInput(event)" placeholder="<?php echo e(__('name')); ?>" class="form-control" required>
                                </div>
                                <div class="form-group col-sm-12 col-md-5">
                                    <label><?php echo e(__('type')); ?> <span class="text-danger">*</span></label>
                                    <select name="type" id="type-field" class="form-control type-field">
                                        <option value="text" selected><?php echo e(__('Text')); ?></option>
                                        <option value="number"><?php echo e(__('Numeric')); ?></option>
                                        <option value="dropdown"><?php echo e(__('Dropdown')); ?></option>
                                        <option value="radio"><?php echo e(__('Radio Button')); ?></option>
                                        <option value="checkbox"><?php echo e(__('Checkbox')); ?></option>
                                        <option value="textarea"><?php echo e(__('TextArea')); ?></option>
                                        <option value="file"><?php echo e(__('File Upload')); ?></option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12 col-md-2">
                                    <label><?php echo e(__('required')); ?> </label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input required-field" name="required" id="customSwitch1">
                                        <label class="custom-control-label" for="customSwitch1"></label>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="default-values-section" style="display: none">
                                <div class="mt-4" data-repeater-list="default_data">
                                    <div class="col-md-5 pl-0 mb-4">
                                        <button type="button" class="btn btn-success add-new-option" data-repeater-create title="Add new row">
                                            <span><i class="fa fa-plus"></i> <?php echo e(__('add_new_option')); ?></span>
                                        </button>
                                    </div>
                                    <div class="row option-section" data-repeater-item>
                                        <div class="form-group col-md-5">
                                            <label><?php echo e(__('option')); ?> - <span class="option-number">1</span> <span class="text-danger">*</span></label>
                                            <input type="text" name="option" placeholder="<?php echo e(__('text')); ?>" class="form-control" required>
                                        </div>
                                        <div class="form-group col-md-1 pl-0 mt-4">
                                            <button data-repeater-delete type="button" class="btn btn-icon btn-inverse-danger remove-default-option" title="<?php echo e(__('remove').' '.__('option')); ?>" disabled>
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            

                            <input class="btn btn-theme float-right ml-3" id="create-btn" type="submit" value=<?php echo e(__('submit')); ?>>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            <?php echo e(__('list') . ' ' . __('form-fields')); ?>

                        </h4>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary" id="preview-fields" data-toggle="modal" data-target="#previewFieldModal"><?php echo e(__('preview').' '.__('form-fields')); ?></button>
                        </div>
                        <div class="col-12 mt-4 text-right">
                            <b><a href="#" class="table-list-type active mr-2" data-id="0"><?php echo e(__('all')); ?></a></b> | <a href="#" class="ml-2 table-list-type" data-id="1"><?php echo e(__("Trashed")); ?></a>
                        </div>
                        <table aria-describedby="mydesc" class='table reorder-table-row' id='table_list'
                               data-toggle="table" data-url="<?php echo e(route('school-custom-field.list', 1)); ?>"
                               data-click-to-select="true" data-side-pagination="server"
                               data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                               data-search="true" data-toolbar="#toolbar" data-show-columns="true"
                               data-show-refresh="true" data-trim-on-search="false"
                               data-mobile-responsive="true" data-use-row-attr-func="true"
                               data-reorderable-rows="true" data-maintain-selected="true"
                               data-export-data-type='all' data-export-options='{ "fileName": "<?php echo e(__('form-fields')); ?>-<?= date(' d-m-y') ?>" ,"ignoreColumn":["operate"]}'
                               data-show-export="true" data-query-params="queryParams" data-escape="true">
                            <thead>
                            <tr>
                                <th scope="col" data-field="id" data-sortable="true" data-visible="false"><?php echo e(__('id')); ?></th>
                                <th scope="col" data-field="no"><?php echo e(__('no.')); ?></th>
                                <th scope="col" data-field="name"><?php echo e(__('name')); ?></th>
                                <th scope="col" data-field="type"><?php echo e(__('type')); ?></th>
                                <th scope="col" data-field="is_required" data-formatter="yesAndNoStatusFormatter"><?php echo e(__('is').' '.__('required')); ?></th>
                                <th scope="col" data-field="default_values" data-formatter="formFieldDefaultValuesFormatter"><?php echo e(__('Default Values')); ?></th>
                                <th scope="col" data-field="rank" data-sortable="false"><?php echo e(__('rank')); ?></th>
                                <th scope="col" data-field="operate" data-sortable="false" data-events="formFieldsEvents" data-escape="false"><?php echo e(__('action')); ?></th>
                            </tr>
                            </thead>
                        </table>
                        <span class="d-block mb-4 mt-2 text-danger small"><?php echo e(__('draggable_rows_notes')); ?></span>
                        <div class="mt-1 d-none d-md-block">
                            <button id="change-order-school-form-field" class="btn btn-theme"><?php echo e(__('update_rank')); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    
    <div class="modal fade" id="previewFieldModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><?php echo e(__('preview').' '.__('form-fields')); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="fa fa-close"></i></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row preview-content">
                        <?php if(!empty($formFields)): ?>
    
                            
                            <?php $__currentLoopData = $formFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="form-group col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                    
                                    <label><?php echo e($data->name); ?> <?php if($data->is_required): ?>
                                            <span class="text-danger">*</span>
                                        <?php endif; ?></label>
    
                                    
                                    <?php if($data->type == 'text'): ?>
                                        <input type="text" name="<?php echo e($data->name); ?>" class="form-control" placeholder="<?php echo e($data->name); ?>" <?php if($data->is_required): ?> required <?php endif; ?>>
    
                                        
                                    <?php elseif($data->type == 'number'): ?>
                                        <input type="number" min="0" name="<?php echo e($data->name); ?>" class="form-control" placeholder="<?php echo e($data->name); ?>" <?php if($data->is_required): ?> required <?php endif; ?>>
    
                                        
                                    <?php elseif($data->type == 'dropdown'): ?>
                                        <select class="form-control" <?php if($data->is_required): ?> required <?php endif; ?>>
                                            <option value="" disabled selected>Select <?php echo e($data->name); ?></option>
                                            <?php if(!empty($data->default_values)): ?>
                                                <?php $__currentLoopData = $data->default_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($value); ?>"><?php echo e($value); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php endif; ?>
                                        </select>
    
                                        
                                    <?php elseif($data->type == 'radio'): ?>
                                        <div class="d-flex flex-wrap">
                                            <?php if(!empty($data->default_values)): ?>
                                                <?php $__currentLoopData = $data->default_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="radio" name="<?php echo e($data->name); ?>" value="<?php echo e($value); ?>">
                                                            <?php echo e($value); ?>

                                                        </label>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php endif; ?>
                                        </div>
    
                                        
                                    <?php elseif($data->type == 'checkbox'): ?>
                                        <div class="d-flex flex-wrap">
                                            <?php $__currentLoopData = $data->default_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="form-check mr-3">
                                                    <label class="form-check-label">
                                                        <input type="checkbox" class="form-check-input chkclass" value="<?php echo e($value); ?>"><?php echo e($value); ?>

                                                    </label>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
    
                                        
                                    <?php elseif($data->type == 'textarea'): ?>
                                        <textarea placeholder="<?php echo e($data->name); ?>" class="form-control" <?php if($data->is_required): ?> required <?php endif; ?>></textarea>
    
                                        
                                    <?php elseif($data->type == 'file'): ?>
                                        <div class="input-group">
                                            <input type="file" name="admin_image" class="file-upload-default" <?php if($data->is_required): ?> required <?php endif; ?> />
                                            <input type="text" class="form-control file-upload-info" disabled placeholder="<?php echo e(__('image')); ?>" required/>
                                            <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-theme" type="button"><?php echo e(__('upload')); ?></button>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        <?php echo e(__('edit') . ' ' . __('form-fields')); ?>

                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="pt-3 edit-form edit-common-validation-rules" action="<?php echo e(url('school-custom-fields')); ?>" novalidate="novalidate">
                    <input type="hidden" name="edit_id" id="edit-id" value=""/>
                    <div class="modal-body">
                        <div class="form-group col-sm-12">
                            <label><?php echo e(__('name')); ?> <span class="text-danger">*</span></label>
                            <input type="text" name="name" onkeypress="validateInput(event)" id="edit-name" placeholder="<?php echo e(__('name')); ?>" class="form-control" required>
                        </div>
                        <div class="form-group col-sm-12">
                            <label><?php echo e(__('type')); ?> <span class="text-danger">*</span></label>
                            <select id="edit-type-select" class="form-control edit-type-field">
                                <option value="text" selected>Text</option>
                                <option value="number">Numeric</option>
                                <option value="dropdown">Dropdown</option>
                                <option value="radio">Radio Button</option>
                                <option value="checkbox">Checkbox</option>
                                <option value="textarea">TextArea</option>
                                <option value="file">File Upload</option>
                            </select>

                            <?php echo Form::hidden('type', "", ['id' => 'edit-type-field-value']); ?>

                        </div>
                        <div class="form-group col-sm-12 col-md-2">
                            <label><?php echo e(__('required')); ?> </label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" name="edit_required" id="customSwitch2">
                                <label class="custom-control-label" id="edit-required-toggle" for="customSwitch2"></label>
                            </div>
                        </div>

                        
                        <div class="edit-default-values-section ml-4" style="display: none">
                            <div class="mt-4" data-repeater-list="edit_default_data">
                                <div class="pl-0 mb-4">
                                    <button type="button" class="btn btn-success add-new-edit-option" data-repeater-create title="<?php echo e(__('add_new_option')); ?>">
                                        <span><i class="fa fa-plus"></i> <?php echo e(__('add_new_option')); ?></span>
                                    </button>
                                </div>
                                <div class="row edit-option-section" data-repeater-item>
                                    <div class="form-group col-md-10">
                                        <label><?php echo e(__('option')); ?> - <span class="edit-option-number">1</span> <span class="text-danger">*</span></label>
                                        <input type="text" name="option" placeholder="<?php echo e(__('text')); ?>" class="form-control" required>
                                    </div>
                                    <div class="form-group col-md-1 pl-0 mt-4">
                                        <button data-repeater-delete type="button" class="btn btn-icon btn-inverse-danger remove-edit-default-option" title="<?php echo e(__('remove').' '.__('option')); ?>" disabled>
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                </div>
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
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script>
        function formSuccessFunction() {
            $('#type-field').val('text').trigger('change');
            $('[data-repeater-item]').slice(2).remove();
        }

        function validateInput(event) {
            // Get the ASCII code of the key that was pressed
            var charCode = event.which || event.keyCode;

            // Allow letters (A-Z, a-z) and space (ASCII code 32)
            if (!(charCode >= 65 && charCode <= 90) && !(charCode >= 97 && charCode <= 122) && !(charCode === 32)) {
                event.preventDefault(); // Prevent invalid character input
            }
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/form-fields/school-field.blade.php ENDPATH**/ ?>