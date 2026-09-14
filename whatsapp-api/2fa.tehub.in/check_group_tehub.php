<?php
header('Content-Type: text/plain');

$url = 'https://2fa.tehub.in/whatsapp/groups?session=default';
$res = @file_get_contents($url);

if ($res) {
    $data = json_decode($res, true);
    if (isset($data['success']) && $data['success']) {
        echo "=== Groups found for +919566777266 ===\n";
        $found = false;
        foreach ($data['groups'] as $group) {
            $jid = $group['jid'] ?? '';
            $name = $group['name'] ?? '';
            echo "- $name ($jid)\n";
            if ($jid === '120363426187610725@g.us') {
                $found = true;
            }
        }
        
        if ($found) {
            echo "\nSTATUS: SUCCESS! +919566777266 IS a member of the group 'Tehub'.\n";
        } else {
            echo "\nSTATUS: WARNING! +919566777266 is NOT a member of the group 'Tehub' (120363426187610725@g.us)!\n";
        }
    } else {
        echo "Gateway error: " . ($data['error'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "Failed to connect to gateway.\n";
}
