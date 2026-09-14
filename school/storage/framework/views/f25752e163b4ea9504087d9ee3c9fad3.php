<?php $__env->startSection('title'); ?>
    <?php echo e(__('general_settings')); ?>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('general_settings')); ?>

            </h3>
        </div>
        <div class="row grid-margin">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form class="create-form-without-reset" action="<?php echo e(route('school-settings.store')); ?>" method="POST" novalidate="novalidate" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="border border-secondary rounded-lg mb-2">
                                <div class="row my-4 mx-1">
                                    <div class="form-group col-md-4 col-sm-12">
                                        <label for="school_name"><?php echo e(__('school_name')); ?> <span class="text-danger">*</span></label>
                                        <input name="school_name" id="school_name" value="<?php echo e($settings['school_name'] ?? ''); ?>" type="text" maxlength="73" required placeholder="<?php echo e(__('school_name')); ?>" class="form-control"/>
                                    </div>
                                    <div class="form-group col-md-4 col-sm-12">
                                        <label for="school_email"><?php echo e(__('school_email')); ?> <span class="text-danger">*</span></label>
                                        <input name="school_email" id="school_email" value="<?php echo e($settings['school_email'] ?? ''); ?>" type="email" required placeholder="<?php echo e(__('school_email')); ?>" class="form-control"/>
                                    </div>

                                    <div class="form-group col-md-4 col-sm-12">
                                        <label for="school_code"><?php echo e(__('school_code')); ?></label>
                                        <input name="school_code" id="school_code" value="<?php echo e(Auth::user()->school->code); ?>" type="text" disabled placeholder="<?php echo e(__('school_code')); ?>" class="form-control"/>
                                    </div>

                                    <div class="form-group col-md-6 col-sm-12">
                                        <label for="school_phone"><?php echo e(__('school_phone')); ?> <span class="text-danger">*</span></label>
                                        <input name="school_phone" id="school_phone" value="<?php echo e($settings['school_phone'] ?? ''); ?>" type="number" required placeholder="<?php echo e(__('school_phone')); ?>" class="form-control remove-number-increment"/>
                                    </div>
                                    <div class="form-group col-md-6 col-sm-12">
                                        <label for="school_tagline"><?php echo e(__('school_tagline')); ?> <span class="text-danger">*</span></label>
                                        <textarea name="school_tagline" id="school_tagline" required placeholder="<?php echo e(__('school_tagline')); ?>" class="form-control"><?php echo e($settings['school_tagline'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="form-group col-md-4 col-sm-12">
                                        <label for="school_address"><?php echo e(__('school_address')); ?> <span class="text-danger">*</span></label>
                                        <textarea name="school_address" id="school_address" required placeholder="<?php echo e(__('school_address')); ?>" class="form-control"><?php echo e($settings['school_address'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="form-group col-md-4 col-sm-12">
                                        <label for="date_format"><?php echo e(__('date_format')); ?></label>
                                        <select name="date_format" id="date_format" required class="form-control">
                                            <?php $__currentLoopData = $getDateFormat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $dateformat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($key); ?>"<?php echo e(isset($settings['date_format']) && $settings['date_format'] == $key ? 'selected' : ''); ?>><?php echo e($dateformat); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-4 col-sm-12">
                                        <label for="time_format"><?php echo e(__('time_format')); ?></label>
                                        <select name="time_format" id="time_format" required class="form-control">
                                            <?php $__currentLoopData = $getTimeFormat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $timeFormat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($key); ?>"<?php echo e(isset($settings['time_format']) && $settings['time_format'] == $key ? 'selected' : ''); ?>><?php echo e($timeFormat); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row my-4 mx-1">
                                    <div class="form-group col-md-6 col-lg-6 col-xl-4 col-sm-12">
                                        <label for="favicon"><?php echo e(__('favicon')); ?> <span class="text-danger">*</span></label>
                                        <input type="file" name="favicon" class="file-upload-default"/>
                                        <div class="input-group col-xs-12">
                                            <input type="text" id="favicon" class="form-control file-upload-info" disabled="" placeholder="<?php echo e(__('favicon')); ?>"/>
                                            <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme" type="button"><?php echo e(__('upload')); ?></button>
                                        </span>
                                            <div class="col-md-12 mt-2">
                                                <img height="50px" src='<?php echo e($settings['favicon'] ??  ''); ?>' alt="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6 col-lg-6 col-xl-4 col-sm-12">
                                        <label for="horizontal_logo"><?php echo e(__('horizontal_logo')); ?> <span class="text-danger">*</span></label>
                                        <input type="file" name="horizontal_logo" class="file-upload-default"/>
                                        <div class="input-group col-xs-12">
                                            <input type="text" id="horizontal_logo" class="form-control file-upload-info" disabled="" placeholder="<?php echo e(__('horizontal_logo')); ?>"/>
                                            <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme" type="button"><?php echo e(__('upload')); ?></button>
                                        </span>
                                            <div class="col-md-12 mt-2">
                                                <img height="50px" src='<?php echo e($settings['horizontal_logo'] ?? ''); ?>' alt="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6 col-lg-6 col-xl-4 col-sm-12">
                                        <label for="vertical_logo"><?php echo e(__('vertical_logo')); ?> <span class="text-danger">*</span></label>
                                        <input type="file" name="vertical_logo" class="file-upload-default"/>
                                        <div class="input-group col-xs-12">
                                            <input type="text" class="form-control file-upload-info" id="vertical_logo" disabled="" placeholder="<?php echo e(__('vertical_logo')); ?>"/>
                                            <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-theme" type="button"><?php echo e(__('upload')); ?></button>
                                        </span>
                                            <div class="col-md-12 mt-2">
                                                <img height="50px" src='<?php echo e($settings['vertical_logo'] ?? ''); ?>' alt="">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-12 col-sm-12 mt-3">
                                        <label for="school_google_map_link"><?php echo e(__('google_map_link')); ?> <span class="text-danger">*</span><span class="text-small text-info"><?php echo e(__('convert_into_embed_url')); ?></span></label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" required name="google_map_link" placeholder="<?php echo e(__('google_map_link')); ?>" value="<?php echo e($settings['google_map_link'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4 col-sm-12">
                                        <label for="fees_remainder_duration"><?php echo e(__('fees_remainder_duration')); ?><span class="text-small text-info"><?php echo e(__('reminder_days_before_due')); ?></span></label>
                                        <div class="input-group mb-3">
                                            <input type="number" class="form-control" required name="fees_remainder_duration" placeholder="<?php echo e(__('fees_remainder_duration')); ?>" value="<?php echo e($settings['fees_remainder_duration'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="border border-secondary rounded-lg my-4 mx-1">
                                <div class="col-md-12 mt-3"><h4><?php echo e(__("UPI / QR Code Settings (Manual Payment)")); ?></h4></div>
                                <div class="col-12 mb-3">
                                    <hr class="mt-0">
                                </div>
                                <div class="row my-4 mx-1">
                                    <div class="form-group col-md-6 col-sm-12">
                                        <label for="upi_id"><?php echo e(__('UPI ID')); ?></label>
                                        <input type="text" name="upi_id" id="upi_id" class="form-control" placeholder="example@upi" value="<?php echo e($settings['upi_id'] ?? ''); ?>"/>
                                    </div>
                                    <div class="form-group col-md-6 col-sm-12">
                                        <label for="upi_qr_code"><?php echo e(__('UPI QR Code')); ?></label>
                                        <input type="file" name="upi_qr_code" class="file-upload-default"/>
                                        <div class="input-group col-xs-12">
                                            <input type="text" class="form-control file-upload-info" id="upi_qr_code_info" disabled="" placeholder="<?php echo e(__('UPI QR Code')); ?>"/>
                                            <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-theme" type="button"><?php echo e(__('upload')); ?></button>
                                            </span>
                                            <div class="col-md-12 mt-2">
                                                <?php if(isset($settings['upi_qr_code']) && !empty($settings['upi_qr_code'])): ?>
                                                    <img height="150px" src='<?php echo e($settings['upi_qr_code']); ?>' alt="UPI QR Code">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border border-secondary rounded-lg my-4 mx-1">
                                <div class="col-md-12 mt-3"><h4><?php echo e(__("Domain Settings")); ?></h4></div>
                                <div class="col-12 mb-3">
                                    <hr class="mt-0">
                                </div> 
                                <div class="row my-4 mx-1">  
                                    <div class="form-group col-sm-12 col-md-4">
                                        <label><?php echo e(__('domain').' '. __('type')); ?> <span class="text-danger">*</span></label><br>
                                        <div class="d-flex">
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    <?php echo Form::radio('domain_type', 'default', false, ['class' => 'default' , ($domain_type ==  "default") ? "checked" : "" ]); ?><?php echo e(__('default')); ?>

                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    <?php echo Form::radio('domain_type', 'custom', false, ['class' => 'custom', ($domain_type ==  "custom") ? "checked" : "" ]); ?><?php echo e(__('custom')); ?>

                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-4 defaultDomain" style="display: none">
                                        <label for="school_domain"><?php echo e(__('default_domain')); ?></label>
                                        <div class="input-group mb-3">
                                                <input type="text" class="form-control domain-pattern" name="domain" placeholder="<?php echo e(__('domain')); ?>" aria-label="Recipient's username" aria-describedby="basic-addon2" disabled value="<?php echo e(($domain_type ==  "default" && $settings['domain']) ? $settings['domain'] : ""); ?>">
                                            <div class="input-group-append">
                                                <span class="input-group-text text-body" id="basic-addon2">.<?php echo e($baseUrlWithoutScheme); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-4 customDomain" style="display: none">
                                        <label for="school_domain"><?php echo e(__('custom_domain')); ?></label>
                                        <div class="input-group mb-3">
                                                <input type="text" class="form-control domain-pattern" name="domain" placeholder="<?php echo e(__('domain')); ?>" aria-label="Recipient's username" aria-describedby="basic-addon2" disabled value="<?php echo e(($domain_type ==  "custom" && $settings['domain']) ? $settings['domain'] : ""); ?>">
                                        </div>
                                    </div>
                                    <?php if(!env('DEMO_MODE')): ?>
                                        <div class="form-group col-sm-12 col-md-4 serverinfo" style="display: none">
                                            <label for="serinfo"><?php echo e(__('server_info')); ?></label>
                                            <div class="input-group mb-3">
                                                    <input type="text" class="form-control" name="server_ip" placeholder="<?php echo e(__('domain')); ?>" aria-describedby="basic-addon2" disabled value="<?php echo e($_SERVER['SERVER_ADDR'] ?? '127.0.0.1'); ?>">
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="mx-4 text-justify text-uppercase">
                                        <small
                                            class="text-danger"><?php echo e(__('Note : If You are using Custom Domain then you have add a dns entry with pointing the server ip address.')); ?></small>
                                    </div>
                                </div>
                            </div>
                            <div class="border border-secondary rounded-lg my-4 mx-1">
                                <div class="col-md-12 mt-3"><h4><?php echo e(__("Roll Number Settings")); ?></h4></div>
                                <div class="col-12 mb-3">
                                    <hr class="mt-0">
                                </div>
                                <div class="form-group col-md-12 col-sm-12">
                                    <label for="roll-number-order"><?php echo e(__("Roll Number Sorting")); ?></label>
                                    <input type="hidden" id="roll-number-sort-column" name="roll_number_sort_column" value="<?php echo e($settings['roll_number_sort_column'] ?? ""); ?>">
                                    <input type="hidden" id="roll-number-sort-order" name="roll_number_sort_order" value="<?php echo e($settings['roll_number_sort_order'] ?? ""); ?>">
                                    <select name="" id="roll-number-order" class="form-control" required>
                                        <option value="" hidden="">-- <?php echo e(__('Select')); ?> --</option>
                                        <option value="first_name,asc"><?php echo e(__("First Name - Ascending")); ?></option>
                                        <option value="first_name,desc"><?php echo e(__("First Name - Descending")); ?></option>
                                        <option value="last_name,asc"><?php echo e(__("Last Name - Ascending")); ?></option>
                                        <option value="last_name,desc"><?php echo e(__("Last Name - Descending")); ?></option>
                                    </select>

                                    <div class="form-check">
                                        <label class="form-check-label"> <input type="checkbox" class="form-check-input" name="change_roll_number" id="change-roll-ckh-settings" value="1"> <?php echo e(__('Change Roll Number for All Classes')); ?> <i class="input-helper"></i></label>
                                    </div>
                                </div>
                            </div>

















                            
                            <input class="btn btn-theme float-right ml-3" id="create-btn" type="submit" value=<?php echo e(__('submit')); ?>>
                            <input class="btn btn-secondary float-right" type="reset" value=<?php echo e(__('reset')); ?>>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script>
    $(document).ready(function () {
        function toggleFields() {
            if ($('.default').is(':checked')) {
                $('.defaultDomain').show().find('input').prop('disabled', false);
                $('.customDomain').hide().find('input').prop('disabled', true);
                $('.serverinfo').hide().find('input');
            } else if ($('.custom').is(':checked')) {
                $('.customDomain').show().find('input').prop('disabled', false);
                $('.serverinfo').show().find('input');
                $('.defaultDomain').hide().find('input').prop('disabled', true);
            }
        }  
        $("input[name='domain_type']").on('change', toggleFields);

        toggleFields();
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/school-settings/general-settings.blade.php ENDPATH**/ ?>