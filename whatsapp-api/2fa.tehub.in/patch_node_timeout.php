<?php
header('Content-Type: text/plain');

$files_to_patch = [
    '/home/shacartc/whatsapp-service/server.js',
    '/home/shacartc/2fa.tehub.in/whatsapp/server.js'
];

foreach ($files_to_patch as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        $old_timeout = 'timeout: 5000';
        $new_timeout = 'timeout: 20000';
        
        if (strpos($content, $old_timeout) !== false) {
            $content = str_replace($old_timeout, $new_timeout, $content);
            file_put_contents($file, $content);
            echo "Successfully patched timeout to 20000 in $file\n";
        } else {
            if (strpos($content, $new_timeout) !== false) {
                echo "Timeout is already 20000 in $file\n";
            } else {
                echo "Warning: Could not find '$old_timeout' in $file\n";
            }
        }
        
        // Touch restart.txt in the application's tmp folder to restart Passenger
        $app_dir = dirname($file);
        $restart_dir = $app_dir . '/tmp';
        if (!file_exists($restart_dir)) {
            mkdir($restart_dir, 0755, true);
        }
        $restart_file = $restart_dir . '/restart.txt';
        touch($restart_file);
        echo "Touched restart trigger at $restart_file\n";
    } else {
        echo "File does not exist: $file\n";
    }
}
