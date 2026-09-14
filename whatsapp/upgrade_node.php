<?php
// Helper script to automatically copy the multi-session server.js to the Node.js app root in cPanel
echo "<h2>⚡ WhatsApp Gateway Auto-Updater</h2>";

$source = __DIR__ . '/whatsapp/server.js';
$destination = '/home/shacartc/whatsapp-service/server.js';

if (!file_exists($source)) {
    echo "<p style='color:red;'>❌ Error: Source file not found at: $source</p>";
    exit;
}

// Try to copy the file
if (copy($source, $destination)) {
    echo "<p style='color:green;'>✅ <strong>Success!</strong> Multi-session file successfully copied to: <code>$destination</code></p>";
    echo "<p>👉 <strong>Final Step:</strong> You must now go to cPanel -> <strong>'Setup Node.js App'</strong> and click <strong>'Restart'</strong> next to your WhatsApp application to apply the changes.</p>";
} else {
    echo "<p style='color:red;'>❌ Error: Failed to copy file to $destination. Please verify if the path is correct or copy the file manually.</p>";
}
