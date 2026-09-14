<?php $__env->startSection('title'); ?>
    <?php echo e(__('online')); ?> <?php echo e(__('fees')); ?> <?php echo e(__('transactions')); ?> <?php echo e(__('logs')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('online')); ?> <?php echo e(__('fees')); ?> <?php echo e(__('transactions')); ?> <?php echo e(__('logs')); ?>

            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <div id="toolbar" class="row">
                            <div class="form-group col-md-4">
                                <label class="filter-menu" for="filter_payment_status" style="font-size: 0.86rem;width: 110px">
                                    <?php echo e(__('Payment Status')); ?>

                                </label>
                                <select name="filter_payment_status" id="filter_payment_status" class="form-control">
                                    <option value=""><?php echo e(__('all')); ?></option>
                                    <option value="failed"><?php echo e(__('failed')); ?></option>
                                    <option value="succeed"><?php echo e(__('succeed')); ?></option>
                                    <option value="pending"><?php echo e(__('pending')); ?></option>
                                </select>
                            </div>

                            <div class="form-group col-md-3">
                                <label class="filter-menu" for="filter_paid_status"> <?php echo e(__('month')); ?> </label>
                                <?php echo Form::select('month', $months, date('n'), ['class' => 'form-control paid-month','placeholder' => __('all')]); ?>

                            </div>

                            <div class="form-group col-md-3">
                                <label class="filter-menu" for="session_year_id"> <?php echo e(__('Session Years')); ?> </label>
                                <select name="session_year_id" id="filter_session_year_id" class="form-control">
                                    <?php $__currentLoopData = $session_year_all; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session_year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($session_year->id); ?>"
                                            <?php echo e($session_year->default ? 'selected' : ''); ?>> <?php echo e($session_year->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <table aria-describedby="mydesc" class='table' id='table_list'
                               data-toggle="table" data-url="<?php echo e(route('fees.transactions.log.list', 1)); ?>"
                               data-click-to-select="true" data-side-pagination="server"
                               data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                               data-search="true" data-toolbar="#toolbar" data-show-columns="true"
                               data-show-refresh="true" data-fixed-columns="false" data-fixed-number="2"
                               data-fixed-right-number="1" data-trim-on-search="false"
                               data-mobile-responsive="true" data-sort-name="id"
                               data-sort-order="desc" data-maintain-selected="true" data-export-data-type='all'
                               data-export-options='{ "fileName": "<?php echo e(__('fees')); ?>-<?php echo e(__('transactions')); ?>-<?= date(' d-m-y') ?>" ,"ignoreColumn":["operate"]}'
                               data-show-export="true" data-query-params="feesPaymentTransactionQueryParams" data-escape="true">
                            <thead>
                            <tr>
                                <th scope="col" data-field="id" data-sortable="false" data-visible="false"><?php echo e(__('id')); ?></th>
                                <th scope="col" data-field="no"><?php echo e(__('no.')); ?></th>
                                <th scope="col" data-field="user.full_name" data-align="center"><?php echo e(__('User')); ?></th>
                                <th scope="col" data-field="amount" data-align="center"><?php echo e(__('Amount')); ?></th>
                                <th scope="col" data-field="payment_gateway" data-align="center" data-formatter="feesTransactionParentGateway"><?php echo e(__('Payment Gateway')); ?></th>
                                <th scope="col" data-field="payment_status" data-align="center" data-formatter="transactionPaymentStatus"><?php echo e(__('Payment Status')); ?></th>
                                <th scope="col" data-field="order_id" data-align="center" data-visible="false"><?php echo e(__('order_id')); ?></th>
                                <th scope="col" data-field="payment_id" data-align="center" data-visible="false"><?php echo e(__('payment_id')); ?></th>
                                <th scope="col" data-field="created_at" data-formatter="dateTimeFormatter" data-sortable="false" data-visible="true"><?php echo e(__('date')); ?></th>
                                <th scope="col" data-field="updated_at" data-formatter="dateTimeFormatter" data-sortable="false" data-visible="false"><?php echo e(__('updated_at')); ?></th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/fees/fees_transaction_logs.blade.php ENDPATH**/ ?>