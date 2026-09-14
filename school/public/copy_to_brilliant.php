<?php
set_time_limit(600);
error_reporting(E_ALL);
ini_set("display_errors", 1);

header("Content-Type: text/plain");
echo "=== Copying school.tehub.in to brilliantbca.com ===\n\n";

$source = "/home/shacartc/school.tehub.in";
$dest = "/home/shacartc/brilliantbca.com";

// Check if source exists
if (!is_dir($source)) {
    echo "ERROR: Source directory not found: $source\n";
    exit(1);
}

// Create destination if not exists
if (!is_dir($dest)) {
    mkdir($dest, 0755, true);
    echo "Created destination directory: $dest\n";
} else {
    echo "Destination directory already exists: $dest\n";
}

// Use shell command for efficient copying
echo "Starting copy (this may take a while)...\n";
$cmd = "cp -r $source/* $dest/ 2>&1";
$output = shell_exec($cmd);
echo "Copy command output: " . ($output ?: "Success (no output)") . "\n";

// Also copy hidden files like .env, .htaccess
$cmd2 = "cp $source/.env $dest/.env 2>&1";
$output2 = shell_exec($cmd2);
echo ".env copy: " . ($output2 ?: "Success") . "\n";

$cmd3 = "cp $source/public/.htaccess $dest/public/.htaccess 2>&1";
$output3 = shell_exec($cmd3);
echo ".htaccess copy: " . ($output3 ?: "Success") . "\n";

// Verify
if (is_dir("$dest/public") && is_file("$dest/public/index.php")) {
    echo "\nSUCCESS: Codebase copied successfully!\n";
    echo "Files in public/: " . count(scandir("$dest/public")) . "\n";
    echo "Files in app/: " . count(scandir("$dest/app")) . "\n";
} else {
    echo "\nERROR: Copy may have failed. public/index.php not found.\n";
}

// Set permissions
shell_exec("chmod -R 755 $dest");
shell_exec("chmod -R 775 $dest/storage");
shell_exec("chmod -R 775 $dest/bootstrap/cache");
echo "Permissions set.\n";

echo "\nDone.\n";
?>