<div id="student_tags">
    <a data-value="{full_name}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('full_name')); ?> }</a>
    <a data-value="{first_name}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('first_name')); ?> }</a>
    <a data-value="{last_name}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('last_name')); ?> }</a>
    <a data-value="{class_section}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('class_section')); ?> }</a>
    <a data-value="{student_mobile}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('student_mobile')); ?> }</a>
    <a data-value="{dob}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('dob')); ?> }</a>
    <a data-value="{roll_no}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('roll_no')); ?> }</a>
    <a data-value="{admission_no}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('admission_no')); ?> }</a>
    <a data-value="{current_address}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('current_address')); ?> }</a>
    <a data-value="{permanent_address}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('permanent_address')); ?> }</a>
    <a data-value="{gender}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('gender')); ?> }</a>
    <a data-value="{admission_date}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('admission_date')); ?> }</a>
    <a data-value="{guardian_name}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('guardian_name')); ?> }</a>
    <a data-value="{guardian_mobile}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('guardian_mobile')); ?> }</a>
    <a data-value="{guardian_email}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('guardian_email')); ?> }</a>
    <a data-value="{exam}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('exam')); ?> }</a>
    <a data-value="{total_marks}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('total_marks')); ?> }</a>
    <a data-value="{obtain_marks}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('obtain_marks')); ?> }</a>
    <a data-value="{grade}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('grade')); ?> }</a>
    <a data-value="{session_year}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('session_year')); ?> }</a>

    <?php $__currentLoopData = $formFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $formField): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($formField->user_type == 1): ?> <!-- 1 => Student -->
            <a data-value="<?php echo e('{'.$formField->name.'}'); ?>" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__($formField->name)); ?> }</a>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<div id="staff_tags">
    <a data-value="{full_name}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('full_name')); ?> }</a>
    <a data-value="{first_name}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('first_name')); ?> }</a>
    <a data-value="{last_name}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('last_name')); ?> }</a>
    <a data-value="{gender}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('gender')); ?> }</a>
    <a data-value="{joining_date}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('joining_date')); ?> }</a>
    <a data-value="{role}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('role')); ?> }</a>
    <a data-value="{qualification}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('qualification')); ?> }</a>
    <a data-value="{dob}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('dob')); ?> }</a>
    <a data-value="{email}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('email')); ?> }</a>
    <a data-value="{mobile}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('mobile')); ?> }</a>
    <a data-value="{current_address}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('current_address')); ?> }</a>
    <a data-value="{permanent_address}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('permanent_address')); ?> }</a>
    <a data-value="{experience}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('experience')); ?> }</a>
    <a data-value="{session_year}" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__('session_year')); ?> }</a>
    
    <?php $__currentLoopData = $formFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $formField): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($formField->user_type == 2): ?> <!-- 2 => Staff -->
            <a data-value="<?php echo e('{'.$formField->name.'}'); ?>" class="btn btn-gradient-light btn_tag mt-2">{ <?php echo e(__($formField->name)); ?> }</a>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div><?php /**PATH /home/shacartc/school.tehub.in/resources/views/certificate/tags.blade.php ENDPATH**/ ?>