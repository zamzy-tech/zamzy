<?php
header('Content-Type: text/plain; charset=UTF-8');

// =========================================================================
// DEPLOY: Deploy updated SchoolController.php and activate free trial for schools 15 & 16
// =========================================================================

$targetContent = <<<'PHP'
                Auth::login($user);
                Session::put('school_database_name', $database_name);

                $checkoutUrl = null;
                if ($trialPackageId) {
                    $checkoutUrl = url('subscriptions/prepaid/package/' . $trialPackageId . '/0/1');
                    
                    $paymentConfig = \App\Models\PaymentConfiguration::whereNull('school_id')->where('status', 1)->first();
                    if ($paymentConfig && $paymentConfig->payment_method === 'Razorpay') {
                        $checkoutUrl = url('subscriptions');
                    }
                }
PHP;

$replacementContent = <<<'PHP'
                if ($trialPackageId) {
                    $this->subscriptionService->createSubscription($trialPackageId, $schoolData->id, null, 1);
                    $this->cache->removeSchoolCache(config('constants.CACHE.SCHOOL.SETTINGS'), $schoolData->id);
                }

                Auth::login($user);
                Session::put('school_database_name', $database_name);

                $checkoutUrl = url('dashboard');
PHP;

$sites = [
    '/home/shacartc/school.tehub.in',
    '/home/shacartc/bms.tehub.in',
    '/home/shacartc/sch.brilliantbca.com',
];

foreach ($sites as $site) {
    if (!is_dir($site)) {
        echo "SKIP: $site (not found)\n";
        continue;
    }
    
    $targetPath = "$site/app/Http/Controllers/SchoolController.php";
    if (!file_exists($targetPath)) {
        echo "SKIP: File not found at $targetPath\n";
        continue;
    }
    
    $content = file_get_contents($targetPath);
    $original = $content;
    
    // Perform replacement
    $content = str_replace($targetContent, $replacementContent, $content);
    
    if ($content !== $original) {
        // Backup
        $backupDir = "$site/backups_trial_fix_" . date('Ymd');
        if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
        copy($targetPath, "$backupDir/SchoolController.php.bak");
        
        file_put_contents($targetPath, $content);
        echo "Successfully modified SchoolController.php in $site\n";
    } else {
        echo "NO CHANGE or pattern not found in $targetPath\n";
    }
}

echo "\nBootstrapping Laravel to activate trials for School 15 and 16...\n";

$bootstrapPath = '/home/shacartc/school.tehub.in/bootstrap/app.php';
if (!file_exists($bootstrapPath)) {
    die("Laravel bootstrap not found\n");
}

$app = require_once $bootstrapPath;
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Services\SubscriptionService;

try {
    $subscriptionService = app(SubscriptionService::class);
    
    // Activate trial for school 15
    try {
        $subscriptionService->createSubscription(2, 15, null, 1);
        echo "Successfully activated Trial (30 days) for School ID 15\n";
    } catch (\Throwable $ex) {
        echo "School 15 trial error: " . $ex->getMessage() . "\n";
    }

    // Activate trial for school 16
    try {
        $subscriptionService->createSubscription(2, 16, null, 1);
        echo "Successfully activated Trial (30 days) for School ID 16\n";
    } catch (\Throwable $ex) {
        echo "School 16 trial error: " . $ex->getMessage() . "\n";
    }

    // Clear Spatie cache
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    // Clear caches via php artisan
    foreach ($sites as $site) {
        if (!is_dir($site)) continue;
        
        $cacheDir = "$site/bootstrap/cache";
        if (is_dir($cacheDir)) {
            foreach (glob("$cacheDir/*.php") as $f) @unlink($f);
        }
        $viewCache = "$site/storage/framework/views";
        if (is_dir($viewCache)) {
            foreach (glob("$viewCache/*.php") as $f) @unlink($f);
        }
        chdir($site);
        @shell_exec('php artisan cache:clear');
        @shell_exec('php artisan config:clear');
        @shell_exec('php artisan route:clear');
        @shell_exec('php artisan view:clear');
        echo "Cleared cache in $site\n";
    }

    echo "\n=== ALL TRIAL ACTIVATIONS COMPLETE ===\n";

} catch (\Throwable $e) {
    echo "MAIN ERROR: " . $e->getMessage() . "\n";
}

unlink(__FILE__);
?>
