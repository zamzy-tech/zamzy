<?php $__env->startSection('title'); ?>
    <?php echo e(__('role_management')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('role_management')); ?>

            </h3>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('role-create')): ?>
                <a class="btn btn-sm btn-theme" href="<?php echo e(route('roles.create')); ?>"> <?php echo e(__('Create New Role')); ?></a>
            <?php endif; ?>
        </div>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('role-list')): ?>
            <div class="row grid-margin">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                                   data-url="<?php echo e(route('roles.list')); ?>" data-click-to-select="true" data-side-pagination="server"
                                   data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                   data-show-columns="true" data-show-refresh="true" data-fixed-columns="false"
                                   data-fixed-number="2" data-fixed-right-number="1" data-trim-on-search="false"
                                   data-mobile-responsive="true" data-sort-name="id" data-toolbar="#toolbar" data-sort-order="desc"
                                   data-maintain-selected="true" data-export-data-type='all'
                                   data-export-options='{ "fileName": "roles-list-<?= date('d-m-y') ?>" ,"ignoreColumn":["operate"]}'
                                   data-show-export="true" data-query-params="queryParams" data-escape="true">
                                <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true" data-visible="false"><?php echo e(__('id')); ?></th>
                                    <th scope="col" data-field="no"><?php echo e(__('no.')); ?></th>
                                    <th scope="col" data-field="name" data-sortable="true"><?php echo e(__('name')); ?></th>
                                    <th scope="col" data-field="operate" data-escape="false"><?php echo e(__('action')); ?></th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/roles/index.blade.php ENDPATH**/ ?>