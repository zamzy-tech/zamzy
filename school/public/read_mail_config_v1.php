<?php
header('Content-Type: application/json');

function parseLaravelEnv($path) {
    if (!file_exists($path)) {
        return [];
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $config = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            $parts = explode('=', $line, 2);
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            if (preg_match('/^"([^"]*)"$/', $val, $matches)) $val = $matches[1];
            elseif (preg_match('/^\'([^\']*)\'$/', $val, $matches)) $val = $matches[1];
            $config[$key] = $val;
        }
    }
    return $config;
}

$env = parseLaravelEnv('../.env');
$mail_keys = [
    'MAIL_MAILER',
    'MAIL_HOST',
    'MAIL_PORT',
    'MAIL_USERNAME',
    'MAIL_PASSWORD',
    'MAIL_ENCRYPTION',
    'MAIL_FROM_ADDRESS',
    'MAIL_FROM_NAME'
];

$res = [];
foreach ($mail_keys as $key) {
    $val = $env[$key] ?? 'NOT_SET';
    if ($key === 'MAIL_PASSWORD' && $val !== 'NOT_SET') {
        $res[$key] = substr($val, 0, 2) . '...' . strlen($val);
    } else {
        $res[$key] = $val;
    }
}

echo json_encode($res, JSON_PRETTY_PRINT);
?>
