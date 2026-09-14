<?php $__env->startSection('title'); ?>
    <?php echo e(__('expense_category')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('manage') . ' ' . __('expense_category')); ?>

            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            <?php echo e(__('create') . ' ' . __('expense_category')); ?>

                        </h4>
                        <form class="pt-3" id="create-form" action="<?php echo e(route('expense-category.store')); ?>" method="POST" novalidate="novalidate" enctype="multipart/form-data">
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-6">
                                    <label for="name"><?php echo e(__('name')); ?> <span class="text-danger">*</span></label>
                                    <input name="name" id="name" type="text" required placeholder="<?php echo e(__('name')); ?>" class="form-control"/>
                                </div>

                                <div class="form-group col-sm-12 col-md-6">
                                    <label for="description"><?php echo e(__('description')); ?> </label>
                                    <textarea name="description" id="description" placeholder="<?php echo e(__('description')); ?>" class="form-control"></textarea>
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
                        <h4 class="card-title"><?php echo e(__('list') . ' ' . __('expense_category')); ?></h4>

                        <div class="col-12 text-right">
                            <b><a href="#" class="table-list-type active mr-2" data-id="0"><?php echo e(__('all')); ?></a></b> | <a href="#" class="ml-2 table-list-type" data-id="1"><?php echo e(__("Trashed")); ?></a>
                        </div>
                        <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                               data-url="<?php echo e(route('expense-category.show',[1])); ?>" data-click-to-select="true"
                               data-side-pagination="server" data-pagination="true"
                               data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true"
                               data-show-refresh="true" data-fixed-columns="false" data-fixed-number="2"
                               data-fixed-right-number="1" data-trim-on-search="false" data-mobile-responsive="true"
                               data-sort-name="id" data-sort-order="desc" data-maintain-selected="true"
                               data-export-data-type='all' data-query-params="queryParams"
                               data-toolbar="#toolbar" data-export-options='{ "fileName": "expense-category-list-<?= date('d-m-y') ?>" ,"ignoreColumn":["operate"]}'
                               data-show-export="true" data-escape="true">
                            <thead>
                            <tr>
                                <th scope="col" data-field="id" data-sortable="true" data-visible="false"><?php echo e(__('id')); ?></th>
                                <th scope="col" data-field="no"><?php echo e(__('no.')); ?></th>
                                <th scope="col" data-field="name" data-sortable="true"><?php echo e(__('name')); ?></th>
                                <th scope="col" data-field="description" data-sortable="true"><?php echo e(__('description')); ?></th>
                                <th scope="col" data-field="operate" data-events="expenseCategoryEvents" data-escape="false"><?php echo e(__('action')); ?></th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel"><?php echo e(__('edit') . ' ' . __('expense_category')); ?></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form class="pt-3 edit-form" id="" action="<?php echo e(url('expense-category')); ?>"
                              novalidate="novalidate">
                            <?php echo csrf_field(); ?>
                            <div class="modal-body">
                                <input type="hidden" name="id" id="edit_id" value=""/>

                                <div class="form-group">
                                    <label for="edit_name"><?php echo e(__('name')); ?> <span class="text-danger">*</span></label>
                                    <input name="name" required id="edit_name" type="text" placeholder="<?php echo e(__('name')); ?>" class="form-control"/>
                                </div>

                                <div class="form-group">
                                    <label for="edit_description"><?php echo e(__('description')); ?></label>
                                    <textarea name="description" id="edit_description" class="form-control"></textarea>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo e(__('close')); ?></button>
                                    <input class="btn btn-theme" type="submit" value="<?php echo e(__('submit')); ?>"/>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/expense/category.blade.php ENDPATH**/ ?>