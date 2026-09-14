<?php
header('Content-Type: text/plain; charset=UTF-8');

// =========================================================================
// DEPLOY: SchoolDataService.php with filtered School Admin permissions
// Also update existing schools' School Admin roles to exclude Super Admin perms
// =========================================================================

// Read the updated SchoolDataService.php from local project
$localDataServicePath = 'c:/xampp/htdocs/TEHSCH/school_saas/app/Services/SchoolDataService.php';
if (!file_exists($localDataServicePath)) {
    die("Local SchoolDataService.php not found at $localDataServicePath\n");
}
$dataServiceContent = file_get_contents($localDataServicePath);

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
    
    // Backup and write
    $targetPath = "$site/app/Services/SchoolDataService.php";
    $backupDir = "$site/backups_permission_fix_" . date('Ymd');
    if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
    
    if (file_exists($targetPath)) {
        copy($targetPath, "$backupDir/SchoolDataService.php.bak");
    }
    
    file_put_contents($targetPath, $dataServiceContent);
    echo "Deployed updated SchoolDataService.php to $targetPath (" . strlen($dataServiceContent) . " bytes)\n";
}

echo "\nRe-syncing School Admin permissions for all existing school databases...\n";

// Execute Laravel environment bootstrap to run the roles update using Eloquent/Spatie inside Laravel
// We can use school.tehub.in bootstrap
$bootstrapPath = '/home/shacartc/school.tehub.in/bootstrap/app.php';
if (!file_exists($bootstrapPath)) {
    die("Laravel bootstrap not found at $bootstrapPath\n");
}

// Bootstrap Laravel
$app = require_once $bootstrapPath;
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\School;
use App\Services\SchoolDataService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

try {
    $schools = School::on('mysql')->withTrashed()->get();
    echo "Found " . $schools->count() . " schools in central DB.\n";
    
    $schoolService = app(SchoolDataService::class);
    
    foreach ($schools as $school) {
        echo "Processing school ID {$school->id}: {$school->name} (DB: {$school->database_name})...\n";
        
        try {
            // Switch connection
            Config::set('database.connections.school.database', $school->database_name);
            DB::purge('school');
            DB::connection('school')->reconnect();
            DB::setDefaultConnection('school');
            
            // Re-run createPermissions and createSchoolAdminRole
            $schoolService->createPermissions();
            $schoolService->createSchoolAdminRole($school);
            
            // Also clear Spatie permission cache inside this school DB context
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            
            echo "  Successfully updated School Admin role and permissions!\n";
        } catch (\Throwable $ex) {
            echo "  ERROR processing school ID {$school->id}: " . $ex->getMessage() . "\n";
        }
    }
    
    // Switch back to mysql
    DB::purge('school');
    DB::setDefaultConnection('mysql');
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    
    // Clear all global caches
    foreach ($sites as $site) {
        $cacheDir = "$site/bootstrap/cache";
        if (is_dir($cacheDir)) {
            foreach (glob("$cacheDir/*.php") as $f) unlink($f);
        }
        $viewCache = "$site/storage/framework/views";
        if (is_dir($viewCache)) {
            foreach (glob("$viewCache/*.php") as $f) unlink($f);
        }
        // Run artisan commands via execution
        chdir($site);
        shell_exec('php artisan cache:clear');
        shell_exec('php artisan config:clear');
        shell_exec('php artisan route:clear');
        shell_exec('php artisan view:clear');
        echo "Cleared cache via php artisan in $site\n";
    }
    
    echo "\n=== ALL COMPLETED SUCCESSFULLY ===\n";

} catch (\Throwable $e) {
    echo "MAIN SCRIPT ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

unlink(__FILE__);
?>
