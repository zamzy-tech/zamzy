<?php
header('Content-Type: text/plain; charset=UTF-8');

// =========================================================================
// DEPLOY: Deploy updated index.blade.php
// =========================================================================

$sites = [
    '/home/shacartc/school.tehub.in',
    '/home/shacartc/bms.tehub.in',
    '/home/shacartc/sch.brilliantbca.com',
];

function normalize($str) {
    return str_replace("\r\n", "\n", $str);
}

$target = <<<'PHP'
                                                    <div class="row">
                                                         <div class="col-sm-12 col-md-12 mb-3">
                                                             {{-- Start Immediate plan --}}
                                                             @if ($paymentConfiguration && $paymentConfiguration->payment_method == 'Razorpay' && $package->type == 0)
                                                                 <form action="{{ url('subscriptions/razorpay') }}" class="razorpay-form-{{ $package->id }}" method="POST"> @csrf
                                                                     <input type="hidden" name="package_id" class="package_id_{{ $package->id }}" value="{{ $package->id }}">
                                                                     <input type="hidden" name="amount" class="bill_amount_{{ $package->id }}" value="{{ $package->charges }}">
                                                                     
                                                                     <input type="hidden" name="type" class="type_{{ $package->id }}" value="package">
                                                                     <input type="hidden" name="package_type" class="package_type_{{ $package->id }}" value="immediate">
                                                                     
                                                                     <input type="hidden" name="razorpay_payment_id" class="razorpay_payment_id" value="">
                                                                     <input type="hidden" name="razorpay_signature" class="razorpay_signature" value="">
                                                                     <input type="hidden" name="razorpay_order_id" class="razorpay_order_id" value="">
                                                                     
                                                                     <input type="hidden" name="paymentTransactionId" class="paymentTransactionId" value="">
                                                                     
                                                                     <button class="btn btn-theme w-100" id="razorpay-button-{{ $package->id }}">{{ __('update_current_plan') }}</button>
                                                                 </form>
 
                                                             @elseif ($paymentConfiguration && $paymentConfiguration->payment_method == 'Stripe' && $package->type == 0)
                                                                 <form class="" action="{{ route('subscriptions.store') }}" novalidate="novalidate" data-stripe-publishable-key="{{ $settings['stripe_publishable_key'] ?? null }}" data-success-function="formSuccessFunction" method="post">
                                                                     @csrf
                                                                     <input type="hidden" name="payment_method" value="stripe">
                                                                     <input type="hidden" name="id" id="edit_id">
                                                                     <input type="hidden" name="package_id" class="package_id_{{ $package->id }}" value="{{ $package->id }}">
                                                                     <input type="hidden" name="amount" class="bill_amount" value="{{ $package->charges }}">
                                                                     <input type="hidden" name="type" class="type" value="package">
                                                                     <input type="hidden" name="package_type" class="package_type" value="immediate">
 
                                                                     <button class="btn btn-theme w-100" type="submit" id="stripe-button-{{ $package->id }}">{{ __('update_current_plan') }}</button>
                                                                 </form>
                                                             @elseif ($paymentConfiguration && $paymentConfiguration->payment_method == 'Paystack' && $package->type == 0)
                                                                 <form class="" action="{{ route('subscriptions.store') }}" novalidate="novalidate" data-paystack-publishable-key="{{ $paymentConfiguration->api_key ?? null }}" data-success-function="formSuccessFunction" method="post">
                                                                     @csrf
                                                                     <input type="hidden" name="payment_method" value="paystack">
                                                                     <input type="hidden" name="id" id="edit_id">
                                                                     <input type="hidden" name="package_id" class="package_id_{{ $package->id }}" value="{{ $package->id }}">
                                                                     <input type="hidden" name="amount" class="bill_amount" value="{{ $package->charges }}">
                                                                     <input type="hidden" name="type" class="type" value="package">
                                                                     <input type="hidden" name="package_type" class="package_type" value="immediate">
                                                                     
                                                                     {{-- <button type="button" class="btn btn-theme w-100 paystack-button">{{ __('get_start') }}</button> --}}
                                                                     <button class="btn btn-theme w-100" id="paystack-button-{{ $package->id }}">{{ __('update_current_plan') }}</button>
                                                                 </form>
                                                             @elseif ($paymentConfiguration && $paymentConfiguration->payment_method == 'Flutterwave' && $package->type == 0)
                                                                 <form class="" action="{{ route('subscriptions.store') }}" novalidate="novalidate" data-flutterwave-publishable-key="{{ $paymentConfiguration->api_key ?? null }}" data-success-function="formSuccessFunction" method="post">
                                                                     @csrf
                                                                     <input type="hidden" name="payment_method" value="flutterwave">
                                                                     <input type="hidden" name="package_id" class="package_id_{{ $package->id }}" value="{{ $package->id }}">
                                                                     <input type="hidden" name="amount" class="bill_amount" value="{{ $package->charges }}">
                                                                     <input type="hidden" name="type" class="type" value="package">
                                                                     <input type="hidden" name="package_type" class="package_type" value="immediate">
                                                                     <input type="hidden" name="id" id="edit_id">
                                                                     {{-- <input class="btn btn-theme payment-status" type="submit" value={{ __('Flutterwave') }} /> --}}
                                                                     <button class="btn btn-theme w-100" id="flutterwave-button-{{ $package->id }}">{{ __('update_current_plan') }}</button>
                                                                 </form>
                                                             @else
                                                                 <a href="#" class="btn start-immediate-plan @if ($package->highlight) btn-success @else btn-primary @endif btn-block" data-type="{{ $package->type }}" data-id="{{ $package->id }}">{{ __('update_current_plan') }}</a>
                                                             @endif                                                   
                                                         </div>
 
                                                         {{-- Set upcoming --}}
                                                         <div class="col-sm-12 col-md-12">
                                                             <a href="#" class="btn @if ($package->highlight) btn-outline-success @else btn-outline-primary @endif btn-block select-plan" data-type="{{ $package->type }}" data-id="{{ $package->id }}" data-iscurrentplan="0">{{ __('update_upcoming_plan') }}</a>
                                                         </div>
                                                     </div>
