<!-- Button trigger modal -->

<div class="modal fade formModal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered  modal-xl">
        <div class="modal-content row">
            <div class="col-12 rightSide">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel"><?php echo e(__('registration_form')); ?></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="school-registration" action="<?php echo e(url('schools/registration')); ?>" method="post" data-success-function="schoolRegistrationSuccess">
                        <?php echo csrf_field(); ?>
                        <div class="schoolFormWrapper">
                            <div class="headingWrapper">
                                <span><?php echo e(__('create_school')); ?></span>
                            </div>
                            <div class="formWrapper">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="inputWrapper">
                                            <label for="name"><?php echo e(__('name')); ?> <span class="text-danger">*</span></label>
                                            <input type="text" name="school_name" id="name" placeholder="<?php echo e(__('enter_your_school_name')); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="inputWrapper">
                                            <label for="supportEmail"><?php echo e(__('email')); ?> <span class="text-danger">*</span></label>
                                            <input type="email" name="school_email" id="support-email"
                                                placeholder="<?php echo e(__('enter_your_school_email')); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="inputWrapper">
                                            <label for="supportPhone"><?php echo e(__('mobile')); ?> <span class="text-danger">*</span></label>
                                            <input type="text" oninput="this.value=this.value.replace(/[^0-9]/g,'');" name="school_phone" id="supportPhone"
                                                placeholder="<?php echo e(__('enter_your_school_mobile_number')); ?>" maxlength="16" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="inputWrapper">
                                            <label for="address"><?php echo e(__('address')); ?> <span class="text-danger">*</span></label>
                                            <input type="text" name="school_address" id="address"
                                                placeholder="<?php echo e(__('enter_your_school_address')); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="inputWrapper">
                                            <label for="tagline"><?php echo e(__('tagline')); ?> <span class="text-danger">*</span></label>
                                            <input type="text" name="school_tagline" id="tagline" placeholder="<?php echo e(__('tagline')); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="inputWrapper">
                                            <label><?php echo e(__('DOMAIN TYPE')); ?> <span class="text-danger">*</span></label>
                                            <div class="d-flex mt-2" style="gap: 20px;">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="domain_type" id="domain_type_default" value="default" checked>
                                                    <label class="form-check-label" for="domain_type_default"><?php echo e(__('DEFAULT')); ?></label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="domain_type" id="domain_type_custom" value="custom">
                                                    <label class="form-check-label" for="domain_type_custom"><?php echo e(__('CUSTOM')); ?></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12" id="domain_input_wrapper">
                                        <div class="inputWrapper">
                                            <label id="domain_label" for="domain"><?php echo e(__('sub_domain')); ?> <span class="text-danger">*</span></label>
                                            <input type="text" name="domain" id="domain" placeholder="<?php echo e(__('enter_sub_domain')); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <?php if(isset($extraFields) && count($extraFields)): ?>     
                                    <div class="row other-details mt-3">

                                        
                                        <?php $__currentLoopData = $extraFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            
                                            <?php echo e(Form::hidden('extra_fields['.$key.'][id]', '', ['id' => $data->type.'_'.$key.'_id'])); ?>


                                            
                                            <?php echo e(Form::hidden('extra_fields['.$key.'][form_field_id]', $data->id, ['id' => $data->type.'_'.$key.'_id'])); ?>


                                            <div class='form-group col-md-12 col-lg-6 col-xl-6 col-sm-12'>

                                                
                                                <?php if($data->type != 'radio' && $data->type != 'checkbox'): ?>
                                                    <label><?php echo e($data->name); ?> <?php if($data->is_required): ?>
                                                            <span class="text-danger">*</span>
                                                        <?php endif; ?></label>
                                                <?php endif; ?>

                                                
                                                <?php if($data->type == 'text'): ?>
                                                    <?php echo e(Form::text('extra_fields['.$key.'][data]', '', ['class' => 'form-control text-fields', 'id' => $data->type.'_'.$key, 'placeholder' => $data->name, ($data->is_required == 1 ? 'required' : '')])); ?>

                                                    
                                                <?php elseif($data->type == 'number'): ?>
                                                    <?php echo e(Form::number('extra_fields['.$key.'][data]', '', ['min' => 0, 'class' => 'form-control number-fields', 'id' => $data->type.'_'.$key, 'placeholder' => $data->name, ($data->is_required == 1 ? 'required' : '')])); ?>


                                                    
                                                <?php elseif($data->type == 'dropdown'): ?>
                                                    <select name="extra_fields[<?php echo e($key); ?>][data]" id="<?php echo e($data->type . '_' . $key); ?>" class="form-control select-fields" 
                                                            <?php echo e($data->is_required == 1 ? 'required' : ''); ?>>
                                                        <option value="" disabled selected>Select <?php echo e($data->name); ?></option>
                                                        <?php $__currentLoopData = $data->default_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $optionKey => $optionValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($optionKey); ?>"><?php echo e($optionValue); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>

                                                    
                                                <?php elseif($data->type == 'radio'): ?>
                                                    <label class="d-block"><?php echo e($data->name); ?> <?php if($data->is_required): ?>
                                                            <span class="text-danger">*</span>
                                                        <?php endif; ?></label>
                                                    <div class="row col-md-12 col-lg-12 col-xl-6 col-sm-12">
                                                        <?php if(count($data->default_values)): ?>
                                                            <?php $__currentLoopData = $data->default_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $keyRadio => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <div class="form-check mr-2">
                                                                    <label class="form-check-label">
                                                                        <?php echo e(Form::radio('extra_fields['.$key.'][data]', $value, null, ['id' => $data->type.'_'.$keyRadio, 'class' => 'radio-fields',($data->is_required == 1 ? 'required' : '')])); ?>

                                                                        <?php echo e($value); ?>

                                                                    </label>
                                                                </div>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        <?php endif; ?>
                                                    </div>

                                                    
                                                <?php elseif($data->type == 'checkbox'): ?>
                                                    <label class="d-block"><?php echo e($data->name); ?> <?php if($data->is_required): ?>
                                                            <span class="text-danger">*</span>
                                                        <?php endif; ?></label>
                                                    <?php if(count($data->default_values)): ?>
                                                        <div class="row col-lg-12 col-xl-6 col-md-12 col-sm-12 checkbox-group">
                                                            <?php $__currentLoopData = $data->default_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chkKey => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <div class="mr-2 form-check">
                                                                    <label class="form-check-label group-required">
                                                                        <?php echo e(Form::checkbox('extra_fields['.$key.'][data][]', $value, null, ['id' => $data->type.'_'.$chkKey, 'class' => 'form-check-input chkclass checkbox-fields checkbox-group'])); ?> <?php echo e($value); ?>


                                                                    </label>
                                                                </div>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </div>
                                                        <?php if($data->is_required): ?>
                                                          <span class="text-danger d-none checkbox-error"><?php echo e(__('this field is required')); ?></span>
                                                       <?php endif; ?>

                                                    <?php endif; ?>
                                             
                                                    
                                                <?php elseif($data->type == 'textarea'): ?>
                                                    <?php echo e(Form::textarea('extra_fields['.$key.'][data]', '', ['placeholder' => $data->name, 'id' => $data->type.'_'.$key, 'class' => 'form-control textarea-fields', ($data->is_required ? 'required' : '') , 'rows' => 3])); ?>


                                                    
                                                <?php elseif($data->type == 'file'): ?>
                                                    <div class="input-group col-xs-12">
                                                        <?php echo e(Form::file('extra_fields['.$key.'][data]', ['class' => 'file-upload-default', 'id' => $data->type.'_'.$key, ($data->is_required ? 'required' : '')])); ?>

                                                        <?php echo e(Form::text('', '', ['class' => 'form-control file-upload-info', 'readonly' => '', 'placeholder' => __('image')])); ?>

                                                        <span class="input-group-append">
                                                            <button class="file-upload-browse btn btn-themes" type="button"><?php echo e(__('upload')); ?></button>
                                                        </span>
                                                    </div>
                                                    <div id="file_div_<?php echo e($key); ?>" class="mt-2 d-none file-div">
                                                        <a href="" id="file_link_<?php echo e($key); ?>" target="_blank"><?php echo e($data->name); ?></a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="adminFormWrapper schoolFormWrapper">
                            <div class="formWrapper">
                                    <input type="hidden" name="trial_package" value="<?php echo e($trail_package ?? ''); ?>">

                                    <?php if(config('services.recaptcha.key') ?? ''): ?>
                                        <div class="col-lg-12">
                                            <div class="g-recaptcha mt-4" data-sitekey=<?php echo e(config('services.recaptcha.key')); ?>></div>
                                        </div>    
                                    <?php endif; ?>
                                    
                                    
                                    <div class="col-12 modalfooter">

                                        <div class="inputWrapper">
                                            
                                        </div>
                                        <div>
                                            <input type="submit" class="commonBtn" value="<?php echo e(__('submit')); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $(document).on('change', 'input[name="domain_type"]', function() {
        let val = $(this).val();
        if (val === 'default') {
            $('#domain_label').html("<?php echo e(__('sub_domain')); ?> <span class='text-danger'>*</span>");
            $('#domain').attr('placeholder', "<?php echo e(__('enter_sub_domain')); ?>");
        } else {
            $('#domain_label').html("<?php echo e(__('custom_domain')); ?> <span class='text-danger'>*</span>");
            $('#domain').attr('placeholder', "<?php echo e(__('enter_custom_domain')); ?>");
        }
    });
});

function schoolRegistrationSuccess(response) {
    if (response.redirect_url) {
        showSuccessToast(response.message);
        setTimeout(() => {
            window.location.href = response.redirect_url;
        }, 1500);
    } else {
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    }
}
</script>
<?php /**PATH /home/shacartc/school.tehub.in/resources/views/registration_form.blade.php ENDPATH**/ ?>