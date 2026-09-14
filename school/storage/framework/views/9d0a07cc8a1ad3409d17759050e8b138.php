<?php $__env->startSection('title'); ?>
    <?php echo e(__('plans')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<style>
    :root {
    --primary-color: <?php echo e($settings['theme_primary_color'] ?? '#56cc99'); ?>;
    --secondary-color: <?php echo e($settings['theme_secondary_color'] ?? '#215679'); ?>;
   
}
</style>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('manage') . ' ' . __('subscription')); ?>

            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <?php if($upcoming_package): ?>
                            <h3 class="card-title text-danger"><?php echo e(__('note')); ?> : <?php echo e(__('if_youve_already_made_payment_for_your_upcoming_plan_changes_or_updates_to_the_current_and_upcoming_plan_will_not_be_permitted')); ?></h3>
                        <?php endif; ?>
                        
                        <div class="row pricing-table mt-4">
                            <?php $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $package): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($package->is_trial == 1): ?>
                                    <?php continue; ?>
                                <?php endif; ?>
                                <div class="col-md-6 col-xl-4 grid-margin stretch-card pricing-card">
                                    <div class="card <?php if($package->highlight): ?> border-success ribbon <?php else: ?> border-primary <?php endif; ?>  border pricing-card-body">
                                        <?php if($package->is_trial != 1): ?>
                                            <?php if($package->type == 1): ?>
                                                <span class="package-type-badge postpaid-color"><?php echo e(__('postpaid')); ?></span>
                                            <?php else: ?>
                                                <span class="package-type-badge prepaid-color">Autopay</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <div class="text-center pricing-card-head mb-2">
                                            <h3><?php echo e(__($package->name)); ?></h3>
                                            <p><?php echo e($package->description); ?></p>
                                            <h1 class="font-weight-normal mb-2"></h1>
                                            <hr>
                                            <div class="row">
                                                <?php if($package->is_trial == 1): ?>
                                                    <div class="col-sm-12 col-md-12">
                                                        <b><?php echo e(__('package_information')); ?></b>
                                                    </div>
                                                    <div class="col-sm-12 col-md-12 mt-3 text-small">
                                                        <?php echo e(__('student_limit')); ?> : <?php echo e($settings['student_limit']); ?>

                                                    </div>

                                                    <div class="col-sm-12 col-md-12 mt-1 text-small">
                                                        <?php echo e(__('staff_limit')); ?> : <?php echo e($settings['staff_limit']); ?>

                                                    </div>
                                                    <div class="col-sm-12 col-md-12 mt-1 text-small">
                                                        <?php echo e($settings['trial_days']); ?> / <?php echo e(__('days')); ?>

                                                    </div>
                                                <?php elseif($package->type == 0): ?>
                                                    <div class="col-sm-12 col-md-12">
                                                        <b><?php echo e(__('package_price_information')); ?></b>
                                                        <hr>
                                                    </div>
                                                    <div class="col-sm-12 col-md-12">
                                                        <h3> <?php echo e($settings['currency_symbol']); ?> <?php echo e($package->charges); ?></h3>
                                                    </div>
                                                    <div class="col-sm-12 col-md-12 mt-2">
                                                        <span class="badge badge-success" style="font-weight: bold; padding: 6px 12px; border-radius: 12px; font-size: 11px;">
                                                            <i class="fa fa-gift"></i> Includes 1 Month Free Trial for ₹1
                                                        </span>
                                                    </div>
                                                    <div class="col-sm-12 col-md-12 mt-3 text-small">
                                                        <?php echo e(__('student_limit')); ?> : <?php echo e($package->no_of_students); ?> / <?php echo e(__('staff_limit')); ?> : <?php echo e($package->no_of_staffs); ?>

                                                    </div>
                                                    <div class="col-sm-12 col-md-12 mt-2 text-small">
                                                        <?php echo e($package->days); ?> / <?php echo e(__('days')); ?>

                                                    </div>
                                                <?php else: ?>
                                                    <div class="col-sm-12 col-md-12">
                                                        <b><?php echo e(__('package_price_information')); ?></b>
                                                        <hr>
                                                    </div>
                                                    <div class="col-sm-12 col-md-12 mt-2">
                                                        <h3> <?php echo e($settings['currency_symbol']); ?> <?php echo e($package->charges); ?></h3>
                                                    </div>
                                                    <div class="col-sm-12 col-md-12 mt-2 text-small text-muted font-weight-bold">
                                                         (<?php echo e(__('postpaid')); ?>)
                                                    </div>
                                                    <div class="col-sm-12 col-md-12 mt-2 text-small">
                                                        <?php echo e($package->days); ?> / <?php echo e(__('days')); ?>

                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <hr>

                                        <ul class="list-unstyled">
                                            <?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <li><i class="fa fa-check check mr-2"></i><?php echo e(__($feature->name)); ?></li>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </ul>
                                        <?php if(!$upcoming_package): ?>
                                            <?php if($current_plan): ?>
                                                <?php if($package->id == $current_plan->package_id): ?>
                                                    <div class="wrapper mb-3">
                                                        <a href="#" class="btn disabled <?php if($package->highlight): ?> btn-success <?php else: ?> btn-outline-primary <?php endif; ?> btn-block select-plan" data-type="<?php echo e($package->type); ?>" data-id="<?php echo e($package->id); ?>"><?php echo e(__('current_active_plan')); ?></a>
                                                    </div>

                                                    
                                                <?php else: ?>
                                                    <div class="row">
                                                        <div class="col-sm-12 col-md-12 mb-3">
                                                            
                                                            <?php if($paymentConfiguration && $paymentConfiguration->payment_method == 'Razorpay' && $package->type == 0): ?>
                                                                <form action="<?php echo e(url('subscriptions/razorpay')); ?>" class="razorpay-form-<?php echo e($package->id); ?>" method="POST"> <?php echo csrf_field(); ?>
                                                                    <input type="hidden" name="package_id" class="package_id_<?php echo e($package->id); ?>" value="<?php echo e($package->id); ?>">
                                                                    <input type="hidden" name="amount" class="bill_amount_<?php echo e($package->id); ?>" value="<?php echo e($package->charges); ?>">
        
                                                                    <input type="hidden" name="type" class="type_<?php echo e($package->id); ?>" value="package">
                                                                    <input type="hidden" name="package_type" class="package_type_<?php echo e($package->id); ?>" value="immediate">
        
                                                                    <input type="hidden" name="razorpay_payment_id" class="razorpay_payment_id" value="">
                                                                    <input type="hidden" name="razorpay_signature" class="razorpay_signature" value="">
                                                                    <input type="hidden" name="razorpay_order_id" class="razorpay_order_id" value="">
        
                                                                    <input type="hidden" name="paymentTransactionId" class="paymentTransactionId" value="">
        
                                                                    <button class="btn btn-theme w-100" id="razorpay-button-<?php echo e($package->id); ?>"><?php echo e(__('update_plan')); ?></button>
                                                                </form>

                                                            <?php elseif($paymentConfiguration && $paymentConfiguration->payment_method == 'Stripe' && $package->type == 0): ?>
                                                                <form class="" action="<?php echo e(route('subscriptions.store')); ?>" novalidate="novalidate" data-stripe-publishable-key="<?php echo e($settings['stripe_publishable_key'] ?? null); ?>" data-success-function="formSuccessFunction" method="post">
                                                                    <?php echo csrf_field(); ?>
                                                                    <input type="hidden" name="payment_method" value="stripe">
                                                                    <input type="hidden" name="id" id="edit_id">
                                                                    <input type="hidden" name="package_id" class="package_id_<?php echo e($package->id); ?>" value="<?php echo e($package->id); ?>">
                                                                    <input type="hidden" name="amount" class="bill_amount" value="<?php echo e($package->charges); ?>">
                                                                    <input type="hidden" name="type" class="type" value="package">
                                                                    <input type="hidden" name="package_type" class="package_type" value="immediate">

                                                                    <button class="btn btn-theme w-100" type="submit" id="stripe-button-<?php echo e($package->id); ?>"><?php echo e(__('update_plan')); ?></button>
                                                                </form>
                                                            <?php elseif($paymentConfiguration && $paymentConfiguration->payment_method == 'Paystack' && $package->type == 0): ?>
                                                                <form class="" action="<?php echo e(route('subscriptions.store')); ?>" novalidate="novalidate" data-paystack-publishable-key="<?php echo e($paymentConfiguration->api_key ?? null); ?>" data-success-function="formSuccessFunction" method="post">
                                                                    <?php echo csrf_field(); ?>
                                                                    <input type="hidden" name="payment_method" value="paystack">
                                                                    <input type="hidden" name="id" id="edit_id">
                                                                    <input type="hidden" name="package_id" class="package_id_<?php echo e($package->id); ?>" value="<?php echo e($package->id); ?>">
                                                                    <input type="hidden" name="amount" class="bill_amount" value="<?php echo e($package->charges); ?>">
                                                                    <input type="hidden" name="type" class="type" value="package">
                                                                    <input type="hidden" name="package_type" class="package_type" value="immediate">
                                                                    
                                                                    
                                                                    <button class="btn btn-theme w-100" id="paystack-button-<?php echo e($package->id); ?>"><?php echo e(__('update_plan')); ?></button>
                                                                </form>
                                                            <?php elseif($paymentConfiguration && $paymentConfiguration->payment_method == 'Flutterwave' && $package->type == 0): ?>
                                                                <form class="" action="<?php echo e(route('subscriptions.store')); ?>" novalidate="novalidate" data-flutterwave-publishable-key="<?php echo e($paymentConfiguration->api_key ?? null); ?>" data-success-function="formSuccessFunction" method="post">
                                                                    <?php echo csrf_field(); ?>
                                                                    <input type="hidden" name="payment_method" value="flutterwave">
                                                                    <input type="hidden" name="package_id" class="package_id_<?php echo e($package->id); ?>" value="<?php echo e($package->id); ?>">
                                                                    <input type="hidden" name="amount" class="bill_amount" value="<?php echo e($package->charges); ?>">
                                                                    <input type="hidden" name="type" class="type" value="package">
                                                                    <input type="hidden" name="package_type" class="package_type" value="immediate">
                                                                    <input type="hidden" name="id" id="edit_id">
                                                                    
                                                                    <button class="btn btn-theme w-100" id="flutterwave-button-<?php echo e($package->id); ?>"><?php echo e(__('update_plan')); ?></button>
                                                                </form>
                                                            <?php else: ?>
                                                                <a href="#" class="btn start-immediate-plan <?php if($package->highlight): ?> btn-success <?php else: ?> btn-primary <?php endif; ?> btn-block" data-type="<?php echo e($package->type); ?>" data-id="<?php echo e($package->id); ?>"><?php echo e(__('update_plan')); ?></a>
                                                            <?php endif; ?>                                                   
                                                        </div>

                                                        
                                                    </div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php if($paymentConfiguration && $paymentConfiguration->payment_method == 'Razorpay' && $package->type == 0): ?>
                                                    
                                                    <div class="wrapper">
                                                        <form action="<?php echo e(url('subscriptions/razorpay')); ?>" class="razorpay-form-<?php echo e($package->id); ?>" method="POST">
                                                            <?php echo csrf_field(); ?>
                                                            <input type="hidden" name="package_id" class="package_id_<?php echo e($package->id); ?>" value="<?php echo e($package->id); ?>">
                                                            <input type="hidden" name="amount" class="bill_amount_<?php echo e($package->id); ?>" value="<?php echo e($package->charges); ?>">

                                                            <input type="hidden" name="type" class="type_<?php echo e($package->id); ?>" value="package">
                                                            <input type="hidden" name="package_type" class="package_type_<?php echo e($package->id); ?>" value="new">

                                                            <input type="hidden" name="razorpay_payment_id" class="razorpay_payment_id" value="">
                                                            <input type="hidden" name="razorpay_signature" class="razorpay_signature" value="">
                                                            <input type="hidden" name="razorpay_order_id" class="razorpay_order_id" value="">

                                                            <input type="hidden" name="paymentTransactionId" class="paymentTransactionId" value="">

                                                            <button class="btn btn-theme w-100" id="razorpay-button-<?php echo e($package->id); ?>"><?php echo e(__('get_start')); ?></button>
                                                        </form>
                                                    </div>
                                                <?php elseif($paymentConfiguration && $paymentConfiguration->payment_method == 'Stripe' && $package->type == 0): ?>
                                                    <form class="" action="<?php echo e(route('subscriptions.store')); ?>" novalidate="novalidate" data-stripe-publishable-key="<?php echo e($settings['stripe_publishable_key'] ?? null); ?>" data-success-function="formSuccessFunction" method="post">
                                                        <?php echo csrf_field(); ?>
                                                        <input type="hidden" name="payment_method" value="stripe">
                                                        <input type="hidden" name="package_id" class="package_id_<?php echo e($package->id); ?>" value="<?php echo e($package->id); ?>">
                                                        <input type="hidden" name="type" value="package">
                                                        <input type="hidden" name="package_type" value="new">

                                                        <button class="btn btn-theme w-100" id="stripe-button-<?php echo e($package->id); ?>"><?php echo e(__('get_start')); ?></button>
                                                    </form>
                                                <?php elseif($paymentConfiguration && $paymentConfiguration->payment_method == 'Paystack' && $package->type == 0): ?>
                                                    <form action="<?php echo e(route('subscriptions.store')); ?>" class="paystack-form-<?php echo e($package->id); ?>" data-paystack-publishable-key="<?php echo e($paymentConfiguration->api_key ?? null); ?>" data-success-function="formSuccessFunction" method="post">
                                                        <?php echo csrf_field(); ?>
                                                        <input type="hidden" name="payment_method" value="paystack">
                                                        <input type="hidden" name="package_id" class="package_id_<?php echo e($package->id); ?>" value="<?php echo e($package->id); ?>">
                                                        <input type="hidden" name="type" value="package">
                                                        <input type="hidden" name="package_type" value="new">
                                                        
                                                        <button class="btn btn-theme w-100" type="submit"><?php echo e(__('get_start')); ?></button>
                                                    </form>
                                                <?php elseif($paymentConfiguration && $paymentConfiguration->payment_method == 'Flutterwave' && $package->type == 0): ?>
                                                    <form class="" action="<?php echo e(route('subscriptions.store')); ?>" novalidate="novalidate" data-flutterwave-publishable-key="<?php echo e($paymentConfiguration->api_key ?? null); ?>" data-success-function="formSuccessFunction" method="post">
                                                        <?php echo csrf_field(); ?>
                                                        <input type="hidden" name="payment_method" value="flutterwave">
                                                        <input type="hidden" name="id" id="edit_id">
                                                        <input type="hidden" name="package_id" class="package_id_<?php echo e($package->id); ?>" value="<?php echo e($package->id); ?>">
                                                        <input type="hidden" name="amount" class="bill_amount_<?php echo e($package->id); ?>" value="<?php echo e($package->charges); ?>">
                                                        <input type="hidden" name="type" class="type_<?php echo e($package->id); ?>" value="package">
                                                        <input type="hidden" name="package_type" class="package_type_<?php echo e($package->id); ?>" value="new">

                                                        <button class="btn btn-theme w-100" type="submit" id="flutterwave-button-<?php echo e($package->id); ?>"><?php echo e(__('get_start')); ?></button>
                                                    </form>
                                                <?php else: ?>
                                                    <div class="wrapper">
                                                        <a href="#" class="btn <?php if($package->highlight): ?> btn-success <?php else: ?> btn-outline-primary <?php endif; ?> btn-block select-plan" data-type="<?php echo e($package->type); ?>" data-iscurrentplan="1" data-id="<?php echo e($package->id); ?>"><?php echo e(__('get_start')); ?></a>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        // razorpay-payment-button
    setTimeout(() => {
        $('.razorpay-payment-button').addClass('btn btn-info w-100');
    }, 100);
    </script>


