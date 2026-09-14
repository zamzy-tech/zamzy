<?php
header('Content-Type: text/plain; charset=UTF-8');

// =========================================================================
// DEPLOY: Deploy updated SubscriptionService.php & SubscriptionController.php (Normalizing line endings)
// =========================================================================

$sites = [
    '/home/shacartc/school.tehub.in',
    '/home/shacartc/bms.tehub.in',
    '/home/shacartc/sch.brilliantbca.com',
];

// Normalize line endings function
function normalize($str) {
    return str_replace("\r\n", "\n", $str);
}

// Content for SubscriptionService.php replacement
$serviceTarget = normalize(<<<'PHP'
        $settings = $this->cache->getSystemSettings();
        $package = $this->package->builder()->with('package_feature')->where('id', $package_id)->first();
        $end_date = '';
        if (!$school_id) {
            $school_id = Auth::user()->school_id;
        }
        if ($package->is_trial) {
            $trialDays = $settings['trial_days'] ?? 30;
            $end_date = Carbon::now()->addDays($trialDays)->format('Y-m-d');
        } else {
            $end_date = Carbon::now()->addDays(($package->days - 1))->format('Y-m-d');
        }
        $start_date = Carbon::now()->format('Y-m-d');



        // If not current subscription plan
        if (!$isCurrentPlan) {
            // Attempt to get the current active subscription
            $current_subscription = $this->active_subscription($school_id);

            // Check if a current subscription was found
            if ($current_subscription) {
                $start_date = Carbon::parse($current_subscription->end_date)->addDays()->format('Y-m-d');
                $end_date = Carbon::parse($start_date)->addDays(($package->days - 1))->format('Y-m-d');
            } else {
                // Handle the case where there is no active subscription
                Log::warning("No active subscription found for school_id: {$school_id}");
                // You might want to set default dates or handle this case differently
                $start_date = Carbon::now()->format('Y-m-d');
                $end_date = Carbon::now()->addDays($package->days - 1)->format('Y-m-d');
            }
        }
PHP;
);

$serviceReplacement = normalize(<<<'PHP'
        $settings = $this->cache->getSystemSettings();
        $package = $this->package->builder()->with('package_feature')->where('id', $package_id)->first();
        $end_date = '';
        if (!$school_id) {
            $school_id = Auth::user()->school_id;
        }

        // Find the latest active or future subscription for this school (excluding the one being updated)
        $last_subscription_query = Subscription::where('school_id', $school_id)
            ->where('end_date', '>=', Carbon::now()->format('Y-m-d'))
            ->orderBy('end_date', 'desc');

        if ($subscription_id) {
            $last_subscription_query->where('id', '!=', $subscription_id);
        }

        $last_subscription = $last_subscription_query->first();

        if ($last_subscription) {
            $start_date = Carbon::parse($last_subscription->end_date)->addDay()->format('Y-m-d');
            $end_date = Carbon::parse($start_date)->addDays(($package->days - 1))->format('Y-m-d');
        } else {
            $start_date = Carbon::now()->format('Y-m-d');
            if ($package->is_trial) {
                $trialDays = $settings['trial_days'] ?? 30;
                $end_date = Carbon::now()->addDays($trialDays)->format('Y-m-d');
            } else {
                $end_date = Carbon::now()->addDays(($package->days - 1))->format('Y-m-d');
            }
        }
PHP;
);

