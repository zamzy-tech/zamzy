<?php $__env->startSection('title'); ?>
    <?php echo e(__('students')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('manage') . ' ' . __('students')); ?>

            </h3>
        </div>

        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            <?php echo e(__('create') . ' ' . __('students')); ?>

                        </h4>
                        <form class="pt-3 student-registration-form" id="create-form" data-success-function="formSuccessFunction" enctype="multipart/form-data" action="<?php echo e(route('students.store')); ?>" method="POST" novalidate="novalidate">
                            <?php echo csrf_field(); ?>
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-3">
                                    <label>Admission Number <span class="text-danger">*</span></label>
                                    <?php echo Form::text('admission_no', $admission_no, ['placeholder' => 'Admission Number', 'class' => 'form-control']); ?>

                                </div>

                                <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-3">
                                    <label for="class_section"><?php echo e(__('class_section')); ?> <span class="text-danger">*</span></label>
                                    <select name="class_section_id" id="class_section" class="form-control select2">
                                        <option value=""><?php echo e(__('select') . ' ' . __('Class') . ' ' . __('section')); ?></option>
                                        <?php if(count($class_sections)): ?>
                                            <?php $__currentLoopData = $class_sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class_section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($class_section->id); ?>"><?php echo e($class_section->full_name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-3">
                                    <label for="session_year_id"><?php echo e(__('session_year')); ?> <span class="text-danger">*</span></label>
                                    <select name="session_year_id" id="session_year_id" class="form-control select2">
                                        <?php if(count($sessionYears)): ?>
                                            <?php $__currentLoopData = $sessionYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($year->id); ?>" <?php echo e($year->default==1 ? "selected" : ""); ?>><?php echo e($year->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-3">
                                    <label><?php echo e(__('admission_date')); ?> <span class="text-danger">*</span></label>
                                    <?php echo Form::text('admission_date', null, ['placeholder' => __('admission_date'), 'class' => 'datepicker-popup-no-future form-control','id'=>'admission_date','autocomplete'=>'off']); ?>

                                    <span class="input-group-addon input-group-append">
                                    </span>
                                </div>

                                <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-3">
                                    <label><?php echo e(__('PEN NO')); ?></label>
                                    <?php echo Form::text('pen_no', null, ['placeholder' => __('PEN NO'), 'class' => 'form-control', 'id' => 'pen_no']); ?>

                                </div>

                                <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-3">
                                    <label>Previous Year Balance</label>
                                    <?php echo Form::number('previous_year_balance', 0, ['placeholder' => 'Previous Year Balance', 'class' => 'form-control', 'step' => '0.01', 'min' => '0']); ?>

                                </div>


                                <?php if(!empty($features) ): ?>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                        <label><?php echo e(__('Status')); ?> <span class="text-danger">*</span></label><br>
                                        <div class="d-flex">
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    <?php echo Form::radio('status', 1, true); ?>

                                                    <?php echo e(__('Active')); ?>

                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    <?php echo Form::radio('status', 0); ?>

                                                    <?php echo e(__('Inactive')); ?>

                                                </label>
                                            </div>
                                        </div>
                                        <span class="text-danger small"><?php echo e(__('Note').':-'.__('Activating this will consider in your current subscription cycle')); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <hr>
                             <div class="row mt-3 mb-3">
                                 <div class="col-md-12">
                                     <h4 class="card-title text-info">Fee Settings</h4>
                                 </div>
                                 <div class="form-group col-sm-12 col-md-3">
                                     <label class="d-block font-weight-bold">Class Fee Selection</label>
                                     <div class="form-check form-check-inline">
                                         <label class="form-check-label">
                                             <input type="checkbox" name="apply_class_fee" id="apply_class_fee" value="1" checked> Apply Class Fee <span id="class_fee_amount" style="font-weight: bold; color: #3085d6;">(₹ 0)</span>
                                         </label>
                                     </div>
                                 </div>
                                 <div class="form-group col-sm-12 col-md-5">
                                     <label class="d-block font-weight-bold">Van Fee Selection</label>
                                     <div class="d-flex align-items-center" style="height: 38px;">
                                         <div class="form-check form-check-inline mr-4">
                                             <label class="form-check-label">
                                                 <input type="radio" name="apply_van_fee" value="0" checked id="van_none"> No Van
                                             </label>
                                         </div>
                                         <div class="form-check form-check-inline mr-4">
                                             <label class="form-check-label">
                                                 <input type="radio" name="apply_van_fee" value="1" id="van_1"> Van 1 (₹ 9,600)
                                             </label>
                                         </div>
                                         <div class="form-check form-check-inline">
                                             <label class="form-check-label">
                                                 <input type="radio" name="apply_van_fee" value="2" id="van_2"> Van 2 (₹ 12,000)
                                             </label>
                                         </div>
                                     </div>
                                 </div>
                                  <div class="form-group col-sm-12 col-md-4">
                                      <label class="d-block font-weight-bold">Amount Paid (Optional)</label>
                                      <input type="number" name="admission_amount_paid" id="admission_amount_paid" class="form-control" placeholder="Enter Amount Paid on Admission" min="0" step="any">
                                  </div>
                                  <div class="form-group col-sm-12 col-md-3">
                                      <label class="d-block font-weight-bold">Admission Fee</label>
                                      <div class="form-check form-check-inline">
                                          <label class="form-check-label">
                                              <input type="checkbox" name="apply_admission_fee" id="apply_admission_fee" value="1"> Admission Fee <span style="font-weight: bold; color: #e74c3c;">(₹ 2,000)</span>
                                          </label>
                                      </div>
                                  </div>
                                  <div class="form-group col-sm-12 col-md-4">
                                      <label class="d-block font-weight-bold">Payment Package (EMI Option)</label>
                                      <select name="payment_package" id="payment_package" class="form-control">
                                          <option value="">Full Payment</option>
                                          <option value="yearly">Yearly Pay (1 Installment)</option>
                                          <option value="half_yearly">Half Yearly (2 Installments)</option>
                                          <option value="quarterly">Quarterly (3 Installments)</option>
                                          <option value="monthly">Monthly (11 Installments)</option>
                                      </select>
                                  </div>
                             </div>

                             <div class="row mt-3 mb-4" id="fee_summary_box" style="display:none;">
                                 <div class="col-md-6 col-sm-12">
                                     <div class="card bg-light p-3 border">
                                         <h5 class="text-secondary mb-3">Total Fees Summary</h5>
                                         <div class="d-flex justify-content-between mb-2">
                                             <span>Class Fee:</span>
                                             <span id="preview_class_fee" class="font-weight-bold">₹ 0</span>
                                         </div>
                                         <div class="d-flex justify-content-between mb-2">
                                             <span>Van Fee:</span>
                                             <span id="preview_van_fee" class="font-weight-bold">₹ 0</span>
                                         </div>
                                         <div class="d-flex justify-content-between mb-2">
                                             <span>Admission Fee:</span>
                                             <span id="preview_admission_fee" class="font-weight-bold">₹ 0</span>
                                         </div>
                                         <hr class="my-2">
                                         <div class="d-flex justify-content-between mb-2">
                                             <span class="h6 mb-0 font-weight-bold">Total Due Amount:</span>
                                             <span id="preview_total_fee" class="h6 mb-0 font-weight-bold text-success">₹ 0</span>
                                         </div>
                                         <div id="preview_initial_payment_row" class="d-flex justify-content-between mb-2" style="display:none !important;">
                                             <span>Initial Payment Paid:</span>
                                             <span id="preview_initial_payment" class="font-weight-bold text-dark">₹ 0</span>
                                         </div>
                                         <div id="preview_remaining_row" class="d-flex justify-content-between mb-2" style="display:none !important;">
                                             <span>Remaining Balance:</span>
                                             <span id="preview_remaining" class="font-weight-bold text-danger">₹ 0</span>
                                         </div>
                                         <div id="preview_installments_section" style="display:none;">
                                             <hr class="my-2">
                                             <h6 class="text-primary mb-2 font-weight-bold">Installment Schedule</h6>
                                             <div id="preview_installments_list" style="font-size: 0.9rem;">
                                                 <!-- Dynamic installments list -->
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                             
                             <hr>
                            <div class="row mt-5">
                                <div class="form-group col-sm-12 col-md-12 col-lg-8 col-xl-8">
                                    <label>Full Name <span class="text-danger">*</span></label>
                                    <?php echo Form::text('full_name', null, ['placeholder' => 'Full Name', 'class' => 'form-control']); ?>

                                </div>
                                <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                    <label><?php echo e(__('dob')); ?> <span class="text-danger">*</span></label>
                                    <?php echo Form::text('dob', null, ['placeholder' => __('dob'), 'class' => 'datepicker-popup-no-future form-control','autocomplete'=>'off']); ?>

                                    <span class="input-group-addon input-group-append">
                                    </span>
                                </div>

                                <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                    <label><?php echo e(__('gender')); ?> <span class="text-danger">*</span></label><br>
                                    <div class="d-flex">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <?php echo Form::radio('gender', 'male',true); ?>

                                                <?php echo e(__('male')); ?>

                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <?php echo Form::radio('gender', 'female'); ?>

                                                <?php echo e(__('female')); ?>

                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                    <label for="image"><?php echo e(__('image')); ?> </label>
                                    <input type="file" name="image" class="file-upload-default"/>
                                    <div class="input-group col-xs-12">
                                        <input type="text" id="image" class="form-control file-upload-info" disabled="" placeholder="<?php echo e(__('image')); ?>"/>
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme" type="button"><?php echo e(__('upload')); ?></button>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                    <label for="aadhar_pic"><?php echo e(__('aadhar_pic')); ?> </label>
                                    <input type="file" name="aadhar_pic" class="file-upload-default"/>
                                    <div class="input-group col-xs-12">
                                        <input type="text" id="aadhar_pic" class="form-control file-upload-info" disabled="" placeholder="<?php echo e(__('aadhar_pic')); ?>"/>
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme" type="button"><?php echo e(__('upload')); ?></button>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label><?php echo e(__('current_address')); ?> <span class="text-danger">*</span></label>
                                    <?php echo Form::textarea('current_address', null, ['required', 'placeholder' => __('current_address'), 'class' => 'form-control', 'rows' => 3]); ?>

                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label><?php echo e(__('permanent_address')); ?> <span class="text-danger">*</span></label>
                                    <?php echo Form::textarea('permanent_address', null, ['required', 'placeholder' => __('permanent_address'), 'class' => 'form-control', 'rows' => 3]); ?>

                                </div>
                            </div>

                            <?php if(count($extraFields)): ?>
                                <div class="row other-details">

                                    
                                    <?php $__currentLoopData = $extraFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            
                                            <?php echo e(Form::hidden('extra_fields['.$key.'][id]', '', ['id' => $data->type.'_'.$key.'_id'])); ?>


                                            
                                            <?php echo e(Form::hidden('extra_fields['.$key.'][form_field_id]', $data->id, ['id' => $data->type.'_'.$key.'_id'])); ?>


                                            <div class='form-group col-md-12 col-lg-6 col-xl-4 col-sm-12'>

                                                
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
                                                    <?php echo e(Form::select('extra_fields['.$key.'][data]',$data->default_values,null,
                                                        ['id' => $data->type.'_'.$key,'class' => 'form-control select-fields',
                                                            ($data->is_required == 1 ? 'required' : ''),
                                                            'placeholder' => 'Select '.$data->name
                                                        ]
                                                    )); ?>


                                                        
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
                                                            <div class="row col-lg-12 col-xl-6 col-md-12 col-sm-12">
                                                                <?php $__currentLoopData = $data->default_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chkKey => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <div class="mr-2 form-check">
                                                                        <label class="form-check-label">
                                                                            <?php echo e(Form::checkbox('extra_fields['.$key.'][data][]', $value, null, ['id' => $data->type.'_'.$chkKey, 'class' => 'form-check-input chkclass checkbox-fields',($data->is_required == 1 ? 'required' : '')])); ?> <?php echo e($value); ?>


                                                                        </label>
                                                                    </div>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </div>
                                                        <?php endif; ?>

                                                        
                                                    <?php elseif($data->type == 'textarea'): ?>
                                                        <?php echo e(Form::textarea('extra_fields['.$key.'][data]', '', ['placeholder' => $data->name, 'id' => $data->type.'_'.$key, 'class' => 'form-control textarea-fields', ($data->is_required ? 'required' : '') , 'rows' => 3])); ?>


                                                        
                                                    <?php elseif($data->type == 'file'): ?>
                                                        <div class="input-group col-xs-12">
                                                            <?php echo e(Form::file('extra_fields['.$key.'][data]', ['class' => 'file-upload-default', 'id' => $data->type.'_'.$key, ($data->is_required ? 'required' : '')])); ?>

                                                            <?php echo e(Form::text('', '', ['class' => 'form-control file-upload-info', 'disabled' => '', 'placeholder' => __('image')])); ?>

                                                            <span class="input-group-append">
                                                                <button class="file-upload-browse btn btn-theme" type="button"><?php echo e(__('upload')); ?></button>
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

                            <hr>
                            
                            <div class="row mt-5">
                                <div class="form-group col-sm-12 col-md-12">
                                    <label for="guardian_relation">Relation <span class="text-danger">*</span></label>
                                    <select name="guardian_relation" id="guardian_relation" class="form-control">
                                        <option value="father">Father</option>
                                        <option value="mother">Mother</option>
                                        <option value="father_mother">Father & Mother</option>
                                        <option value="guardian" selected>Guardian</option>
                                    </select>
                                </div>

                                <div class="form-group col-sm-12 col-md-12">
                                    <label for="guardian_email"><span class="guardian-relation-label">Guardian</span> Email</label>
                                    <select class="guardian-search form-control guardian_email" id="guardian_email"></select>
                                    <input type="hidden" id="guardian_email" class="guardian_email" name="guardian_email">
                                </div>

                                
                                <div class="row col-sm-12 col-md-12 p-0 m-0" id="single_guardian_fields">
                                    <div class="form-group col-sm-12 col-md-12 col-lg-8 col-xl-8">
                                        <label><span class="guardian-relation-label">Guardian</span> Full Name <span class="text-danger">*</span></label>
                                        <?php echo Form::text('guardian_full_name', null, ['placeholder' => __('guardian') . ' ' . __('Full Name'), 'class' => 'form-control', 'id' => 'guardian_full_name']); ?>

                                    </div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-4 col-xl-4">
                                        <label><span class="guardian-relation-label">Guardian</span> <?php echo e(__('mobile')); ?></label>
                                        <?php echo Form::number('guardian_mobile', null, ['placeholder' => __('guardian') . ' ' . __('mobile'), 'class' => 'form-control remove-number-increment', 'id' => 'guardian_mobile','min' => 1 ]); ?>

                                    </div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                        <label><span class="guardian-relation-label">Guardian</span> Photo</label>
                                        <input type="file" name="guardian_image" class="file-upload-default" accept="image/*"/>
                                        <div class="input-group col-xs-12">
                                            <input type="text" class="form-control file-upload-info" disabled="" placeholder="Upload Guardian Photo"/>
                                            <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-theme" type="button"><?php echo e(__('upload')); ?></button>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                        <label><span class="guardian-relation-label">Guardian</span> Aadhar Image</label>
                                        <input type="file" name="guardian_aadhar_pic" class="file-upload-default" accept="image/*,application/pdf"/>
                                        <div class="input-group col-xs-12">
                                            <input type="text" class="form-control file-upload-info" disabled="" placeholder="Upload Guardian Aadhar Image"/>
                                            <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-theme" type="button"><?php echo e(__('upload')); ?></button>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-12">
                                        <label><?php echo e(__('gender')); ?> <span class="text-danger">*</span></label><br>
                                        <div class="d-flex">
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    <input type="radio" checked name="guardian_gender" value="male" id="guardian_male">
                                                    <?php echo e(__('male')); ?>

                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    <input type="radio" name="guardian_gender" value="female" id="guardian_female">
                                                    <?php echo e(__('female')); ?>

                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="row col-sm-12 col-md-12 p-0 m-0 d-none" id="double_parent_fields">
                                    <div class="col-md-12"><h4 class="card-title text-info">Father Details</h4></div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-8 col-xl-8">
                                        <label>Father Full Name <span class="text-danger">*</span></label>
                                        <?php echo Form::text('guardian_full_name', null, ['placeholder' => 'Father Full Name', 'class' => 'form-control', 'id' => 'father_full_name', 'disabled' => true]); ?>

                                    </div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-4 col-xl-4">
                                        <label>Father Mobile</label>
                                        <?php echo Form::number('guardian_mobile', null, ['placeholder' => 'Father Mobile', 'class' => 'form-control remove-number-increment', 'id' => 'father_mobile','min' => 1, 'disabled' => true]); ?>

                                    </div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                        <label>Father Photo</label>
                                        <input type="file" name="guardian_image" class="file-upload-default" accept="image/*" disabled true/>
                                        <div class="input-group col-xs-12">
                                            <input type="text" class="form-control file-upload-info" disabled="" placeholder="Upload Father Photo"/>
                                            <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-theme" type="button"><?php echo e(__('upload')); ?></button>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                        <label>Father Aadhar Image</label>
                                        <input type="file" name="guardian_aadhar_pic" class="file-upload-default" accept="image/*,application/pdf" disabled true/>
                                        <div class="input-group col-xs-12">
                                            <input type="text" class="form-control file-upload-info" disabled="" placeholder="Upload Father Aadhar Image"/>
                                            <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-theme" type="button"><?php echo e(__('upload')); ?></button>
                                            </span>
                                        </div>
                                    </div>
                                    <input type="hidden" name="guardian_gender" value="male" id="father_gender" disabled true>

                                    <div class="col-md-12 mt-4"><h4 class="card-title text-info">Mother Details</h4></div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-8 col-xl-8">
                                        <label>Mother Full Name <span class="text-danger">*</span></label>
                                        <?php echo Form::text('mother_name', null, ['placeholder' => 'Mother Full Name', 'class' => 'form-control', 'id' => 'mother_full_name', 'disabled' => true]); ?>

                                    </div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-4 col-xl-4">
                                        <label>Mother Mobile</label>
                                        <?php echo Form::number('mother_mobile', null, ['placeholder' => 'Mother Mobile', 'class' => 'form-control remove-number-increment', 'id' => 'mother_mobile','min' => 1, 'disabled' => true]); ?>

                                    </div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                        <label>Mother Photo</label>
                                        <input type="file" name="mother_image" class="file-upload-default" accept="image/*" disabled true/>
                                        <div class="input-group col-xs-12">
                                            <input type="text" class="form-control file-upload-info" disabled="" placeholder="Upload Mother Photo"/>
                                            <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-theme" type="button"><?php echo e(__('upload')); ?></button>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                        <label>Mother Aadhar Image</label>
                                        <input type="file" name="mother_aadhar_pic" class="file-upload-default" accept="image/*,application/pdf" disabled true/>
                                        <div class="input-group col-xs-12">
                                            <input type="text" class="form-control file-upload-info" disabled="" placeholder="Upload Mother Aadhar Image"/>
                                            <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-theme" type="button"><?php echo e(__('upload')); ?></button>
                                            </span>
                                        </div>
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
        function formSuccessFunction() {
            setTimeout(() => {
                window.location.reload()
            }, 3000);
        }

        $('#admission_date').datepicker({
            format: "dd-mm-yyyy",
            rtl: isRTL()
        }).datepicker("setDate", 'now');

        $(document).ready(function() {
            function updateGuardianLabels() {
                var relation = $('#guardian_relation').val();
                
                if (relation === 'father_mother') {
                    $('#single_guardian_fields').addClass('d-none');
                    $('#single_guardian_fields').find('input, select, textarea').prop('disabled', true);
                    
                    $('#double_parent_fields').removeClass('d-none');
                    $('#double_parent_fields').find('input, select, textarea').prop('disabled', false);
                    
                    $('.guardian-relation-label').text('Father & Mother');
                } else {
                    $('#double_parent_fields').addClass('d-none');
                    $('#double_parent_fields').find('input, select, textarea').prop('disabled', true);
                    
                    $('#single_guardian_fields').removeClass('d-none');
                    $('#single_guardian_fields').find('input, select, textarea').prop('disabled', false);
                    
                    var labelText = relation.charAt(0).toUpperCase() + relation.slice(1);
                    $('.guardian-relation-label').text(labelText);
                    
                    $('#guardian_full_name').attr('placeholder', labelText + ' Full Name');
                    $('#guardian_mobile').attr('placeholder', labelText + ' Mobile');
                    
                    if (relation === 'father') {
                        $('#guardian_female').prop('checked', false);
                        $('#guardian_male').prop('checked', true);
                    } else if (relation === 'mother') {
                        $('#guardian_male').prop('checked', false);
                        $('#guardian_female').prop('checked', true);
                    }
                }
            }
            
            $(document).on('change', '#guardian_relation', function() {
                updateGuardianLabels();
            });
            
            updateGuardianLabels();

            // Dynamic Fees Calculation Code
            const classSectionToClassMap = {
                <?php $__currentLoopData = $class_sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    "<?php echo e($cs->id); ?>": "<?php echo e($cs->class_id); ?>",
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            };
            
            const classFeesMap = {
                <?php $__currentLoopData = $classFees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classId => $amount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    "<?php echo e($classId); ?>": "<?php echo e($amount); ?>",
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            };

            function getInstallmentSchedule(packageType, remainingAmount) {
                let installments = [];
                if (packageType === 'yearly') {
                    installments.push({
                        name: 'Yearly Pay',
                        date: '30-06-2026',
                        amount: remainingAmount
                    });
                } else if (packageType === 'half_yearly') {
                    let part = Math.round((remainingAmount / 2) * 100) / 100;
                    installments.push({
                        name: '1st Half',
                        date: '30-06-2026',
                        amount: part
                    });
                    installments.push({
                        name: '2nd Half',
                        date: '30-11-2026',
                        amount: Math.round((remainingAmount - part) * 100) / 100
                    });
                } else if (packageType === 'quarterly') {
                    let part = Math.round((remainingAmount / 3) * 100) / 100;
                    installments.push({
                        name: '1st Quarter',
                        date: '30-06-2026',
                        amount: part
                    });
                    installments.push({
                        name: '2nd Quarter',
                        date: '15-09-2026',
                        amount: part
                    });
                    installments.push({
                        name: '3rd Quarter',
                        date: '31-12-2026',
                        amount: Math.round((remainingAmount - part * 2) * 100) / 100
                    });
                } else if (packageType === 'monthly') {
                    let part = Math.round((remainingAmount / 11) * 100) / 100;
                    let months = [
                        '05-07-2026', '05-08-2026', '05-09-2026', '05-10-2026', '05-11-2026',
                        '05-12-2026', '05-01-2027', '05-02-2027', '05-03-2027', '05-04-2027',
                        '05-05-2027'
                    ];
                    for (let i = 0; i < 11; i++) {
                        let amt = (i === 10) ? Math.round((remainingAmount - part * 10) * 100) / 100 : part;
                        installments.push({
                            name: 'Month ' + (i + 1),
                            date: months[i],
                            amount: amt
                        });
                    }
                }
                return installments;
            }

            function updateFeeSummary() {
                const csId = $('#class_section').val();
                const classId = classSectionToClassMap[csId];
                const rawClassFee = classId ? (classFeesMap[classId] || 0) : 0;
                
                if (rawClassFee > 0) {
                    $('#class_fee_amount').text('(₹ ' + parseFloat(rawClassFee).toLocaleString('en-IN') + ')');
                } else {
                    $('#class_fee_amount').text('(₹ 0)');
                }

                const applyClass = $('#apply_class_fee').is(':checked');
                const classFee = applyClass ? parseFloat(rawClassFee) : 0;
                
                const vanSelection = $('input[name="apply_van_fee"]:checked').val();
                let vanFee = 0;
                if (vanSelection == 1) {
                    vanFee = 9600;
                } else if (vanSelection == 2) {
                    vanFee = 12000;
                }

                const applyAdmission = $('#apply_admission_fee').is(':checked');
                const admissionFee = applyAdmission ? 2000 : 0;

                const totalDue = classFee + vanFee + admissionFee;
                const initialPayment = parseFloat($('#admission_amount_paid').val()) || 0;
                const remainingAmount = Math.max(0, totalDue - initialPayment);
                const paymentPackage = $('#payment_package').val();

                if (csId) {
                    $('#fee_summary_box').show();
                    $('#preview_class_fee').text('₹ ' + classFee.toLocaleString('en-IN'));
                    $('#preview_van_fee').text('₹ ' + vanFee.toLocaleString('en-IN'));
                    $('#preview_admission_fee').text('₹ ' + admissionFee.toLocaleString('en-IN'));
                    $('#preview_total_fee').text('₹ ' + totalDue.toLocaleString('en-IN'));

                    if (paymentPackage) {
                        $('#preview_initial_payment_row').attr('style', 'display: flex !important;');
                        $('#preview_initial_payment').text('₹ ' + initialPayment.toLocaleString('en-IN'));
                        
                        $('#preview_remaining_row').attr('style', 'display: flex !important;');
                        $('#preview_remaining').text('₹ ' + remainingAmount.toLocaleString('en-IN'));

                        $('#preview_installments_section').show();
                        let schedule = getInstallmentSchedule(paymentPackage, remainingAmount);
                        let html = '';
                        schedule.forEach(inst => {
                            html += `<div class="d-flex justify-content-between mb-1 text-muted">
                                <span>${inst.name} (${inst.date}):</span>
                                <span class="font-weight-bold">₹ ${inst.amount.toLocaleString('en-IN')}</span>
                            </div>`;
                        });
                        $('#preview_installments_list').html(html);
                    } else {
                        $('#preview_initial_payment_row').attr('style', 'display: none !important;');
                        $('#preview_remaining_row').attr('style', 'display: none !important;');
                        $('#preview_installments_section').hide();
                    }
                } else {
                    $('#fee_summary_box').hide();
                }
            }

            $(document).on('change input', '#class_section, #apply_class_fee, input[name="apply_van_fee"], #apply_admission_fee, #admission_amount_paid, #payment_package', function() {
                updateFeeSummary();
            });

            updateFeeSummary();
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/students/create.blade.php ENDPATH**/ ?>