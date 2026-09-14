<?php
header('Content-Type: text/plain; charset=UTF-8');

define('LARAVEL_START', microtime(true));
require '/home/shacartc/school.tehub.in/vendor/autoload.php';
$app = require_once '/home/shacartc/school.tehub.in/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->boot();

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

// Authenticate as school 16 user
$user = User::where('school_id', 16)->first();
Auth::login($user);

// Switch database to school 16 DB
// Find school 16 database name
DB::setDefaultConnection('mysql');
$school = DB::table('schools')->where('id', 16)->first();
$schoolDb = $school->database_name;
echo "School Database: $schoolDb\n";

\App\Http\Middleware\SwitchDatabase::switchToSchool($schoolDb);

// Execute Controller index query logic
$today_date = Carbon::now()->format('Y-m-d');
$subscriptionService = app(SubscriptionService::class);
$current_plan = $subscriptionService->active_subscription(16);

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

$html = view('subscription.index', compact('packages', 'features', 'current_plan', 'settings','upcoming_package','paymentConfiguration','system_settings'))->render();

file_put_contents('/home/shacartc/school.tehub.in/rendered_view.html', $html);
echo "Rendered view successfully. Saved to rendered_view.html.\n";

// Let's print out all script tags containing "razorpay" or "checkout"
preg_match_all('/<script\b[^>]*>(.*?)<\/script>/is', $html, $matches);
foreach ($matches[0] as $idx => $script) {
    if (strpos($script, 'razorpay-button') !== false || strpos($script, 'checkout') !== false) {
        echo "\n==================== Script Tag " . ($idx + 1) . " ====================\n";
        echo $script . "\n";
    }
}

unlink(__FILE__);
?>
