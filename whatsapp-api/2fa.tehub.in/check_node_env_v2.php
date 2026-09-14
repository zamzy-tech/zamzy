<?php
header('Content-Type: text/plain');
echo "=== Listing nodevenv modules ===\n";
echo shell_exec('ls -la /home/shacartc/nodevenv/whatsapp-service/20/lib/node_modules 2>&1');

echo "\n=== Listing nodevenv lib folder ===\n";
echo shell_exec('ls -la /home/shacartc/nodevenv/whatsapp-service/20/lib 2>&1');

echo "\n=== Listing nodevenv base folder ===\n";
echo shell_exec('ls -la /home/shacartc/nodevenv/whatsapp-service/20 2>&1');
