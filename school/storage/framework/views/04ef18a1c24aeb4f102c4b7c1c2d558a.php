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
                        <form id="formdata" class="create-form-without-reset" action="<?php echo e(route('system-settings.store')); ?>" method="POST" novalidate="novalidate" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <?php echo $__env->make('settings.forms.system-settings-form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                            
                            <input class="btn btn-theme float-right ml-3" id="create-btn" type="submit" value=<?php echo e(__('submit')); ?>>
                            <input class="btn btn-secondary float-right" type="reset" value=<?php echo e(__('reset')); ?>>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
         $(document).ready(function () {
            // set web_maintenance setting from database
             $("#web_maintenance").prop( "checked", false );
             if ($('#web_maintenance').val() == 1) {
                 $("#web_maintenance").prop( "checked", true );
             } else { 
                 $("#web_maintenance").prop( "checked", false );
             }
             
             $(document).on('change', '#web_maintenance', function () {
                 if ($('#web_maintenance').val() == 1) {
                     $('#web_maintenance').val(0);
                     $('#txt_web_maintenance').val(0);
                 } else {
                     $('#web_maintenance').val(1);
                     $('#txt_web_maintenance').val(1);
                 }
             });

             // Initialize two_factor_verification state
            if ($('#two_factor_verification').is(':checked')) {
                $('#txt_two_factor_verification').val(1);
                $('#two_factor_verification').prop('checked', true);
            } else {
                $('#txt_two_factor_verification').val(0);
                $('#two_factor_verification').prop('checked', false);
            }

            $(document).on('change', '#two_factor_verification', function () {
                if ($('#two_factor_verification').is(':checked')) {
                    $('#txt_two_factor_verification').val(1);
                    $('#two_factor_verification').prop('checked', true);
                } else {
                    $('#txt_two_factor_verification').val(0);
                    $('#two_factor_verification').prop('checked', false);
                }
            });
             
             $(document).on('change', '#file_upload_size_limit', function () {                
                $('#txt_file_upload_size_limit').val($('#file_upload_size_limit').val());
             });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/settings/system-settings.blade.php ENDPATH**/ ?>