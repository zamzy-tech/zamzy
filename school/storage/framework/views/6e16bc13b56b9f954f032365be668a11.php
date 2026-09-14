<?php $__env->startSection('title'); ?>
    <?php echo e(__('manage') . ' ' . __('fees')); ?> <?php echo e(__('paid')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('manage') . ' ' . __('fees')); ?> <?php echo e(__('paid')); ?>

            </h3>
        </div>
        <div class="row">
            
            <div class="col-md-4 col-sm-12 grid-margin stretch-card">
                <div class="card card-statistics">
                    <div class="custom-card-body">
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <p class="font-weight-bold"><?php echo e(__('total_fees')); ?></p>
                                <div class="d-flex align-items-center">
                                    <h4 class="font-weight-semibold total_fees_statistics">0</h4>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 border-left text-right">
                                <p class="text-muted mt-2"><?php echo e(__('compulsory_fees')); ?> : <span
                                        class="total_compulsory_fees">0</span></p>
                                <p class="text-muted mb-0"><?php echo e(__('optional_fees')); ?> : <span
                                        class="total_optional_fees">0</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 col-sm-12 grid-margin stretch-card">
                <div class="card card-statistics">
                    <div class="custom-card-body">
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <p class="font-weight-bold"> <?php echo e(__('collected')); ?> <?php echo e(__('Fees')); ?></p>
                                <div class="d-flex align-items-center">
                                    <h4 class="font-weight-semibold total_fees_collected">0</h4>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 border-left text-right">
                                <p class="text-muted mt-2"><?php echo e(__('compulsory_fees')); ?> : <span
                                        class="total_compulsory_fees_collected">0</span></p>
                                <p class="text-muted mb-0"><?php echo e(__('optional_fees')); ?> : <span
                                        class="total_optional_fees_collected">0</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 col-sm-12 grid-margin stretch-card">
                <div class="card card-statistics">
                    <div class="custom-card-body">
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <p class="font-weight-bold"> <?php echo e(__('pending')); ?> <?php echo e(__('Fees')); ?></p>
                                <div class="d-flex align-items-center">
                                    <h4 class="font-weight-semibold total_fees_pending">0</h4>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 border-left text-right">
                                <p class="text-muted mt-2"><?php echo e(__('compulsory_fees')); ?> : <span
                                        class="total_compulsory_fees_pending">0</span></p>
                                <p class="text-muted mb-0"><?php echo e(__('optional_fees')); ?> : <span
                                        class="total_optional_fees_pending">0</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title"></h4>
                        <div id="toolbar">
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label class="filter-menu" for="session_year_id"> <?php echo e(__('Session Years')); ?> </label>
                                    <select name="session_year_id" id="session_year_id" class="form-control">
                                        <?php $__currentLoopData = $session_year_all; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session_year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($session_year->id); ?>"
                                                <?php echo e($session_year->default ? 'selected' : ''); ?>> <?php echo e($session_year->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="filter-menu" for="filter_fees_id"><?php echo e(__('Fees')); ?></label>
                                    <select name="filter_fees_id" id="filter_fees_id" class="form-control">
                                        <?php $__currentLoopData = $fees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $fee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($fee->id); ?>" data-class-section-id="<?php echo e($fee->class_id); ?>" <?php echo e($key == 0 ? 'selected' : ''); ?>>
                                                <?php echo e($fee->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="filter-class-section-id"
                                        class="filter-menu"><?php echo e(__('Class Section')); ?></label>
                                    <select name="filter-class-section-id" id="filter-class-section-id"
                                        class="form-control">
                                        <option value=""><?php echo e(__('all')); ?></option>
                                        <?php $__currentLoopData = $class_section; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($class->id); ?>" data-class-section-id="<?php echo e($class->class_id); ?>">
                                                <?php echo e($class->full_name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label class="filter-menu" for="filter_paid_status"> <?php echo e(__('status')); ?> </label>
                                    <select name="filter_paid_status" id="filter_paid_status" class="form-control">
                                        <option value="0"><?php echo e(__('unpaid')); ?></option>
                                        <option value="1"><?php echo e(__('paid')); ?></option>
                                        <option value="2"><?php echo e(__('Partial Paid')); ?></option>
                                        
                                    </select>
                                </div>
                            </div>

                            
                            <div class="row paid-filter" style="display: none">
                                <div class="form-group col-md-3">
                                    <label class="filter-menu" for="filter_paid_status"> <?php echo e(__('month')); ?> </label>
                                    <?php echo Form::select('month', $months, date('n'), ['class' => 'form-control paid-month','placeholder' => __('all')]); ?>

                                </div>

                                
                                
                                <div class="form-group col-md-3">
                                    <label for="filter_online_offline_payment" class="filter-menu"><?php echo e(__('online_offline_payment')); ?></label>
                                    <select name="filter_online_offline_payment" id="filter_online_offline_payment" class="form-control select2">
                                        <option value="0"><?php echo e(__('all')); ?></option>
                                        <option value="1"><?php echo e(__('online')); ?></option>
                                        <option value="2"><?php echo e(__('offline')); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                            data-url="<?php echo e(route('fees.paid.list', 1)); ?>" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                            data-search="true" data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                            data-fixed-columns="false" data-trim-on-search="false" data-mobile-responsive="true"
                            data-sort-name="id" data-sort-order="desc" data-maintain-selected="true"
                            data-export-data-type='all'
                            data-export-options='{ "fileName": "<?php echo e(__('fees')); ?>-<?php echo e(__('paid')); ?>-<?php echo e(__('list')); ?>-<?= date('d-m-y')
                            ?>" ,"ignoreColumn":["operate"]}'
                            data-show-export="true" data-query-params="feesPaidListQueryParams" data-escape="true">
                            <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true" data-visible="false" data-align="center"><?php echo e(__('id')); ?></th>
                                    <th scope="col" data-field="no" data-formatter="totalFeesFormatter" data-sortable="false" data-align="center"><?php echo e(__('no.')); ?></th>
                                    <th scope="col" data-field="student.id" data-sortable="false" data-visible="false" data-align="center"><?php echo e(__('Student Id')); ?></th>
                                    <th scope="col" data-field="full_name" data-sortable="false" data-align="center"> <?php echo e(__('Student Name')); ?></th>
                                    <th scope="col" data-field="student.class_section.full_name" data-sortable="false" data-align="center"><?php echo e(__('Class')); ?></th>
                                    <th scope="col" data-field="fees.total_compulsory_fees" data-sortable="false" data-align="center"><?php echo e(__('Compulsory Fees')); ?></th>
                                    <th scope="col" data-field="fees.total_optional_fees" data-sortable="false" data-align="center"><?php echo e(__('Optional Fees')); ?></th>
                                    <th scope="col" data-field="payment_method" data-sortable="false" data-align="center"> <?php echo e(__('Payment Method')); ?></th>
                                    <th scope="col" data-field="fees_status" data-sortable="false" data-formatter="feesPaidStatusFormatter" data-align="center"> <?php echo e(__('Fees Status')); ?></th>
                                    <th scope="col" data-field="fees_paid.date" data-formatter="dateFormatter" data-sortable="false" data-align="center"><?php echo e(__('Date')); ?></th>
                                    <th scope="col" data-field="paid_amount" data-sortable="false"><?php echo e(__('paid_amount')); ?></th>
                                    <th scope="col" data-field="operate" data-sortable="false" data-events="feesPaidEvents" data-align="center" data-escape="false"> <?php echo e(__('Action')); ?></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
    <script>

        $('#filter_paid_status').change(function (e) { 
            e.preventDefault();
            $('.paid-filter').hide(500);

            if ($(this).val() == 1 || $(this).val() == 2) {
                $('.paid-filter').show(500);
            }
        });

        window.onload = setTimeout(() => {
            $('#session_year_id').trigger('change');
        }, 500);

        $('#session_year_id').on('change', function() {
            let data = new FormData();
            data.append('session_year_id', $(this).val());
            ajaxRequest('GET', baseUrl + '/fees/search', {
                'session_year_id': $(this).val()
            }, null, function(response) {
                let feesDropdown = "";
                response.data.forEach(function(value, index) {
                    feesDropdown += "<option value='" + value.id + "' data-class-section-id='" + value.class_id + "'>" + value.name + "</option>";
                })

                $('#filter_fees_id').html(feesDropdown);
                $('#table_list').bootstrapTable('refresh');
            }, null, null, true)
        })
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/fees/fees_paid.blade.php ENDPATH**/ ?>