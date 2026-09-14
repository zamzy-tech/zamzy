<?php $__env->startSection('title'); ?>
    <?php echo e(__('Fees Type')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('Manage Fees Type')); ?>

            </h3>
        </div>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            <?php echo e(__('Create Fees Type')); ?>

                        </h4>
                        <form id="create-form" class="pt-3 create-form" action="<?php echo e(url('fees-type')); ?>" method="POST" novalidate="novalidate">
                            <?php echo csrf_field(); ?>
                            <div class="row">
                                <div class="form-group col-md-12 col-lg-6">
                                    <label><?php echo e(__('name')); ?> <span class="text-danger">*</span></label>
                                    <?php echo Form::text('name', null, ['required', 'placeholder' => __('name'), 'class' => 'form-control']); ?>

                                </div>

                                <div class="form-group col-md-12 col-lg-6">
                                    <label><?php echo e(__('description')); ?> </label>
                                    <?php echo Form::textarea('description', null, ['placeholder' => __('description'), 'class' => 'form-control']); ?>

                                </div>
                            </div>
                            
                            <input class="btn btn-theme float-right ml-3" id="create-btn" type="submit" value=<?php echo e(__('submit')); ?>>
                                <input class="btn btn-secondary float-right" type="reset" value=<?php echo e(__('reset')); ?>>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            <?php echo e(__('List Fees')); ?>

                        </h4>
                        <div class="row">
                            <div class="col-12 text-right">
                                <b><a href="#" class="table-list-type active mr-2" data-id="0"><?php echo e(__('all')); ?></a></b> | <a href="#" class="ml-2 table-list-type" data-id="1"><?php echo e(__("Trashed")); ?></a>
                            </div>
                            <div class="col-12">
                                <table aria-describedby="mydesc" class='table' id='table_list'
                                       data-toggle="table" data-url="<?php echo e(route('fees-type.show',1)); ?>" data-click-to-select="true"
                                       data-side-pagination="server" data-pagination="true"
                                       data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-toolbar="#toolbar"
                                       data-show-columns="true" data-show-refresh="true" data-fixed-columns="false"
                                       data-fixed-number="2" data-fixed-right-number="1" data-trim-on-search="false"
                                       data-mobile-responsive="true" data-sort-name="id" data-sort-order="desc"
                                       data-maintain-selected="true" data-export-data-type='all' data-show-export="true"
                                       data-query-params="queryParams" data-escape="true">
                                    <thead>
                                    <tr>
                                        <th scope="col" data-field="id" data-sortable="true" data-visible="false"><?php echo e(__('id')); ?></th>
                                        <th scope="col" data-field="no"><?php echo e(__('no.')); ?></th>
                                        <th scope="col" data-field="name" data-sortable="true"><?php echo e(__('name')); ?></th>
                                        <th scope="col" data-field="description" data-sortable="true"><?php echo e(__('description')); ?></th>
                                        <th scope="col" data-events="FeesTypeActionEvents" data-field="operate" data-escape="false"><?php echo e(__('action')); ?></th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><?php echo e(__('Edit Fees Type')); ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"><i class="fa fa-close"></i></span>
                        </button>
                    </div>
                    <form id="edit-form" class="pt-3 edit-form" action="<?php echo e(url('fees-type')); ?>">
                        <input type="hidden" name="edit_id" id="edit_id">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="name"><?php echo e(__('name')); ?> <span class="text-danger">*</span></label>
                                <?php echo Form::text('edit_name',null,['required','class' => 'form-control edit_name','id' => 'edit_name','placeholder' => __('name')]); ?>

                            </div>

                            <div class="form-group">
                                <label for="name"><?php echo e(__('description')); ?> </label>
                                <?php echo Form::textarea('edit_description',null,array('class'=>'form-control edit_description','id'=>'edit_description','placeholder'=>__('description'))); ?>

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

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/fees/fees_types.blade.php ENDPATH**/ ?>