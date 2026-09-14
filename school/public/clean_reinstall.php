<?php
header("Content-Type: text/plain");
set_time_limit(600);

$base = "/home/shacartc/bms.tehub.in";
chdir($base);

function deleteDirRecursive($dirPath) {
    if (!is_dir($dirPath)) {
        return;
    }
    $files = array_diff(scandir($dirPath), [".", ".."]);
    foreach ($files as $file) {
        $filePath = "$dirPath/$file";
        if (is_dir($filePath)) {
            deleteDirRecursive($filePath);
        } else {
            unlink($filePath);
        }
    }
    rmdir($dirPath);
}

echo "=== Clean Reinstall ===\n";

// 1. Delete vendor directory
echo "Deleting vendor folder...\n";
if (is_dir("$base/vendor")) {
    deleteDirRecursive("$base/vendor");
    echo "Vendor folder deleted.\n";
} else {
    echo "Vendor folder not found.\n";
}

// 2. Clear database tables again
echo "Clearing database tables...\n";
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=shacartc_bms_saas", "shacartc_school", "School@786");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) {
        $pdo->exec("DROP TABLE `$t`");
        echo "  Dropped table: $t\n";
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Database cleared.\n";
} catch (Exception $e) {
    echo "Database clear failed: " . $e->getMessage() . "\n";
}

// 3. Set Composer env
putenv("COMPOSER_HOME=" . $base . "/.composer");
if (!is_dir($base . "/.composer")) {
    mkdir($base . "/.composer", 0755, true);
}

// 4. Run composer install
echo "\nRunning composer install...\n";
$composerPath = "$base/composer.phar";
if (!file_exists($composerPath)) {
    echo "Downloading composer.phar...\n";
    file_put_contents($composerPath, file_get_contents("https://getcomposer.org/download/latest-stable/composer.phar"));
}
exec("php $composerPath install --no-dev --optimize-autoloader 2>&1", $output);
echo implode("\n", $output) . "\n";

// 5. Run migrations
echo "\nRunning migrations...\n";
unset($migOutput);
exec("php artisan migrate --force 2>&1", $migOutput);
echo implode("\n", $migOutput) . "\n";

// 6. Run seeders
echo "\nRunning seeders...\n";
unset($seedOutput);
exec("php artisan db:seed --force 2>&1", $seedOutput);
echo implode("\n", $seedOutput) . "\n";

// 7. Clear cache
echo "\nClearing cache...\n";
exec("php artisan config:clear 2>&1");
exec("php artisan view:clear 2>&1");
exec("php artisan route:clear 2>&1");
exec("php artisan cache:clear 2>&1");
echo "Cache cleared.\n";

echo "\n=== CLEAN REINSTALL COMPLETE ===\n";
?>