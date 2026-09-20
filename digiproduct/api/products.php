<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$products = [
    [
        'id' => 1,
        'name' => '5L+ USA Business Prospects',
        'slug' => 'usa-business-prospects',
        'subtitle' => '5L+ Verified USA Business & Executive Leads',
        'badge' => 'USA DATABASE',
        'description' => 'Comprehensive database of verified USA business contacts, executive emails & decision-maker directory.',
        'price' => 24900,
        'priceDisplay' => '₹249',
        'priceRaw' => 24900,
        'oldPrice' => 99600,
        'oldPriceDisplay' => '₹996',
        'type' => 'digital',
        'deliveryType' => 'DOWNLOAD',
        'imageUrl' => '/digiproduct/assets/images/usa-prospects-mockup.jpg',
        'resourceReference' => ''
    ],
    [
        'id' => 2,
        'name' => 'India Business Leads Database',
        'slug' => 'india-business-leads',
        'subtitle' => '5L+ Verified Indian Business & Company Contacts',
        'badge' => 'INDIA DATABASE',
        'description' => 'Targeted database of B2B business leads across key Indian industries with company directories & verified sales leads.',
        'price' => 24900,
        'priceDisplay' => '₹249',
        'priceRaw' => 24900,
        'oldPrice' => 99600,
        'oldPriceDisplay' => '₹996',
        'type' => 'digital',
        'deliveryType' => 'DOWNLOAD',
        'imageUrl' => '/digiproduct/assets/images/india-leads-mockup.jpg',
        'resourceReference' => ''
    ],
    [
        'id' => 3,
        'name' => 'Complete Mega Growth Package & Database Bundle',
        'slug' => 'business-bundle',
        'subtitle' => 'USA Database + India Database + Meta Ads + 2 Free Bonuses',
        'badge' => 'MEGA BUNDLE',
        'description' => 'The ultimate all-in-one growth package: USA Database + India Database + Meta Ads Mastery + 2 Free Bonuses (Business Templates & AI Income Starter Kit).',
        'price' => 44900,
        'priceDisplay' => '₹449',
        'priceRaw' => 44900,
        'oldPrice' => 179600,
        'oldPriceDisplay' => '₹1,796',
        'type' => 'bundle',
        'deliveryType' => 'DOWNLOAD',
        'imageUrl' => '/digiproduct/assets/images/mega-bundle-mockup.jpg',
        'resourceReference' => ''
    ]
];

$addons = [
    [
        'id' => 4,
        'name' => 'Meta Ads Mastery Playbook & Templates',
        'slug' => 'meta-ads-mastery',
        'subtitle' => 'Step-by-step Frameworks & High-Converting Ad Copy',
        'badge' => 'META ADS',
        'description' => 'Complete step-by-step Meta Ads video training, ad copy frameworks, creatives & campaign templates.',
        'price' => 4900,
        'priceDisplay' => '₹49',
        'priceRaw' => 4900,
        'oldPrice' => 19600,
        'oldPriceDisplay' => '₹196',
        'type' => 'digital',
        'deliveryType' => 'DOWNLOAD',
        'imageUrl' => '/digiproduct/assets/images/meta-ads-mockup.jpg',
        'isAddon' => 1
    ]
];

$dbFile = __DIR__ . '/../data/zamzy.db';
if (file_exists($dbFile)) {
    try {
        $db = new PDO('sqlite:' . $dbFile);
        $stmt = $db->query("SELECT * FROM products WHERE active = 1 ORDER BY sort_order ASC, id ASC");
        $dbProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($dbProducts)) {
            $mainList = [];
            $addonList = [];
            foreach ($dbProducts as $p) {
                $oldPriceVal = !empty($p['old_price']) ? (int)$p['old_price'] : ((int)$p['price'] * 4);
                $img = $p['image_url'] ?? '';
                if (empty($img)) {
                    if ($p['slug'] === 'usa-business-prospects') $img = '/digiproduct/assets/images/usa-prospects-mockup.jpg';
                    elseif ($p['slug'] === 'india-business-leads') $img = '/digiproduct/assets/images/india-leads-mockup.jpg';
                    elseif ($p['slug'] === 'business-bundle' || $p['slug'] === 'mega-bundle') $img = '/digiproduct/assets/images/mega-bundle-mockup.jpg';
                    elseif ($p['slug'] === 'meta-ads-mastery') $img = '/digiproduct/assets/images/meta-ads-mockup.jpg';
                }

                $formatted = [
                    'id' => (int)$p['id'],
                    'name' => $p['name'],
                    'slug' => $p['slug'],
                    'subtitle' => $p['subtitle'] ?? '',
                    'badge' => $p['badge'] ?? '',
                    'description' => $p['description'] ?? '',
                    'price' => (int)$p['price'],
                    'priceDisplay' => '₹' . number_format($p['price'] / 100, 0),
                    'priceRaw' => (int)$p['price'],
                    'oldPrice' => $oldPriceVal,
                    'oldPriceDisplay' => '₹' . number_format($oldPriceVal / 100, 0),
                    'type' => $p['type'] ?? 'digital',
                    'deliveryType' => $p['delivery_type'] ?? 'DOWNLOAD',
                    'imageUrl' => $img,
                    'resourceReference' => $p['resource_reference'] ?? '',
                    'deliverables' => $p['deliverables'] ?? '',
                    'specifications' => $p['specifications'] ?? '',
                    'faqs' => $p['faqs'] ?? ''
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
