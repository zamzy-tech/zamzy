<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain; charset=UTF-8');

try {
    define('LARAVEL_START', microtime(true));
    require '/home/shacartc/school.tehub.in/vendor/autoload.php';
    $app = require_once '/home/shacartc/school.tehub.in/bootstrap/app.php';

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $app->boot();

    echo "Laravel Booted.\n";

    // 1. Authenticate user
    use App\Models\User;
    use App\Models\Subscription;
    use App\Models\Package;
    use App\Models\Feature;
    use App\Models\PaymentConfiguration;
    use App\Services\SubscriptionService;
    use App\Services\CachingService;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;

    $user = User::where('school_id', 16)->first();
    Auth::login($user);
    echo "User Authenticated: " . $user->email . "\n";

    // 2. Switch DB
    DB::setDefaultConnection('mysql');
    $school = DB::table('schools')->where('id', 16)->first();
    $schoolDb = $school->database_name;
    echo "School Database: $schoolDb\n";

    \App\Http\Middleware\SwitchDatabase::switchToSchool($schoolDb);
    echo "Database switched successfully.\n";

    // 3. Retrieve variables
    $today_date = Carbon::now()->format('Y-m-d');
    $subscriptionService = app(SubscriptionService::class);
    $current_plan = $subscriptionService->active_subscription(16);
    echo "Active subscription ID: " . ($current_plan ? $current_plan->id : 'None') . "\n";

    $upcoming_package = '';
    if ($current_plan) {
        $upcoming_package = Subscription::whereDate('start_date','>=',$current_plan->end_date)->whereHas('subscription_bill.transaction',function($q) {
            $q->where('payment_status',"succeed");
        })->first();
    }

    $packages = Package::with('package_feature.feature')->where('status', 1)->orderBy('rank', 'ASC')->where('is_trial', 0)->get();
    $features = Feature::ActiveFeatures()->get();
    $settings = app(CachingService::class)->getSystemSettings();
    $system_settings = $settings;

    DB::setDefaultConnection('mysql');
    $paymentConfiguration = PaymentConfiguration::on('mysql')->where('school_id', null)->where('status',1)->first();

    \App\Http\Middleware\SwitchDatabase::switchToSchool($schoolDb);

    echo "Compiling view...\n";
    $html = view('subscription.index', compact('packages', 'features', 'current_plan', 'settings','upcoming_package','paymentConfiguration','system_settings'))->render();
    echo "View compiled successfully. HTML length: " . strlen($html) . "\n";

    preg_match_all('/<script\b[^>]*>(.*?)<\/script>/is', $html, $matches);
    foreach ($matches[0] as $idx => $script) {
        if (strpos($script, 'razorpay-button') !== false || strpos($script, 'checkout') !== false) {
            echo "\n==================== Script Tag " . ($idx + 1) . " ====================\n";
            echo $script . "\n";
        }
    }

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

unlink(__FILE__);
?>