PHP;

$replacement = <<<'PHP'
                                                    <div class="row">
                                                         <div class="col-sm-12 col-md-12 mb-3">
                                                             {{-- Start Immediate plan --}}
                                                             @if ($paymentConfiguration && $paymentConfiguration->payment_method == 'Razorpay' && $package->type == 0)
                                                                 <form action="{{ url('subscriptions/razorpay') }}" class="razorpay-form-{{ $package->id }}" method="POST"> @csrf
                                                                     <input type="hidden" name="package_id" class="package_id_{{ $package->id }}" value="{{ $package->id }}">
                                                                     <input type="hidden" name="amount" class="bill_amount_{{ $package->id }}" value="{{ $package->charges }}">
                                                                     <input type="hidden" name="type" class="type_{{ $package->id }}" value="package">
                                                                     <input type="hidden" name="package_type" class="package_type_{{ $package->id }}" value="immediate">
                                                                     <input type="hidden" name="razorpay_payment_id" class="razorpay_payment_id" value="">
                                                                     <input type="hidden" name="razorpay_signature" class="razorpay_signature" value="">
                                                                     <input type="hidden" name="razorpay_order_id" class="razorpay_order_id" value="">
                                                                     <input type="hidden" name="paymentTransactionId" class="paymentTransactionId" value="">
                                                                     <button class="btn btn-theme w-100" id="razorpay-button-{{ $package->id }}">{{ __('update_plan') }}</button>
                                                                 </form>
                                                             @elseif ($paymentConfiguration && $paymentConfiguration->payment_method == 'Stripe' && $package->type == 0)
                                                                 <form class="" action="{{ route('subscriptions.store') }}" novalidate="novalidate" data-stripe-publishable-key="{{ $settings['stripe_publishable_key'] ?? null }}" data-success-function="formSuccessFunction" method="post">
                                                                     @csrf
                                                                     <input type="hidden" name="payment_method" value="stripe">
                                                                     <input type="hidden" name="id" id="edit_id">
                                                                     <input type="hidden" name="package_id" class="package_id_{{ $package->id }}" value="{{ $package->id }}">
                                                                     <input type="hidden" name="amount" class="bill_amount" value="{{ $package->charges }}">
                                                                     <input type="hidden" name="type" class="type" value="package">
                                                                     <input type="hidden" name="package_type" class="package_type" value="immediate">
                                                                     <button class="btn btn-theme w-100" type="submit" id="stripe-button-{{ $package->id }}">{{ __('update_plan') }}</button>
                                                                 </form>
                                                             @elseif ($paymentConfiguration && $paymentConfiguration->payment_method == 'Paystack' && $package->type == 0)
                                                                 <form class="" action="{{ route('subscriptions.store') }}" novalidate="novalidate" data-paystack-publishable-key="{{ $paymentConfiguration->api_key ?? null }}" data-success-function="formSuccessFunction" method="post">
                                                                     @csrf
                                                                     <input type="hidden" name="payment_method" value="paystack">
                                                                     <input type="hidden" name="id" id="edit_id">
                                                                     <input type="hidden" name="package_id" class="package_id_{{ $package->id }}" value="{{ $package->id }}">
                                                                     <input type="hidden" name="amount" class="bill_amount" value="{{ $package->charges }}">
                                                                     <input type="hidden" name="type" class="type" value="package">
                                                                     <input type="hidden" name="package_type" class="package_type" value="immediate">
                                                                     <button class="btn btn-theme w-100" id="paystack-button-{{ $package->id }}">{{ __('update_plan') }}</button>
                                                                 </form>
                                                             @elseif ($paymentConfiguration && $paymentConfiguration->payment_method == 'Flutterwave' && $package->type == 0)
                                                                 <form class="" action="{{ route('subscriptions.store') }}" novalidate="novalidate" data-flutterwave-publishable-key="{{ $paymentConfiguration->api_key ?? null }}" data-success-function="formSuccessFunction" method="post">
                                                                     @csrf
                                                                     <input type="hidden" name="payment_method" value="flutterwave">
                                                                     <input type="hidden" name="package_id" class="package_id_{{ $package->id }}" value="{{ $package->id }}">
                                                                     <input type="hidden" name="amount" class="bill_amount" value="{{ $package->charges }}">
                                                                     <input type="hidden" name="type" class="type" value="package">
                                                                     <input type="hidden" name="package_type" class="package_type" value="immediate">
                                                                     <input type="hidden" name="id" id="edit_id">
                                                                     <button class="btn btn-theme w-100" id="flutterwave-button-{{ $package->id }}">{{ __('update_plan') }}</button>
                                                                 </form>
                                                             @else
                                                                 <a href="#" class="btn start-immediate-plan @if ($package->highlight) btn-success @else btn-primary @endif btn-block" data-type="{{ $package->type }}" data-id="{{ $package->id }}">{{ __('update_plan') }}</a>
                                                             @endif                                                   
                                                         </div>
                                                     </div>
