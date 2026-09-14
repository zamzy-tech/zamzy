<?php
// We will bootstrap Laravel on the remote server and render the subscriptions page for user with ID belonging to school 16 (e.g. Yaseer Arafath)
header('Content-Type: text/plain; charset=UTF-8');

define('LARAVEL_START', microtime(true));
require '/home/shacartc/school.tehub.in/vendor/autoload.php';
$app = require_once '/home/shacartc/school.tehub.in/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Find a school admin user for school 16
use App\Models\User;
use Illuminate\Support\Facades\Auth;

$user = User::role('School Admin')->where('school_id', 16)->first();
if (!$user) {
    echo "No school admin found for school 16\n";
    exit;
}

// Log in as this user
Auth::login($user);

// Now mock the request to /subscriptions
$request = Illuminate\Http\Request::create('/subscriptions', 'GET');
$response = $app->handle($request);

$html = $response->getContent();

// Save the rendered HTML to scratch/rendered.html for inspection if needed
file_put_contents('/home/shacartc/school.tehub.in/rendered.html', $html);
echo "Rendered HTML saved. Let's dump the script tags at the bottom:\n";

// Match all script tags at the bottom of the page
preg_match_all('/<script\b[^>]*>(.*?)<\/script>/is', $html, $matches);
foreach ($matches[0] as $idx => $script) {
    if (strpos($script, 'razorpay-button') !== false || strpos($script, 'checkout.js') !== false) {
        echo "\n--- Script Tag " . ($idx + 1) . " ---\n";
        echo $script . "\n";
    }
}

unlink(__FILE__);
?>
