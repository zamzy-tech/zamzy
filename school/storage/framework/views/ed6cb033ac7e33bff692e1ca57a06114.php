<?php $__env->startSection('title'); ?>
    <?php echo e(__('section')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('manage').' '.__('section')); ?>

            </h3>
        </div>
        <div class="row">
            <div class="col-md-6 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            <?php echo e(__('create').' '.__('section')); ?>

                        </h4>
                        <form class="pt-3 section-create-form" id="create-form" action="<?php echo e(route('section.store')); ?>" method="POST" novalidate="novalidate">
                            <div class="row">
                                <div class="form-group col-sm-6 col-md-12">
                                    <label><?php echo e(__('name')); ?> <span class="text-danger">*</span></label>
                                    <input name="name" type="text" placeholder="<?php echo e(__('name')); ?>" class="form-control" required/>
                                </div>
                            </div>
                            <input class="btn btn-theme float-right" id="create-btn" type="submit" value=<?php echo e(__('submit')); ?>>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            <?php echo e(__('list').' '.__('section')); ?>

                        </h4>
                        <div class="col-12 text-right">
                            <b><a href="#" class="table-list-type active mr-2" data-id="0"><?php echo e(__('all')); ?></a></b> | <a href="#" class="ml-2 table-list-type" data-id="1"><?php echo e(__("Trashed")); ?></a>
                        </div>
                        <table aria-describedby="mydesc" class='table' id='table_list'
                               data-toggle="table" data-url="<?php echo e(route('section.show',[1])); ?>"
                               data-click-to-select="true" data-side-pagination="server"
                               data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                               data-search="true" data-toolbar="#toolbar" data-show-columns="true"
                               data-show-refresh="true" data-fixed-columns="false" data-fixed-number="2"
                               data-fixed-right-number="1" data-trim-on-search="false"
                               data-mobile-responsive="true" data-sort-name="id"
                               data-sort-order="desc" data-maintain-selected="true"
                               data-query-params="queryParams" data-show-export="true"
                               data-export-options='{"fileName": "section-list-<?= date('d-m-y') ?>","ignoreColumn": ["operate"]}'
                               data-escape="true">
                            <thead>
                            <tr>
                                <th scope="col" data-field="id" data-sortable="true" data-visible="false"><?php echo e(__('id')); ?></th>
                                <th scope="col" data-field="no"><?php echo e(__('no.')); ?></th>
                                <th scope="col" data-field="name"><?php echo e(__('name')); ?></th>
                                <th scope="col" data-field="created_at" data-formatter="dateTimeFormatter" data-sortable="true" data-visible="false"><?php echo e(__('created_at')); ?></th>
                                <th scope="col" data-field="updated_at" data-formatter="dateTimeFormatter" data-sortable="true" data-visible="false"><?php echo e(__('updated_at')); ?></th>
                                <th scope="col" data-field="operate" data-events="sectionEvents" data-escape="false"><?php echo e(__('action')); ?></th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel"><?php echo e(__('edit').' '.__('section')); ?></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form class="pt-3 section-edit-form" id="edit-form" action="<?php echo e(url('section')); ?>" novalidate="novalidate">
                            <input type="hidden" name="edit_id" id="edit_id" value=""/>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="form-group col-sm-6 col-md-12">
                                        <label><?php echo e(__('name')); ?> <span class="text-danger">*</span></label>
                                        <input name="name" id="edit_name" type="text" placeholder="<?php echo e(__('name')); ?>" class="form-control" required/>
                                    </div>
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
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/section/index.blade.php ENDPATH**/ ?>