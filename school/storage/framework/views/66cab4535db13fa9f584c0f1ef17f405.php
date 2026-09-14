<?php $__env->startSection('title'); ?>
    <?php echo e(__('Create New Role')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('Create New Role')); ?>

            </h3>
        </div>
        <div class="row grid-margin">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-end">
                            <a class="btn btn-sm btn-theme" href="<?php echo e(route('roles.index')); ?>"> <?php echo e(__('back')); ?></a>
                        </div>

                        <div class="row">
                            <?php echo Form::open(['route' => 'roles.store', 'method' => 'POST']); ?>

                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <label><?php echo e(__('name')); ?> <span class="text-danger">*</span></label>
                                        <?php echo Form::text('name', null, ['placeholder' => 'Name', 'class' => 'form-control']); ?>

                                    </div>
                                </div>
                                <div class="form-group col-lg-3 col-sm-12 col-xs-12 col-md-3">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <?php echo e(Form::checkbox('selectall', 1, false, ['class' => 'name form-check-input', 'id' => 'selectall'])); ?>Select all
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-12">
                                    <label><strong><?php echo e(__('permission')); ?>:</strong></label>
                                    <div class="row mt-4">
                                        <?php
                                            $groupedPermissions = $permission->groupBy(function ($item) {
                                                return explode('-', $item->name)[0];
                                            });
                                        ?>
                                
                                        <?php $__currentLoopData = $groupedPermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $permissions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="col-sm-12 col-md-12 mb-4">
                                                <div class="form-check">
                                                    <label class="form-check-label" for="checkbox-<?php echo e($group); ?>">
                                                        <strong><?php echo e(ucfirst($group)); ?></strong>
                                                    <input 
                                                        type="checkbox" 
                                                        class="form-check-input parent-checkbox" 
                                                        id="checkbox-<?php echo e($group); ?>" 
                                                        data-group="<?php echo e($group); ?>" 
                                                        onchange="togglePermissions(this)">
                                                   
                                                        
                                                    </label>
                                                </div>
                                
                                                <div class="row mt-2">
                                                    <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div class="form-group col-lg-3 col-sm-12 col-xs-12 col-md-3">
                                                            <div class="form-check">
                                                                <label class="form-check-label">
                                                                    <?php echo e(Form::checkbox('permission[]', $value->id, false, ['class' => 'form-check-input child-checkbox', 'data-group' => $group, 'onchange' => 'updateParentCheckboxState("' . $group . '")'])); ?>

                                                                    <?php echo e($value->name); ?>

                                                                </label>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                                <hr>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-12">
                                    
                                    <input class="btn btn-theme float-right ml-3" id="create-btn" type="submit" value=<?php echo e(__('submit')); ?>>
                                    <input class="btn btn-secondary float-right" type="reset" value=<?php echo e(__('reset')); ?>>
                                </div>
                            </div>
                            <?php echo Form::close(); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script>
        $(document).ready(function () {
        $('#selectall').prop("checked", false);
        $('.parent-checkbox').prop("checked", false);
        $('.child-checkbox').prop("checked", false);

        // Ensure parent checkbox states are updated after checking all checkboxes
        $('.parent-checkbox').each(function () {
            updateParentCheckboxState($(this).data('group'));
        });

        // Handle "Select All" checkbox functionality
        $('#selectall').click(function () {
            const isChecked = this.checked;
            // Select or deselect all checkboxes (both parent and child)
            $('.parent-checkbox').prop('checked', isChecked);
            $('.child-checkbox').prop('checked', isChecked);

            // Trigger change event to ensure parent checkboxes are updated properly
            $('.parent-checkbox').each(function () {
                updateParentCheckboxState($(this).data('group'));
            });
        });

        // Function to handle change event for individual child checkboxes
        $('.selectedId').change(function () {
            updateSelectAllState();
        });

        // Function to handle change event for parent checkboxes
        $('.parent-checkbox').change(function () {
            updateSelectAllState();
        });

        // Update the state of the "Select All" checkbox
        function updateSelectAllState() {
            var allSelected = $('.parent-checkbox').length === $('.parent-checkbox:checked').length;

            $('#selectall').prop('checked', allSelected);
        }
    });

    // Function to handle the parent checkbox click
    function togglePermissions(checkbox) {
        const group = checkbox.dataset.group;
        const childCheckboxes = document.querySelectorAll(`.child-checkbox[data-group="${group}"]`);
        
        childCheckboxes.forEach(childCheckbox => {
            childCheckbox.checked = checkbox.checked;
        });

        // Update parent checkbox state
        updateParentCheckboxState(group);
    }

    // Function to update the parent checkbox state based on children
    function updateParentCheckboxState(group) {
        const parentCheckbox = document.querySelector(`#checkbox-${group}`);
        const childCheckboxes = document.querySelectorAll(`.child-checkbox[data-group="${group}"]`);
        
        const allChecked = Array.from(childCheckboxes).every(checkbox => checkbox.checked);
        const someChecked = Array.from(childCheckboxes).some(checkbox => checkbox.checked);
        
        parentCheckbox.checked = allChecked;
        parentCheckbox.indeterminate = !allChecked && someChecked;
    }

    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/roles/create.blade.php ENDPATH**/ ?>