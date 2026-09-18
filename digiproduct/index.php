<?php
// ZAMZY Digital Products — Apache PHP Entry Point Fallback
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim($uri, '/');

// Handle sub-pages
if ($uri === 'digiproduct' || $uri === 'digiproducts' || $uri === 'digiproduct/index.php' || $uri === '') {
    include __DIR__ . '/public/index.html';
    exit;
}

if (strpos($uri, 'digiproduct/products') !== false || strpos($uri, 'digiproducts/products') !== false) {
    include __DIR__ . '/public/products.html';
    exit;
}

if (strpos($uri, 'digiproduct/checkout') !== false || strpos($uri, 'digiproducts/checkout') !== false) {
    include __DIR__ . '/public/checkout.html';
    exit;
}

if (strpos($uri, 'digiproduct/payment-success') !== false || strpos($uri, 'digiproducts/payment-success') !== false) {
    include __DIR__ . '/public/payment-success.html';
    exit;
}

if (strpos($uri, 'digiproduct/access') !== false || strpos($uri, 'digiproducts/access') !== false) {
    include __DIR__ . '/public/access.html';
    exit;
}

if (strpos($uri, 'digiproduct/recover') !== false || strpos($uri, 'digiproducts/recover') !== false) {
    include __DIR__ . '/public/recover.html';
    exit;
}

// Default fallback
include __DIR__ . '/public/index.html';