$serviceTarget2 = normalize(<<<'PHP'
        // If current subscription plan then set package features
        if ($isCurrentPlan) {
            $subscription_features = array();
PHP;
);

$serviceReplacement2 = normalize(<<<'PHP'
        // If current subscription plan then set package features (only if start date is today or past)
        if ($isCurrentPlan && $start_date <= Carbon::now()->format('Y-m-d')) {
            $subscription_features = array();
PHP;
);

// Content for SubscriptionController.php replacement
$controllerTarget = normalize(<<<'PHP'
                    if ($packageType == 'new' && $packageId) {
                        // Create new subscription
                        $newSubscription = $subscriptionService->createSubscription($packageId, $schoolId, null, 1);
                        
                        // Link bill to payment transaction
                        if ($newSubscription && $newSubscription->subscription_bill && $paymentTransactionId) {
                            \App\Models\SubscriptionBill::where('subscription_id', $newSubscription->id)
                                ->update(['payment_transaction_id' => $paymentTransactionId]);
                        }
                    }
PHP;
);

$controllerReplacement = normalize(<<<'PHP'
                    if (in_array($packageType, ['new', 'immediate']) && $packageId) {
                        // Create new subscription
                        $newSubscription = $subscriptionService->createSubscription($packageId, $schoolId, null, 1);
                        
                        // Link bill to payment transaction
                        if ($newSubscription && $newSubscription->subscription_bill && $paymentTransactionId) {
                            \App\Models\SubscriptionBill::where('subscription_id', $newSubscription->id)
                                ->update(['payment_transaction_id' => $paymentTransactionId]);
                        }
                    } elseif ($packageType == 'bill') {
                        // Link existing bill to payment transaction
                        $subId = $request->subscription_id ?? 0;
                        if ($subId && $paymentTransactionId) {
                            \App\Models\SubscriptionBill::where('subscription_id', $subId)
                                ->update(['payment_transaction_id' => $paymentTransactionId]);
                        }
                    }
PHP;
);

foreach ($sites as $site) {
    if (!is_dir($site)) {
        echo "SKIP: $site (not found)\n";
        continue;
    }
    
    echo "Processing site: $site\n";
    $backupDir = "$site/backups_sub_updates_" . date('Ymd');
    if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);

    // 1. Update SubscriptionService.php
    $servicePath = "$site/app/Services/SubscriptionService.php";
    if (file_exists($servicePath)) {
        $content = normalize(file_get_contents($servicePath));
        $original = $content;
        
        $content = str_replace($serviceTarget, $serviceReplacement, $content);
        $content = str_replace($serviceTarget2, $serviceReplacement2, $content);
        
        if ($content !== $original) {
            copy($servicePath, "$backupDir/SubscriptionService.php.bak");
            file_put_contents($servicePath, $content);
            echo "  Successfully modified SubscriptionService.php\n";
        } else {
            echo "  SubscriptionService.php: Target not found or already changed.\n";
        }
    } else {
        echo "  SubscriptionService.php NOT FOUND\n";
    }

    // 2. Update SubscriptionController.php
    $controllerPath = "$site/app/Http/Controllers/SubscriptionController.php";
    if (file_exists($controllerPath)) {
        $content = normalize(file_get_contents($controllerPath));
        $original = $content;
        
        $content = str_replace($controllerTarget, $controllerReplacement, $content);
        if ($content !== $original) {
            copy($controllerPath, "$backupDir/SubscriptionController.php.bak");
            file_put_contents($controllerPath, $content);
            echo "  Successfully modified SubscriptionController.php\n";
        } else {
            echo "  SubscriptionController.php: Target not found or already changed.\n";
        }
    } else {
        echo "  SubscriptionController.php NOT FOUND\n";
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
    $dataCache = "$site/storage/framework/cache/data";
    if (is_dir($dataCache)) {
        $it = new RecursiveDirectoryIterator($dataCache, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isFile() && $file->getFilename() !== '.gitignore') {
                @unlink($file->getRealPath());
            }
        }
    }
    chdir($site);
    @shell_exec('php artisan cache:clear');
    @shell_exec('php artisan config:clear');
    @shell_exec('php artisan route:clear');
    @shell_exec('php artisan view:clear');
    echo "  Cleared caches.\n";
}

echo "\n=== DEPLOYMENT OF SUBSCRIPTION UPDATES COMPLETE ===\n";

unlink(__FILE__);
?>