PHP;

$target = normalize($target);
$replacement = normalize($replacement);

// Also remove disabled Set upcoming for active plan
$targetActive = normalize(<<<'PHP'
                                                    <div class="wrapper mb-3">
                                                         <a href="#" class="btn disabled @if ($package->highlight) btn-success @else btn-outline-primary @endif btn-block select-plan" data-type="{{ $package->type }}" data-id="{{ $package->id }}">{{ __('current_active_plan') }}</a>
                                                     </div>
 
                                                     {{-- Set upcoming --}}
                                                     <div class="col-sm-12 col-md-12">
                                                         <a href="#" class="btn disabled @if ($package->highlight) btn-outline-success @else btn-outline-primary @endif btn-block select-plan" data-type="{{ $package->type }}" data-id="{{ $package->id }}">{{ __('update_upcoming_plan') }}</a>
                                                     </div>
PHP;
);

$replacementActive = normalize(<<<'PHP'
                                                    <div class="wrapper mb-3">
                                                         <a href="#" class="btn disabled @if ($package->highlight) btn-success @else btn-outline-primary @endif btn-block select-plan" data-type="{{ $package->type }}" data-id="{{ $package->id }}">{{ __('current_active_plan') }}</a>
                                                     </div>
PHP;
);

foreach ($sites as $site) {
    if (!is_dir($site)) {
        echo "SKIP: $site (not found)\n";
        continue;
    }
    
    echo "Processing site: $site\n";
    $indexPath = "$site/resources/views/subscription/index.blade.php";
    if (file_exists($indexPath)) {
        $content = normalize(file_get_contents($indexPath));
        $original = $content;
        
        $content = str_replace($target, $replacement, $content);
        $content = str_replace($targetActive, $replacementActive, $content);
        
        if ($content !== $original) {
            $backupDir = "$site/backups_view_updates_" . date('Ymd');
            if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
            copy($indexPath, "$backupDir/index.blade.php.bak");
            
            file_put_contents($indexPath, $content);
            echo "  Successfully modified index.blade.php\n";
        } else {
            echo "  index.blade.php: Target not found or already changed.\n";
        }
    } else {
        echo "  index.blade.php NOT FOUND\n";
    }

    // Clear caches
    $cacheDir = "$site/bootstrap/cache";
    if (is_dir($cacheDir)) {
        foreach (glob("$cacheDir/*.php") as $f) @unlink($f);
    }
    $viewCache = "$site/storage/framework/views";
    if (is_dir($viewCache)) {
        foreach (glob("$viewCache/*.php") as $f) @unlink($f);
    }
    chdir($site);
    @shell_exec('php artisan view:clear');
    echo "  Cleared view caches.\n";
}

echo "\n=== DEPLOYMENT OF VIEW UPDATES COMPLETE ===\n";

unlink(__FILE__);
?>