<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<?php $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $package): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<script>
    $(document).ready(function () {
        $(document).off('click', '#razorpay-button-<?php echo e($package->id); ?>').on('click', '#razorpay-button-<?php echo e($package->id); ?>', function (e) {
            let baseUrl = window.location.origin;
            var order_id = '';
            var paymentTransactionId = '';

            $.ajax({
                type: "post",
                url: baseUrl + '/subscriptions/create/razorpay/order-id',
                data: {
                    amount : $('.bill_amount_<?php echo e($package->id); ?>').val(),
                    currency : "<?php echo e($system_settings['currency_code'] ?? 'INR'); ?>",

                    type : $('.type_<?php echo e($package->id); ?>').val(),
                    package_type : $('.package_type_<?php echo e($package->id); ?>').val(),
                    package_id : $('.package_id_<?php echo e($package->id); ?>').val(),
                    upcoming_plan_type : $('.upcoming_plan_type').val(),
                    subscription_id : $('.subscription_id').val(),
                    feature_id : $('.feature_id').val(),
                    end_date : $('.end_date').val(),
                    
                },
                success: function (response) {
                    if (response.data) {
                        order_id = response.data.order.id;
                        paymentTransactionId = response.data.paymentTransaction.id;
                        var options = {
                            "key": "<?php echo e($paymentConfiguration->api_key ?? ''); ?>", // Enter the Key ID generated from the Dashboard
                            "amount": response.data.order.amount, // Amount is in currency subunits. Default currency is INR. Hence, 100 refers to 1 INR
                            "currency": "<?php echo e($system_settings['currency_code'] ?? 'INR'); ?>",
                            "name": "<?php echo e($system_settings['system_name'] ?? 'eSchool-Saas'); ?>",
                            "description": "Autopay",
                            "order_id": order_id,
                            "handler": function(response) {
                                // Set the response data in the form relative to the correct package form
                                var $form = $('.razorpay-form-<?php echo e($package->id); ?>');
                                $form.find('.razorpay_payment_id').val(response.razorpay_payment_id);
                                $form.find('.razorpay_signature').val(response.razorpay_signature);
                                $form.find('.razorpay_order_id').val(response.razorpay_order_id || order_id);
                                $form.find('.paymentTransactionId').val(paymentTransactionId);

                                // Submit the form
                                $form[0].submit();
                            }
                        };

                        var rzp1 = new Razorpay(options);
                        rzp1.open();
                    } else {
                        Swal.fire({icon: 'error', text: response.message});
                    }
                }
            });
            e.preventDefault();
        });
    });
</script> 
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/subscription/index.blade.php ENDPATH**/ ?>