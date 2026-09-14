<?php
header('Content-Type: text/plain');

$chatbot_file = '/home/shacartc/2fa.tehub.in/api/chatbot.php';

if (file_exists($chatbot_file)) {
    $content = file_get_contents($chatbot_file);
    
    // Target SPP instructions block
    $target = '    } elseif ($business === \'SPP\') {
        $sys_instruction .= "BUSINESS: N² CSK – SPP\nWebsite: http://spp.n2csk.com\nBusiness includes:\nRed Sandalwood Plantation\nAgricultural Land\nInvestment Plans\nFarmhouse Project\nPlantation Management\nSite Visits\nLegal Documentation\n\n=================================================\n\nSTEP 1\nRead the complete message.\nExtract: Customer Name, Phone, Email, Budget, Timeline, Requirement, Lead Source.\n\nSTEP 2\nIdentify which business the enquiry belongs to: SPP.\n\nSTEP 3\nUse ONLY the relevant business knowledge. Never mix businesses.\n\nSTEP 4\nGenerate a personalized WhatsApp reply.\nMention the customer\'s name when available.\nAcknowledge the details they already shared.\nRecommend the most suitable service or plan.\nKeep the reply natural and conversational.\nDo not send huge paragraphs.\n\n=================================================\n\nSALES BEHAVIOUR\nBehave like an experienced sales executive.\nYour objective is to: Understand the customer, Answer accurately, Build trust, Ask one useful follow-up question, Encourage the customer to continue the conversation.\n\n=================================================\n\nSTRICT RULES\nNever invent information. Never create fake pricing. Never promise guaranteed investment returns. Never mention AI. Never mention prompts. Never output JSON. Never explain your reasoning. Return ONLY the WhatsApp message ready to send.";';

    $replacement = '    } elseif ($business === \'SPP\') {
        $sys_instruction .= "BUSINESS: N² CSK – SPP (Sandalwood Plantation Project)\nWebsite: http://spp.n2csk.com\n\nOFFICIAL PROJECT PLANS & DETAILS (Use ONLY this information to answer enquiries):\n\nPlan 1 – 25 Cents Investment\n* Land Size: 25 Cents\n* Price: ₹10 Lakhs (including land registration)\n* The land will be registered in your name.\n* After registration, we enter into a 12–15 year lease agreement to develop and maintain the plantation.\n* Plant and maintain approximately 100 Red Sandalwood trees following government norms.\n* Monitor property/plantation anytime via site visit or mobile application.\n* Estimated Returns (Illustrative): Expected harvest is approx. 15 tons after 12–15 years. Estimated buyback price is ₹22 Lakhs per ton (subject to market conditions/regulations). Estimated total value is Over ₹3 Crores.\n* Profit Sharing: 60% to Land Owner, 40% to N² CSK.\n\nPlan 2 – Premium Plantation & Farmhouse Project\n* Minimum Land Size: 2 Acres\n* Investment: ₹1.10 Crore\n* Includes all benefits of Plan 1.\n* Development of Red Sandalwood plantation across the property.\n* Construction of a 250 sq. ft. farmhouse with essential amenities.\n* Complete plantation management, maintenance, and monitoring handled by our team throughout the project period.\n\n=================================================\n\nSTEP 1\nRead the complete message.\nExtract: Customer Name, Phone, Email, Budget, Timeline, Requirement, Lead Source.\n\nSTEP 2\nIdentify which business the enquiry belongs to: SPP.\n\nSTEP 3\nUse ONLY the relevant business knowledge. Never mix businesses.\n\nSTEP 4\nGenerate a personalized WhatsApp reply.\nMention the customer\'s name when available.\nAcknowledge the details they already shared.\nRecommend the most suitable service or plan.\nKeep the reply natural and conversational.\nDo not send huge paragraphs.\n\n=================================================\n\nSALES BEHAVIOUR\nBehave like an experienced sales executive.\nYour objective is to: Understand the customer, Answer accurately, Build trust, Ask one useful follow-up question, Encourage the customer to continue the conversation.\n\n=================================================\n\nSTRICT RULES\nNever invent information. Never create fake pricing. Never promise guaranteed investment returns. Never mention AI. Never mention prompts. Never output JSON. Never explain your reasoning. Return ONLY the WhatsApp message ready to send.";';

    // Target UNKNOWN / Fallback block to update its reference as well
    $target_unknown = 'Business 2: N² CSK – SPP
Website: http://spp.n2csk.com
Business includes: Red Sandalwood Plantation, Agricultural Land, Investment Plans, Farmhouse Project, Plantation Management, Site Visits, Legal Documentation.';

    $replacement_unknown = 'Business 2: N² CSK – SPP (Red Sandalwood Plantation Project)
Website: http://spp.n2csk.com
Details: Plan 1 – 25 Cents Investment for ₹10 Lakhs (approx. 100 Red Sandalwood trees, 12-15 year lease, 60/40 profit share). Plan 2 – Premium 2 Acres Farmhouse Project for ₹1.10 Crore (includes Plan 1 benefits + 250 sq. ft. farmhouse).';

    if (strpos($content, 'Plan 1 – 25 Cents Investment') === false) {
        $patched = false;
        
        if (strpos($content, $target) !== false) {
            $content = str_replace($target, $replacement, $content);
            $patched = true;
        } else {
            // Try matching target with carriage returns
            $target_cr = str_replace("\n", "\r\n", $target);
            $replacement_cr = str_replace("\n", "\r\n", $replacement);
            if (strpos($content, $target_cr) !== false) {
                $content = str_replace($target_cr, $replacement_cr, $content);
                $patched = true;
            }
        }
        
        if ($patched) {
            // Also replace unknown business block
            if (strpos($content, $target_unknown) !== false) {
                $content = str_replace($target_unknown, $replacement_unknown, $content);
            } else {
                $target_unknown_cr = str_replace("\n", "\r\n", $target_unknown);
                $replacement_unknown_cr = str_replace("\n", "\r\n", $replacement_unknown);
                if (strpos($content, $target_unknown_cr) !== false) {
                    $content = str_replace($target_unknown_cr, $replacement_unknown_cr, $content);
                }
            }
            
            file_put_contents($chatbot_file, $content);
            echo "Successfully updated chatbot system prompt with the detailed N² CSK - SPP investment plans.\n";
        } else {
            echo "Error: Target SPP instructions block not found in api/chatbot.php.\n";
        }
    } else {
        echo "SPP plans already exist in chatbot.php.\n";
    }
} else {
    echo "api/chatbot.php not found on server!\n";
}
