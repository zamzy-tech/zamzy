<?php $__env->startSection('title'); ?>
    <?php echo e(__('package')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('manage') . ' ' . __('package')); ?>

            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title float-left">
                            <?php echo e(__('list') . ' ' . __('package')); ?>

                        </h4>
                        <div class="row">
                            <div class="col-sm-12 col-md-12 text-right">
                                <a href="<?php echo e(route('package.create')); ?>" class="btn btn-theme btn-sm"><?php echo e(__('create')); ?>

                                    <?php echo e(__('package')); ?></a>
                            </div>
                        </div>
                        <hr>
                        <ul class="text-danger">
                            <li>
                                <span><?php echo e(__('To Reorder the Package, Drag the Table Row Up and Down and then Click on Update Rank')); ?>.</span>
                            </li>
                        </ul>
                        <div class="row">
                            <div id="toolbar">
                                <div class="form-group col-sm-12 col-md-4">
                                    <label class="filter-menu"><?php echo e(__('type')); ?></label>
                                    <?php echo Form::select(
                                        'type',
                                        [
                                            '' => __('all'),
                                            '0' => __('prepaid'),
                                            '1' => __('postpaid'),
                                        ],
                                        null,
                                        ['class' => 'form-control', 'id' => 'type'],
                                    ); ?>

                                </div>
                            </div>
                            <div class="col-12 text-right mt-4">
                                <b><a href="#" class="table-list-type active mr-2" data-id="0"><?php echo e(__('all')); ?></a></b> | <a href="#" class="ml-2 table-list-type" data-id="1"><?php echo e(__('Trashed')); ?></a>
                            </div>
                            <div class="col-12">
                                <table aria-describedby="mydesc" class='table reorder-table-row' id='table_list'
                                       data-toggle="table" data-url="<?php echo e(route('package.show', 1)); ?>"
                                       data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                       data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-toolbar="#toolbar"
                                       data-show-columns="true" data-show-refresh="true" data-trim-on-search="false"
                                       data-mobile-responsive="true" data-sort-name="rank" data-use-row-attr-func="true"
                                       data-reorderable-rows="true" data-sort-order="asc" data-maintain-selected="true"
                                       data-export-data-type='all'
                                       data-export-options='{ "fileName": "<?php echo e(__('list') . ' ' . __('package')); ?>-<?= date('
                                    d-m-y') ?>" ,"ignoreColumn":["operate"]}' data-show-export="true"
                                       data-query-params="packageQueryParams" data-escape="true">
                                    <thead>
                                    <tr>
                                        <th scope="col" data-field="id" data-sortable="true" data-visible="false"><?php echo e(__('id')); ?></th>
                                        <th scope="col" data-field="no"><?php echo e(__('no.')); ?></th>
                                        <th scope="col" data-field="name"><?php echo e(__('name')); ?></th>
                                        <th scope="col" data-field="description"><?php echo e(__('description')); ?></th>
                                        <th scope="col" data-field="status" data-formatter="packageTypeFormatter"><?php echo e(__('type')); ?></th>
                                        <th scope="col" data-field="status" data-formatter="yesAndNoStatusFormatter"><?php echo e(__('published')); ?></th>
                                        <th scope="col" data-field="highlight" data-formatter="yesAndNoStatusFormatter"><?php echo e(__('highlight')); ?></th>
                                        <th scope="col" data-field="days"><?php echo e(__('days')); ?></th>
                                        <th scope="col" data-field="used_by"><?php echo e(__('used_by')); ?></th>
                                        <th scope="col" data-field="package_feature" data-visible="false" data-formatter="packageFeatureFormatter"><?php echo e(__('features')); ?></th>
                                        <th scope="col" data-field="operate" data-events="packageEvents" data-escape="false"><?php echo e(__('action')); ?></th>
                                    </tr>
                                    </thead>
                                </table>
                                <div class="form-group col-sm-12 col-md-4 mt-1 btn-update-rank d-none d-md-block">
                                    <button id="reorder" class="btn btn-theme"><?php echo e(__('update_rank')); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script>
        $(function () {
            $('#table_list').bootstrapTable()
            $('.table-list-type').click(function (e) {
                e.preventDefault();
                var dataId = $(this).data('id');

                // If "Trashed" is selected, show the reorder button
                if (dataId == 1) {
                    $('#reorder').hide(); // Hide the Update Rank button
                } else {
                    $('#reorder').show(); // Show the Update Rank button
                }

                // Highlight the active link
                $('.table-list-type').removeClass('active');
                $(this).addClass('active');

            });
            $('#reorder').click(function () {
                let idByOrder = JSON.stringify($('#table_list').bootstrapTable('getData').map((row) => row.id));
                let data = new FormData();
                data.append('ids', idByOrder);
                data.append('_method', 'PATCH');
                ajaxRequest('POST', baseUrl + '/package/change/rank', data, null, (response) => {
                    $('#table_list').bootstrapTable('refresh');
                    showSuccessToast(response.message)
                }, (response) => {
                    showErrorToast(response.message);
                })
            })
        })

    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/package/index.blade.php ENDPATH**/ ?>