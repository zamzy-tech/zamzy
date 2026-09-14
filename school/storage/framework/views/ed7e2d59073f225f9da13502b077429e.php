<?php $__env->startSection('title'); ?>
    <?php echo e(__('schools')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('manage') . ' ' . __('schools')); ?>

                <a href="javascript:void(0)" data-toggle="modal" data-target="#schoolHelpModal">
                    <i class="fa fa-question-circle help-icon ml-2" data-toggle="tooltip" title="<?php echo e(__('Click for help')); ?>"></i>
                </a>
            </h3>
        </div>

        <?php if (isset($component)) { $__componentOriginalabebc1f665b84803c4d4150c50cce525 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalabebc1f665b84803c4d4150c50cce525 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.help-modal','data' => ['id' => 'schoolHelpModal','role' => 'superadmin','module' => 'Manage_Schools']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('help-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'schoolHelpModal','role' => 'superadmin','module' => 'Manage_Schools']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalabebc1f665b84803c4d4150c50cce525)): ?>
<?php $attributes = $__attributesOriginalabebc1f665b84803c4d4150c50cce525; ?>
<?php unset($__attributesOriginalabebc1f665b84803c4d4150c50cce525); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalabebc1f665b84803c4d4150c50cce525)): ?>
<?php $component = $__componentOriginalabebc1f665b84803c4d4150c50cce525; ?>
<?php unset($__componentOriginalabebc1f665b84803c4d4150c50cce525); ?>
<?php endif; ?>

        <div class="row">
            
            <?php if($demoSchool == 0): ?>
                <div class="col-lg-12 grid-margin stretch-card">
                    <div class="card bg-light">
                        <div class="row card-body">
                            <div class="col-12">
                                <div class="alert alert-info" role="alert">
                                    <strong> <?php echo e(__('important_note')); ?> :</strong>
                                     <?php echo __('This is a demo school. Please do not use this school for real registration. This action can only be performed once. :click_here to create the demo school.',['click_here' => '<a href="javascript:void(0);" class="font-weight-bold" id="createDemoSchool">'. __('click_here').'</a>'] ); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <form class="create-form school-registration-form school-registration-validate" enctype="multipart/form-data" action="<?php echo e(route('schools.store')); ?>" method="POST" novalidate="novalidate">
                            <?php echo csrf_field(); ?>
                            <div class="bg-light p-4 mt-4 mb-4">
                                <h4 class="card-title mb-4">
                                    <?php echo e(__('create') . ' ' . __('schools')); ?>

                                </h4>
                                <div class="row">
                                    <div class="form-group col-sm-12 col-md-6">
                                        <label for="school_name"><?php echo e(__('name')); ?> <span class="text-danger">*</span></label>
                                        <input type="text" name="school_name" id="school_name" placeholder="<?php echo e(__('schools')); ?>" class="form-control" required>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-6">
                                        <label><?php echo e(__('logo')); ?> <span class="text-danger">*</span></label>
                                        <input type="file" required name="school_image" id="school_image" class="file-upload-default" accept="image/png, image/jpg, image/jpeg, image/svg+xml"/>
                                        <div class="input-group col-xs-12">
                                            <input type="text" class="form-control file-upload-info" disabled="" placeholder="<?php echo e(__('logo')); ?>" required aria-label=""/>
                                            <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-theme" type="button"><?php echo e(__('upload')); ?></button>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-6">
                                        <label for="school_support_email"><?php echo e(__('school').' '.__('email')); ?> <span class="text-danger">*</span></label>
                                        <input type="email" name="school_support_email" id="school_support_email" placeholder="<?php echo e(__('support').' '.__('email')); ?>" class="form-control" required>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-6">
                                        <label for="school_support_phone"><?php echo e(__('school').' '.__('phone')); ?> <span class="text-danger">*</span></label>
                                        <input type="number" name="school_support_phone" maxlength="16" id="school_support_phone" placeholder="<?php echo e(__('support').' '.__('phone')); ?>" min="0" class="form-control remove-number-increment" required>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-6">
                                        <label for="school_tagline"><?php echo e(__('tagline')); ?> <span class="text-danger">*</span></label>
                                        <textarea name="school_tagline" id="school_tagline" cols="30" rows="3" class="form-control" placeholder="<?php echo e(__('tagline')); ?>" required></textarea>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-6">
                                        <label for="school_address"><?php echo e(__('address')); ?> <span class="text-danger">*</span></label>
                                        <textarea name="school_address" id="school_address" cols="30" rows="3" class="form-control" placeholder="<?php echo e(__('address')); ?>" required></textarea>
                                    </div>

                                    

                                    <div class="form-group col-sm-12 col-md-6">
                                        <label for="school_domain"><?php echo e(__('School Code Prefix')); ?></label> <span class="text-danger">*</span>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control school_code_prefix" id="school_code_prefix" name="school_code_prefix" required placeholder="<?php echo e(__('prefix')); ?>" value="<?php echo e($prefix); ?>">
                                            <div class="input-group-append">
                                                <input type="text" class="input-group-text text-body school_code" id="basic-addon2" name="school_code" value="<?php echo e($school_code); ?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                        <label><?php echo e(__('domain').' '. __('type')); ?> <span class="text-danger">*</span></label><br>
                                        <div class="d-flex">
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    <?php echo Form::radio('domain_type', 'default', false, ['class' => 'default', 'checked' => "checked"]); ?><?php echo e(__('default')); ?>

                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    <?php echo Form::radio('domain_type', 'custom', false, ['class' => 'custom']); ?><?php echo e(__('custom')); ?>

                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">    
                                    <div class="form-group col-sm-12 col-md-4 defaultDomain" style="display: none">
                                        <label for="school_domain"><?php echo e(__('default_domain')); ?></label>
                                        <div class="input-group mb-3">
                                                <input type="text" class="form-control domain-pattern" name="domain" placeholder="<?php echo e(__('domain')); ?>" aria-label="Recipient's username" aria-describedby="basic-addon2" disabled>
                                            <div class="input-group-append">
                                                <span class="input-group-text text-body" id="basic-addon2">.<?php echo e($baseUrlWithoutScheme); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-4 customDomain" style="display: none">
                                        <label for="school_domain"><?php echo e(__('custom_domain')); ?></label>
                                        <div class="input-group mb-3">
                                                <input type="text" class="form-control domain-pattern" name="domain" placeholder="<?php echo e(__('domain')); ?>" aria-label="Recipient's username" aria-describedby="basic-addon2" disabled>
                                        </div>
                                    </div>
                                </div>
                             
                                <?php if(!empty($extraFields)): ?>
                                    <div class="row other-details">

                                        
                                        <?php $__currentLoopData = $extraFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php $fieldName = str_replace(' ', '_', $data->name) ?>
                                            
                                            <?php echo e(Form::hidden('extra_fields['.$key.'][id]', '', ['id' => $fieldName.'_id'])); ?>


                                            
                                            <?php echo e(Form::hidden('extra_fields['.$key.'][form_field_id]', $data->id)); ?>


                                            
                                            <?php echo e(Form::hidden('extra_fields['.$key.'][input_type]', $data->type)); ?>


                                            <div class='form-group col-xl-4 col-lg-6 col-md-6 col-sm-12'>

                                                
                                                <?php if($data->type != 'radio' && $data->type != 'checkbox'): ?>
                                                    <label><?php echo e($data->name); ?> <?php if($data->is_required): ?>
                                                            <span class="text-danger">*</span>
                                                        <?php endif; ?></label>
                                                <?php endif; ?>

                                                
                                                <?php if($data->type == 'text'): ?>
                                                    <?php echo e(Form::text('extra_fields['.$key.'][data]', '', ['class' => 'form-control text-fields', 'id' => $fieldName, 'placeholder' => $data->name, ($data->is_required == 1 ? 'required' : '')])); ?>

                                                    
                                                <?php elseif($data->type == 'number'): ?>
                                                    <?php echo e(Form::number('extra_fields['.$key.'][data]', '', ['min' => 0, 'class' => 'form-control number-fields', 'id' => $fieldName, 'placeholder' => $data->name, ($data->is_required == 1 ? 'required' : '')])); ?>


                                                    
                                                <?php elseif($data->type == 'dropdown'): ?>
                                                    <?php echo e(Form::select(
                                                        'extra_fields['.$key.'][data]',$data->default_values,
                                                        null,
                                                        [
                                                            'id' => $fieldName,
                                                            'class' => 'form-control select-fields',
                                                            ($data->is_required == 1 ? 'required' : ''),
                                                            'placeholder' => 'Select '.$data->name
                                                        ]
                                                    )); ?>


                                                    
                                                <?php elseif($data->type == 'radio'): ?>
                                                    <label class="d-block"><?php echo e($data->name); ?> <?php if($data->is_required): ?>
                                                            <span class="text-danger">*</span>
                                                        <?php endif; ?></label>
                                                    <div class="d-flex flex-wrap">
                                                        <?php $__currentLoopData = $data->default_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $keyRadio => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <div class="form-check mr-3">
                                                                <label class="form-check-label">
                                                                    <?php echo e(Form::radio('extra_fields['.$key.'][data]', $value, null, ['id' => $fieldName.'_'.$keyRadio, 'class' => 'radio-fields',($data->is_required == 1 ? 'required' : '')])); ?>

                                                                    <?php echo e($value); ?>

                                                                </label>
                                                            </div>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </div>

                                                    
                                                <?php elseif($data->type == 'checkbox'): ?>
                                                    <label class="d-block"><?php echo e($data->name); ?> <?php if($data->is_required): ?>
                                                            <span class="text-danger">*</span>
                                                        <?php endif; ?></label>
                                                    <div class="d-flex flex-wrap">
                                                        <?php $__currentLoopData = $data->default_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chkKey => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <div class="form-check mr-3">
                                                                <label class="form-check-label">
                                                                    <?php echo e(Form::checkbox('extra_fields['.$key.'][data][]', $value, null, ['id' => $fieldName.'_'.$chkKey, 'class' => 'form-check-input chkclass checkbox-fields',($data->is_required == 1 ? 'required' : '')])); ?> <?php echo e($value); ?>

                                                                </label>
                                                            </div>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </div>

                                                    
                                                <?php elseif($data->type == 'textarea'): ?>
                                                    <?php echo e(Form::textarea('extra_fields['.$key.'][data]', '', ['placeholder' => $data->name, 'id' => $fieldName, 'class' => 'form-control textarea-fields', ($data->is_required ? 'required' : '') , 'rows' => 3])); ?>


                                                    
                                                <?php elseif($data->type == 'file'): ?>
                                                    <div class="input-group">
                                                        <?php echo e(Form::file('extra_fields['.$key.'][data]', ['class' => 'file-upload-default', 'id' => $fieldName,($data->is_required == 1 ? 'required' : '')])); ?>

                                                        <?php echo e(Form::text('', '', ['class' => 'form-control file-upload-info', 'disabled' => '', 'placeholder' => __('image')])); ?>

                                                        <span class="input-group-append">
                                                            <button class="file-upload-browse btn btn-theme" type="button"><?php echo e(__('upload')); ?></button>
                                                        </span>
                                                    </div>
                                                    <div id="file_div_<?php echo e($fieldName); ?>" class="mt-2 d-none file-div">
                                                        <a href="" id="file_link_<?php echo e($fieldName); ?>" target="_blank"><?php echo e($data->name); ?></a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <input class="btn btn-theme float-right ml-3" id="create-btn" type="submit"  value=<?php echo e(__('submit')); ?> <?php echo e($email_verified == 0 ? 'disabled' : ''); ?>>
                            
                           
                            <input class="btn btn-secondary float-right" type="reset" value=<?php echo e(__('reset')); ?>>
                            
                            <div class="p-4 mt-5 mb-4">
                                <?php if($email_verified == 0): ?>
                                    <div class="alert alert-danger mt-2" role="alert">
                                        <strong><?php echo e(__('Warning!')); ?></strong> 
                                        <?php echo __('Warning! Please configure the email settings first to continue with creating the school. :click_here',['click_here' => '<a href="/system-settings/email" >'. __('click_here').'</a>']); ?> 
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                        </form>
                        
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            <?php echo e(__('list') . ' ' . __('schools')); ?>

                        </h4>
                        <div class="row" id="toolbar">
                            <div class="form-group col-sm-12 col-md-4">
                                <label class="filter-menu" for="package"><?php echo e(__('package')); ?></label>
                                <?php echo Form::select('package', ['' => 'All'] + $packages, null, ['class' => 'form-control','id' => 'filter_package_id']); ?>

                            </div>
                        </div>
                        <div class="col-12 text-right">
                            <b><a href="#" class="table-list-type active mr-2" data-id="0"><?php echo e(__('all')); ?></a></b> | <a href="#" class="ml-2 table-list-type" data-id="1"><?php echo e(__("Trashed")); ?></a>
                        </div>
                        <table aria-describedby="mydesc" class='table' id='table_list'
                               data-toggle="table" data-url="<?php echo e(route('schools.show', 1)); ?>"
                               data-click-to-select="true" data-side-pagination="server"
                               data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                               data-search="true" data-toolbar="#toolbar" data-show-columns="true"
                               data-show-refresh="true" data-trim-on-search="false" data-mobile-responsive="true"
                               data-sort-name="id" data-sort-order="desc" data-maintain-selected="true" data-export-data-type='all'
                               data-export-options='{ "fileName": "<?php echo e(__('school')); ?>-<?= date(' d-m-y') ?>" ,"ignoreColumn":["operate"]}'
                               data-show-export="true" data-query-params="schoolQueryParams" data-escape="true">
                            <thead>
                            <tr>
                                <th scope="col" data-field="id" data-sortable="true" data-visible="false"><?php echo e(__('id')); ?></th>
                                <th scope="col" data-field="no"><?php echo e(__('no.')); ?></th>
                                <th scope="col" data-field="code" data-visible="false"><?php echo e(__('code')); ?></th>
                                <th scope="col" data-field="logo" data-formatter="imageFormatter"><?php echo e(__('logo')); ?></th>
                                <th scope="col" data-field="name"><?php echo e(__('name')); ?></th>
                                <th scope="col" data-field="support_email"><?php echo e(__('school').' '.__('email')); ?></th>
                                <th scope="col" data-field="support_phone"><?php echo e(__('school').' '.__('phone')); ?></th>
                                <th scope="col" data-field="email_verified_at" data-formatter="verifyEmailStatusFormatter"><?php echo e(__('verify_email')); ?></th>
                                <th scope="col" data-field="tagline" data-visible="false"><?php echo e(__('tagline')); ?></th>
                                <th scope="col" data-field="address"><?php echo e(__('address')); ?></th>
                                <th scope="col" data-field="admin_id" data-visible="false"><?php echo e(__('admin').' '.__('id')); ?></th>
                                <th scope="col" data-field="user" data-formatter="schoolAdminFormatter"><?php echo e(__('school').' '.__('admin')); ?></th>
                                <th scope="col" data-field="active_plan"><?php echo e(__('active_plan')); ?></th>
                                <th scope="col" data-field="status" data-formatter="activeStatusFormatter"><?php echo e(__('status')); ?></th>
                                <th scope="col" data-field="operate" data-formatter="actionColumnFormatter" data-events="schoolEvents" data-escape="false"><?php echo e(__('action')); ?></th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><?php echo e(__('edit')); ?> <?php echo e(__('school')); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="fa fa-close"></i></span>
                    </button>
                </div>
                <form id="edit-form" class="pt-3 edit-form" action="<?php echo e(url('schools')); ?>">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="modal-body">
                        <div class="row">                        
                            <div class="form-group col-sm-12 col-md-6">
                                <label for="edit_school_name"><?php echo e(__('name')); ?> <span class="text-danger">*</span></label>
                                <input type="text" name="edit_school_name" id="edit_school_name" placeholder="<?php echo e(__('schools')); ?>" class="form-control" required>
                            </div>
                            <div class="form-group col-sm-12 col-md-6">
                                <label><?php echo e(__('logo')); ?></label>
                                <input type="file" id="edit_school_image" name="edit_school_image" class="file-upload-default" accept="image/png, image/jpg, image/jpeg, image/svg+xml"/>
                                <div class="input-group">
                                    <input type="text" class="form-control file-upload-info" disabled="" placeholder="<?php echo e(__('logo')); ?>" aria-label=""/>
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-theme" type="button"><?php echo e(__('upload')); ?></button>
                                    </span>
                                </div>
                                <div style="width: 60px;">
                                    <img src="" id="edit-school-logo-tag" class="img-fluid w-100" alt=""/>
                                </div>
                            </div>
                            <div class="form-group col-sm-12 col-md-3">
                                <label for="edit_school_support_email"><?php echo e(__('school').' '.__('email')); ?> <span class="text-danger">*</span></label>
                                <input type="email" name="edit_school_support_email" id="edit_school_support_email" placeholder="<?php echo e(__('support').' '.__('email')); ?>" class="form-control" required>
                            </div>
                            <div class="form-group col-sm-12 col-md-3">
                                <label for="edit_school_support_phone"><?php echo e(__('school').' '.__('phone')); ?> <span class="text-danger">*</span></label>
                                <input type="number" name="edit_school_support_phone" min="0" id="edit_school_support_phone" placeholder="<?php echo e(__('support').' '.__('phone')); ?>" class="form-control remove-number-increment" required>
                            </div>
                            
                            <div class="form-group col-sm-12 col-md-3" id="edit_assign_package_container">
                                <label for="assign_package"><?php echo e(__('assign_package')); ?> </label>
                                <?php echo Form::select('assign_package', $packages, null, ['class' => 'form-control mb-2', 'placeholder' => __('select_package'),'id' => 'edit_assign_package']); ?>

                                

                            </div>

                            <div class="form-group col-sm-12 col-md-3">
                                <label for="school_code"><?php echo e(__('school_code')); ?> </label>
                                <input type="text" name="code" disabled id="school_code" placeholder="<?php echo e(__('school_code')); ?>" class="form-control" required>

                            </div>

                            <div class="form-group col-sm-12 col-md-6">
                                <label for="edit_school_tagline"><?php echo e(__('tagline')); ?> <span class="text-danger">*</span></label>
                                <textarea name="edit_school_tagline" id="edit_school_tagline" cols="30" rows="3" class="form-control" placeholder="<?php echo e(__('tagline')); ?>" required></textarea>
                            </div>
                            <div class="form-group col-sm-12 col-md-6">
                                <label for="edit_school_address"><?php echo e(__('address')); ?> <span class="text-danger">*</span></label>
                                <textarea name="edit_school_address" id="edit_school_address" cols="30" rows="3" class="form-control" placeholder="<?php echo e(__('address')); ?>" required></textarea>
                            </div>

                            
                        </div>
                        <div class="row">    
                            <div class="form-group col-sm-12 col-md-3">
                                <label><?php echo e(__('domain').' '. __('type')); ?> <span class="text-danger">*</span></label><br>
                                <div class="d-flex">
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <?php echo Form::radio('edit_domain_type', 'default', false, ['class' => 'edit_default', 'checked']); ?><?php echo e(__('default')); ?>

                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <?php echo Form::radio('edit_domain_type', 'custom', false, ['class' => 'edit_custom']); ?><?php echo e(__('custom')); ?>

                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">    
                            <div class="form-group col-sm-12 col-md-4 defaultDomain" style="display: none">
                                <label for="school_domain"><?php echo e(__('default_domain')); ?></label>
                                <div class="input-group mb-3">
                                        <input type="text" class="form-control domain-pattern" id="edit_default_domain" name="edit_domain" placeholder="<?php echo e(__('domain')); ?>" aria-label="Recipient's username" aria-describedby="basic-addon2" disabled>
                                    <div class="input-group-append">
                                        <span class="input-group-text text-body" id="basic-addon2">.<?php echo e($baseUrlWithoutScheme); ?></span>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label><?php echo e(__('url')); ?>: <a href="" target="_blank" class="text-theme school_url"></a></label>
                                </div>
                            </div>
                            <div class="form-group col-sm-12 col-md-4 customDomain" style="display: none">
                                <label for="school_domain"><?php echo e(__('custom_domain')); ?></label>
                                <div class="input-group mb-3">
                                        <input type="text" class="form-control domain-pattern" id="edit_custom_domain" name="edit_domain" placeholder="<?php echo e(__('domain')); ?>" aria-label="Recipient's username" aria-describedby="basic-addon2" disabled>
                                </div>
                                <div class="mt-2">
                                    <label><?php echo e(__('url')); ?>: <a href="" target="_blank" class="text-theme school_url"></a></label>
                                </div>
                            </div>
                        </div>
                        <?php if(!empty($extraFields)): ?>
                            <div class="row other-details">

                                
                                <?php $__currentLoopData = $extraFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $fieldName = str_replace(' ', '_', $data->name) ?>
                                    
                                    <?php echo e(Form::hidden('edit_extra_fields['.$key.'][id]', '', ['class' => 'edit_extra_fields_id','id' => 'edit_'.$fieldName.'_id'])); ?>


                                    
                                    <?php echo e(Form::hidden('edit_extra_fields['.$key.'][form_field_id]', $data->id)); ?>


                                    
                                    <?php echo e(Form::hidden('edit_extra_fields['.$key.'][input_type]', $data->type)); ?>


                                    <div class='form-group col-md-12 col-lg-6 col-xl-4 col-sm-12'>

                                        
                                        <?php if($data->type != 'radio' && $data->type != 'checkbox'): ?>
                                            <label><?php echo e($data->name); ?> <?php if($data->is_required): ?>
                                                    <span class="text-danger">*</span>
                                                <?php endif; ?></label>
                                        <?php endif; ?>

                                        
                                        <?php if($data->type == 'text'): ?>
                                            <?php echo e(Form::text('edit_extra_fields['.$key.'][data]', '', ['class' => 'form-control text-fields', 'id' => 'edit_'.$fieldName, 'placeholder' => $data->name, ($data->is_required == 1 ? 'required' : '')])); ?>

                                            
                                        <?php elseif($data->type == 'number'): ?>
                                            <?php echo e(Form::number('edit_extra_fields['.$key.'][data]', '', ['min' => 0, 'class' => 'form-control number-fields', 'id' => 'edit_'.$fieldName, 'placeholder' => $data->name, ($data->is_required == 1 ? 'required' : '')])); ?>


                                            
                                        <?php elseif($data->type == 'dropdown'): ?>
                                            <?php echo e(Form::select(
                                                'edit_extra_fields['.$key.'][data]',$data->default_values,
                                                null,
                                                [
                                                    'id' => 'edit_'.$fieldName,
                                                    'class' => 'form-control select-fields',
                                                    ($data->is_required == 1 ? 'required' : ''),
                                                    'placeholder' => 'Select '.$data->name
                                                ]
                                            )); ?>


                                            
                                        <?php elseif($data->type == 'radio'): ?>
                                            <label class="d-block"><?php echo e($data->name); ?> <?php if($data->is_required): ?>
                                                    <span class="text-danger">*</span>
                                                <?php endif; ?></label>
                                            <div class="row form-check-inline ml-1">
                                                <?php $__currentLoopData = $data->default_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $keyRadio => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="col-md-12 col-lg-12 col-xl-6 col-sm-12 form-check">
                                                        <label class="form-check-label">
                                                            <?php echo e(Form::radio('edit_extra_fields['.$key.'][data]', $value, null, ['id' => 'edit_'.$fieldName.'_'.$keyRadio, 'class' => 'edit-radio-fields',($data->is_required == 1 ? 'required' : '')])); ?>

                                                            <?php echo e($value); ?>

                                                        </label>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>

                                            
                                        <?php elseif($data->type == 'checkbox'): ?>
                                            <label class="d-block"><?php echo e($data->name); ?> <?php if($data->is_required): ?>
                                                    <span class="text-danger">*</span>
                                                <?php endif; ?></label>
                                            <div class="row form-check-inline ml-1">
                                                <?php $__currentLoopData = $data->default_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chkKey => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="col-lg-12 col-xl-6 col-md-12 col-sm-12 form-check">
                                                        <label class="form-check-label">
                                                            <?php echo e(Form::checkbox('edit_extra_fields['.$key.'][data][]', $value, null, ['id' => 'edit_'.$fieldName.'_'.$chkKey, 'class' => 'form-check-input chkclass checkbox-fields',($data->is_required == 1 ? 'required' : '')])); ?> <?php echo e($value); ?>

                                                        </label>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>

                                            
                                        <?php elseif($data->type == 'textarea'): ?>
                                            <?php echo e(Form::textarea('edit_extra_fields['.$key.'][data]', '', ['placeholder' => $data->name, 'id' => 'edit_'.$fieldName, 'class' => 'form-control textarea-fields', ($data->is_required ? 'required' : '') , 'rows' => 3])); ?>


                                            
                                        <?php elseif($data->type == 'file'): ?>
                                            <div class="input-group col-xs-12">
                                                <?php echo e(Form::file('edit_extra_fields['.$key.'][data]', ['class' => 'file-upload-default', 'id' => 'edit_'.$fieldName,($data->is_required == 1 ? 'required' : '')])); ?>

                                                <?php echo e(Form::text('', '', ['class' => 'form-control file-upload-info', 'disabled' => '', 'placeholder' => __('image')])); ?>

                                                <span class="input-group-append">
                                                    <button class="file-upload-browse btn btn-theme" type="button"><?php echo e(__('upload')); ?></button>
                                                </span>
                                            </div>
                                            <div id="edit_file_div_<?php echo e($fieldName); ?>" class="mt-2 d-none file-div">
                                                <a href="" id="edit_file_link_<?php echo e($fieldName); ?>" target="_blank"><?php echo e($data->name); ?></a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo e(__('close')); ?></button>
                        <input class="btn btn-theme" type="submit" value=<?php echo e(__('submit')); ?> />
                    </div>
                </form>
            </div>
        </div>
    </div>



    
    <div class="modal fade" id="editAdminModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><?php echo e(__('change_admin')); ?></h5>
                    <button type="button" class="close close-modal" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="fa fa-close"></i></span>
                    </button>
                </div>
                <form id="admin-form-modal" class="create-form change-school-admin" action="<?php echo e(url('schools/admin/update')); ?>" data-success-function="successFunction" method="post" novalidate>
                    <input type="hidden" name="edit_id" id="edit_school_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-sm-12 col-md-12">
                                <label><?php echo e(__('admin') . ' ' . __('email')); ?> <span class="text-danger">*</span></label>
                                <input type="email" name="edit_admin_email" id="edit-admin-email" class="form-control">
                                <input type="hidden" id="edit_admin_id" name="edit_admin_id">
                                
                                
                            </div>

                            <div class="form-group col-sm-12 col-md-6">
                                <label for="edit-admin-first-name"><?php echo e(__('admin') . ' ' . __('first_name')); ?> <span class="text-danger">*</span></label>
                                <input type="text" name="edit_admin_first_name" id="edit-admin-first-name" placeholder="<?php echo e(__('admin') . ' ' . __('first_name')); ?>" class="form-control" required>
                            </div>
                            <div class="form-group col-sm-12 col-md-6">
                                <label for="edit-admin-last-name"><?php echo e(__('admin') . ' ' . __('last_name')); ?> <span class="text-danger">*</span></label>
                                <input type="text" name="edit_admin_last_name" id="edit-admin-last-name" placeholder="<?php echo e(__('admin') . ' ' . __('last_name')); ?>" class="form-control" required>
                            </div>
                            <div class="form-group col-sm-12 col-md-6">
                                <label for="edit-admin-contact"><?php echo e(__('admin') . ' ' . __('contact')); ?> <span class="text-danger">*</span></label>
                                <input type="number" name="edit_admin_contact" id="edit-admin-contact" placeholder="<?php echo e(__('admin') . ' ' . __('contact')); ?>" class="form-control remove-number-increment" min="0" required>
                            </div>
                            <div class="form-group col-sm-12 col-md-6">
                                <label><?php echo e(__('admin') . ' ' . __('image')); ?></label>
                                <input type="file" name="edit_admin_image" class="edit-admin-image file-upload-default" accept="image/png, image/jpg, image/jpeg, image/svg+xml"/>
                                <div class="input-group col-xs-12">
                                    <input type="text" class="form-control file-upload-info" disabled="" placeholder="<?php echo e(__('admin') . ' ' . __('image')); ?>" aria-label=""/>
                                    <span class="input-group-append">
                                    <button class="file-upload-browse btn btn-theme" id="file-upload-admin-browse" type="button"><?php echo e(__('upload')); ?></button>
                                </span>
                                </div>                      
                                <div style="width: 100px;">
                                    <img src="" id="admin-image-tag" class="img-fluid w-100" alt=""/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-12 col-md-4">
                                <div class="d-flex">
                                    <div class="form-check w-fit-content">
                                        <label class="form-check-label ml-4">
                                            <input type="checkbox" class="form-check-input" name="reset_password" value="1"><?php echo e(__('reset_password')); ?>

                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-sm-12 col-md-4">
                                <div class="d-flex">
                                    <div class="form-check w-fit-content">
                                        <label class="form-check-label ml-4">
                                            <input type="checkbox" class="form-check-input" name="resend_email" value="1"><?php echo e(__('resend_email')); ?>

                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-sm-12 col-md-4">
                                <div class="d-flex">
                                    <div class="form-check w-fit-content">
                                        <label class="form-check-label ml-4">
                                            <input type="checkbox" class="form-check-input" id="manually_verify_email" name="manually_verify_email" value="1"> <?php echo e(__('manually_verify_email')); ?>

                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-sm-12 col-md-4">
                                <div class="d-flex">
                                    <div class="form-check w-fit-content">
                                        <label class="form-check-label ml-4">
                                            <input type="checkbox" class="form-check-input" id="two_factor_verification" name="two_factor_verification" value="0"> <?php echo e(__('two_factor_verification')); ?>

                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary close-modal" data-dismiss="modal"><?php echo e(__('close')); ?></button>
                        <input class="btn btn-theme" type="submit" value=<?php echo e(__('submit')); ?> />
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?> 
<?php $__env->startSection('js'); ?>
<script>
    function successFunction() {
        $('#editAdminModal').modal('hide');
    }

    $(document).ready(function() {
        $('#two_factor_verification').change(function() {
            if ($(this).is(':checked')) {
                $('#two_factor_verification').prop('checked', true);
                $('#two_factor_verification').val(1);
            } else {
                $('#two_factor_verification').prop('checked', false);
                $('#two_factor_verification').val(0);
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('#createDemoSchool').click(function() {
            showLoading();// Show loading message
    
            $.ajax({
                url: baseUrl + '/schools/create-demo-school',
                type: 'POST',
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                },
                success: function(data) {
                    if (data.code == 200) {
                        closeLoading();
                        showSuccessToast(data.message);
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000); 
                    } else {
                        closeLoading();
                        showErrorToast(data.message);
                    }
                },
                error: function(xhr, status, error) {
                    closeLoading();
                    console.error('Error:', error);
                    showErrorToast(trans('error_occured'));
                }
            });
        });

    
    });
</script>
<script>
    $(document).ready(function () {
        function toggleFields() {
            if ($('.default').is(':checked')) {
                $('.defaultDomain').show().find('input').prop('disabled', false);
                $('.customDomain').hide().find('input').prop('disabled', true);
            } else if ($('.custom').is(':checked')) {
                $('.customDomain').show().find('input').prop('disabled', false);
                $('.defaultDomain').hide().find('input').prop('disabled', true);
            }
        }  
        $("input[name='domain_type']").on('change', toggleFields);

        toggleFields();
    });
</script>


<script>
    $(document).ready(function() {
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Handle modal events
        $('#schoolHelpModal').on('show.bs.modal', function () {
            // Add any animation or preparation code here
        });

        $('#schoolHelpModal').on('shown.bs.modal', function () {
            // Focus management or post-display actions
        });

        // Close modal on escape key
        $(document).on('keydown', function(e) {
            if (e.key === "Escape") {
                $('#schoolHelpModal').modal('hide');
            }
        });

        // Prevent modal from closing when clicking inside
        $('#schoolHelpModal .modal-content').on('click', function(e) {
            e.stopPropagation();
        });
    });
</script>
    
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="<?php echo e(asset('js/components/help-modal.js')); ?>"></script>
    <script>
        $(document).ready(function() {
            // Initialize the help modal
            const schoolHelpModal = new HelpModal('schoolHelpModal');
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/schools/index.blade.php ENDPATH**/ ?>