<?php
header('Content-Type: text/plain');

$envPath = '/home/shacartc/sch.brilliantbca.com/.env';
if (!file_exists($envPath)) {
    die("Error: .env not found at $envPath\n");
}

$content = file_get_contents($envPath);

// Define the changes we want
$changes = [
    'APP_NAME' => '"BRILLIANT CHILDRENS ACADEMY"',
    'APP_URL' => 'https://brilliantbca.com',
    'STANDALONE_SCHOOL' => 'true',
    'STANDALONE_SCHOOL_CODE' => 'TEH20261',
    'STANDALONE_SCHOOL_DB' => 'shacartc_eschool_saas_1_brilliant'
];

foreach ($changes as $key => $val) {
    // Check if key already exists
    if (preg_match("/^$key=/m", $content)) {
        // Replace existing key
        $content = preg_replace("/^$key=.*$/m", "$key=$val", $content);
        echo "Updated $key to $val\n";
    } else {
        // Append new key
        $content .= "\n$key=$val";
        echo "Appended $key=$val\n";
    }
}

if (file_put_contents($envPath, $content) !== false) {
    echo "Successfully updated $envPath!\n";
} else {
    echo "Error: Failed to write to $envPath\n";
}
?>
