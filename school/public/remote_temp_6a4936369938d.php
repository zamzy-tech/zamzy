<?php
header('Content-Type: text/plain; charset=UTF-8');

// =========================================================================
// DEPLOY: Update SchoolDataService.php on remote server using string replace
// Also update existing schools' School Admin roles to exclude Super Admin perms
// =========================================================================

$targetContent = <<<'PHP'
    public function createSchoolAdminRole($school) {
        $role = Role::withoutGlobalScope('school')->updateOrCreate(['name' => 'School Admin', 'custom_role' => 0, 'editable' => 0, 'school_id' => $school->id]);
        $SchoolAdminHasAccessTo = Permission::pluck('name')->toArray();
        
        $role->syncPermissions($SchoolAdminHasAccessTo);
    }
PHP;

$replacementContent = <<<'PHP'
    public function createSchoolAdminRole($school) {
        $role = Role::withoutGlobalScope('school')->updateOrCreate(['name' => 'School Admin', 'custom_role' => 0, 'editable' => 0, 'school_id' => $school->id]);
        
        $superAdminPermissions = [
            'schools-list', 'schools-create', 'schools-edit', 'schools-delete',
            'package-list', 'package-create', 'package-edit', 'package-delete',
            'addons-list', 'addons-create', 'addons-edit', 'addons-delete',
            'guidance-list', 'guidance-create', 'guidance-edit', 'guidance-delete',
            'system-setting-manage', 'app-settings', 'fcm-setting-create', 'fcm-setting-manage',
            'email-setting-create', 'subscription-settings', 'subscription-change-bills',
            'school-terms-condition', 'web-settings', 'custom-school-email',
            'school-custom-field-list', 'school-custom-field-create', 'school-custom-field-edit', 'school-custom-field-delete'
        ];
        
        $SchoolAdminHasAccessTo = Permission::whereNotIn('name', $superAdminPermissions)->pluck('name')->toArray();
        
        $role->syncPermissions($SchoolAdminHasAccessTo);
    }
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
    
    $targetPath = "$site/app/Services/SchoolDataService.php";
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
        $backupDir = "$site/backups_permission_fix_" . date('Ymd');
        if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
        copy($targetPath, "$backupDir/SchoolDataService.php.bak");
        
        file_put_contents($targetPath, $content);
        echo "Successfully modified $targetPath\n";
    } else {
        echo "NO CHANGE or pattern not found in $targetPath\n";
    }
}

echo "\nRe-syncing School Admin permissions for all existing school databases...\n";

// Bootstrap Laravel
$bootstrapPath = '/home/shacartc/school.tehub.in/bootstrap/app.php';
if (!file_exists($bootstrapPath)) {
    die("Laravel bootstrap not found at $bootstrapPath\n");
}

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
            foreach (glob("$cacheDir/*.php") as $f) @unlink($f);
        }
        $viewCache = "$site/storage/framework/views";
        if (is_dir($viewCache)) {
            foreach (glob("$viewCache/*.php") as $f) @unlink($f);
        }
        // Run artisan commands
        chdir($site);
        @shell_exec('php artisan cache:clear');
        @shell_exec('php artisan config:clear');
        @shell_exec('php artisan route:clear');
        @shell_exec('php artisan view:clear');
        echo "Cleared cache via php artisan in $site\n";
    }
    
    echo "\n=== ALL COMPLETED SUCCESSFULLY ===\n";

} catch (\Throwable $e) {
    echo "MAIN SCRIPT ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

unlink(__FILE__);
?>
