<?php
header('Content-Type: text/plain');
echo "=== RUNNING PROCESSES ===\n";
echo shell_exec("ps -ef | grep node | grep -v grep 2>&1");
