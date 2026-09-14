<?php
header("Content-Type: text/plain");
echo "STANDALONE_SCHOOL: " . (env("STANDALONE_SCHOOL") ? "true" : "false") . "\n";
echo "STANDALONE_SCHOOL_DB: " . env("STANDALONE_SCHOOL_DB") . "\n";
echo "DB_DATABASE: " . env("DB_DATABASE") . "\n";
if (file_exists("/home/shacartc/school.tehub.in/.env")) {
    $env_lines = file("/home/shacartc/school.tehub.in/.env");
    foreach ($env_lines as $line) {
        if (strpos($line, "DB_") === 0 || strpos($line, "STANDALONE") === 0 || strpos($line, "APP_URL") === 0) {
            echo trim($line) . "\n";
        }
    }
} else {
    echo ".env not found\n";
}
@unlink(__FILE__);
?>