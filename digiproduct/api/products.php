<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$products = [
    [
        'id' => 1,
        'name' => '5L+ USA Business Prospects',
        'slug' => 'usa-business-prospects',
        'description' => 'Comprehensive database of verified USA business contacts and leads.',
        'price' => 24900,
        'priceDisplay' => '₹249',
        'priceRaw' => 24900,
        'type' => 'digital',
        'deliveryType' => 'DOWNLOAD',
        'resourceReference' => ''
    ],
    [
        'id' => 2,
        'name' => 'India Business Leads Database',
        'slug' => 'india-business-leads',
        'description' => 'Targeted database of B2B business leads across key Indian industries.',
        'price' => 19900,
        'priceDisplay' => '₹199',
        'priceRaw' => 19900,
        'type' => 'digital',
        'deliveryType' => 'DOWNLOAD',
        'resourceReference' => ''
    ],
    [
        'id' => 3,
        'name' => 'ZAMZY Business Prospecting Bundle',
        'slug' => 'business-bundle',
        'description' => 'Get both USA Business Prospects and India Business Leads together at a special bundle price.',
        'price' => 34900,
        'priceDisplay' => '₹349',
        'priceRaw' => 34900,
        'type' => 'bundle',
        'deliveryType' => 'DOWNLOAD',
        'resourceReference' => ''
    ]
];

$addons = [
    [
        'id' => 4,
        'name' => 'Meta Ads Mastery Playbook & Templates',
        'slug' => 'meta-ads-mastery',
        'description' => 'Step-by-step Meta Ads frameworks and high-converting ad copy templates.',
        'price' => 4900,
        'priceDisplay' => '₹49',
        'priceRaw' => 4900,
        'type' => 'digital',
        'deliveryType' => 'DOWNLOAD',
        'isAddon' => 1
    ]
];

$dbFile = __DIR__ . '/../data/zamzy.db';
if (file_exists($dbFile)) {
    try {
        $db = new PDO('sqlite:' . $dbFile);
        $stmt = $db->query("SELECT id, name, slug, description, price, type, delivery_type, resource_reference, is_addon FROM products WHERE active = 1 ORDER BY sort_order ASC");
        $dbProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($dbProducts)) {
            $mainList = [];
            $addonList = [];
            foreach ($dbProducts as $p) {
                $formatted = [
                    'id' => (int)$p['id'],
                    'name' => $p['name'],
                    'slug' => $p['slug'],
                    'description' => $p['description'],
                    'price' => (int)$p['price'],
                    'priceDisplay' => '₹' . number_format($p['price'] / 100, 0),
                    'priceRaw' => (int)$p['price'],
                    'type' => $p['type'],
                    'deliveryType' => $p['delivery_type'],
                    'resourceReference' => $p['resource_reference'] ?? ''
                ];
                if (!empty($p['is_addon'])) {
                    $addonList[] = $formatted;
                } else {
                    $mainList[] = $formatted;
                }
            }
            if (!empty($mainList)) $products = $mainList;
            if (!empty($addonList)) $addons = $addonList;
        }
    } catch (Exception $e) {}
}

echo json_encode([
    'products' => $products,
    'addons' => $addons
]);
