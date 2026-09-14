<?php
// Developer API documentation page
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$api_url = $protocol . $_SERVER['HTTP_HOST'] . str_replace('api_docs.php', 'api/whatsapp.php', $_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>WhatsApp API Documentation &amp; Reference — ZAMZY</title>
<link rel="shortcut icon" href="zamzy_logo.png" type="image/png">
<link rel="icon" href="zamzy_logo.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700;800&family=IBM+Plex+Mono:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Inter', sans-serif;
    background: #05060b;
    color: #f8fafc;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background-image: 
      radial-gradient(circle at 15% 15%, rgba(157, 78, 221, 0.12) 0%, transparent 45%),
      radial-gradient(circle at 85% 85%, rgba(0, 255, 204, 0.10) 0%, transparent 45%),
      linear-gradient(to bottom, #05060b, #070913);
    background-attachment: fixed;
  }
  
  /* Header */
  header {
    background: rgba(10, 12, 22, 0.85);
    backdrop-filter: blur(16px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    padding: 16px 36px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 100;
  }
  .logo-block .logo {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 20px;
    font-weight: 800;
    letter-spacing: 0.5px;
    color: #00ffcc;
  }
  .logo-block .tagline {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    color: #94a3b8;
    margin-top: 2px;
  }
  .badge-api {
    background: rgba(0, 255, 204, 0.1);
    color: #00ffcc;
    border: 1px solid rgba(0, 255, 204, 0.3);
    border-radius: 20px;
    padding: 4px 14px;
    font-size: 11.5px;
    font-weight: 700;
    font-family: 'IBM Plex Mono', monospace;
  }
  
  /* Layout */
  .layout {
    display: flex;
    flex: 1;
    max-width: 100%;
    margin: 0 auto;
    width: 100%;
  }
  
  /* Main Content Column */
  .docs-body {
    flex: 1;
    padding: 40px;
    border-right: 1px solid rgba(255,255,255,0.08);
  }
  
  /* Right Side Code Panel */
  .code-panel {
    width: 480px;
    padding: 40px;
    position: sticky;
    top: 73px;
    height: calc(100vh - 73px);
    overflow-y: auto;
    background: #0b0f19;
  }
  
  h1 { font-size: 32px; font-weight: 700; color: #f8fafc; margin-bottom: 16px; }
  h2 { font-size: 20px; font-weight: 700; color: #f8fafc; margin-top: 40px; margin-bottom: 16px; border-bottom: 1px solid #334155; padding-bottom: 8px; }
  p { font-size: 15px; color: #94a3b8; line-height: 1.7; margin-bottom: 16px; }
  
  code { font-family: 'Fira Code', monospace; font-size: 13px; background: #1e293b; padding: 2px 6px; border-radius: 4px; color: #f472b6; }
  
  /* Endpoint Tag */
  .endpoint-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #1e293b;
    border: 1px solid #334155;
    padding: 10px 16px;
    border-radius: 8px;
    font-family: 'Fira Code', monospace;
    font-size: 14px;
    margin-bottom: 24px;
  }
  .method-post {
    background: #10b981;
    color: #fff;
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: 700;
    font-size: 11px;
    text-transform: uppercase;
  }
  
  /* Params table */
  table { width: 100%; border-collapse: collapse; margin: 20px 0; }
  th { background: #1e293b; color: #94a3b8; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px; text-align: left; }
  td { padding: 14px 12px; border-bottom: 1px solid #334155; font-size: 14px; vertical-align: top; }
  .param-name { font-family: 'Fira Code', monospace; color: #38bdf8; font-weight: 600; }
  .param-type { font-size: 12px; color: #64748b; font-style: italic; }
  .required-badge { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); font-size: 9px; text-transform: uppercase; font-weight: 700; padding: 1px 4px; border-radius: 4px; display: inline-block; }
  .optional-badge { background: rgba(148, 163, 184, 0.1); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.2); font-size: 9px; text-transform: uppercase; font-weight: 700; padding: 1px 4px; border-radius: 4px; display: inline-block; }
  
  /* Tab System */
  .tabs { display: flex; gap: 8px; border-bottom: 1px solid #334155; margin-bottom: 16px; }
  .tab-btn { background: none; border: none; color: #94a3b8; font-family: inherit; font-size: 13px; font-weight: 600; padding: 8px 16px; cursor: pointer; transition: all 0.2s; border-bottom: 2px solid transparent; }
  .tab-btn:hover { color: #f8fafc; }
  .tab-btn.active { color: #38bdf8; border-bottom-color: #38bdf8; }
  
  .tab-content { display: none; }
  .tab-content.active { display: block; }
  
  /* Code blocks */
  pre {
    background: #0f172a;
    border: 1px solid #334155;
    border-radius: 8px;
    padding: 16px;
    font-family: 'Fira Code', monospace;
    font-size: 12px;
    color: #e2e8f0;
    overflow-x: auto;
    line-height: 1.5;
    margin-bottom: 24px;
    position: relative;
  }
  .btn-copy-code {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(255,255,255,0.05);
    border: 1px solid #334155;
    color: #94a3b8;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    cursor: pointer;
    font-family: inherit;
  }
  .btn-copy-code:hover { background: rgba(255,255,255,0.1); color: #fff; }
  
  /* Notes Box */
  .info-box {
    background: rgba(56, 189, 248, 0.05);
    border: 1.5px solid rgba(56, 189, 248, 0.15);
    border-left: 4px solid #38bdf8;
    border-radius: 8px;
    padding: 16px 20px;
    margin: 20px 0;
  }
  .info-box-title { font-weight: 700; color: #38bdf8; margin-bottom: 6px; font-size: 14px; text-transform: uppercase; }
  
  @media(max-width: 1024px) {
    .layout { flex-direction: column; }
    .docs-body { border-right: none; }
    .code-panel { width: 100%; position: static; height: auto; }
  }
</style>
</head>
<body>

<header>
  <div class="logo-block">
    <div class="logo">⚡ The Expert Hub</div>
    <div class="tagline">WhatsApp API Integrations</div>
  </div>
  <div class="badge-api">API v1.0.0</div>
</header>

<div class="layout">

  <!-- Left Side: Markdown Documentation -->
  <div class="docs-body">
    <h1>WhatsApp Message Dispatch API</h1>
    <p>Welcome to the WhatsApp Messaging API reference page. Exposing a secure, reliable, and credit-checked gateway API, this interface allows third-party applications to trigger WhatsApp dispatches instantly.</p>
    
    <h2>Endpoint Reference</h2>
    <div class="endpoint-tag">
      <span class="method-post">POST</span>
      <span>/api/whatsapp.php</span>
    </div>
    
    <h2>Authentication</h2>
    <p>Authenticate your API requests by providing your API key as a Bearer token in the <code>Authorization</code> header:</p>
    <pre>Authorization: Bearer YOUR_API_KEY_HERE</pre>
    <p>Alternatively, you may pass the API key in the post payload or URL query string under the <code>api_key</code> parameter (not recommended for production).</p>

    <div class="info-box">
      <div class="info-box-title">Credit System Rules</div>
      <p style="margin-bottom:0;">All accounts trigger a balance validation. 
      Successful WhatsApp dispatches deduct <strong>1 credit</strong>. 
      If your account is configured as <strong>Unlimited</strong>, your credit balance remains untouched. 
      If your credit balance drops to 0, requests are rejected with an <code>HTTP 402 Payment Required</code> code.</p>
    </div>

    <h2>Request Body Parameters</h2>
    <table>
      <thead>
        <tr>
          <th>Field</th>
          <th>Type &amp; Status</th>
          <th>Description</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="param-name">to</td>
          <td>
            <span class="param-type">string</span><br>
            <span class="required-badge">Required</span>
          </td>
          <td>The recipient WhatsApp number. Symbols, dashes, and country codes (e.g. <code>+91 98765-43210</code>) are automatically cleaned.</td>
        </tr>
        <tr>
          <td class="param-name">message</td>
          <td>
            <span class="param-type">string</span><br>
            <span class="required-badge">Required</span>
          </td>
          <td>The text content of your WhatsApp message. Supports WhatsApp formatting: <code>*bold*</code>, <code>_italics_</code>, <code>~strike~</code>.</td>
        </tr>
        <tr>
          <td class="param-name">type</td>
          <td>
            <span class="param-type">string</span><br>
            <span class="optional-badge">Optional</span>
          </td>
          <td>Categorizes the message for logs. Supported types: 
            <code>otp</code>, <code>promotion</code>, <code>invoice</code>, <code>report</code>, <code>general</code>. Defaults to <code>general</code>.
          </td>
        </tr>
        <tr>
          <td class="param-name">pdf</td>
          <td>
            <span class="param-type">string</span><br>
            <span class="optional-badge">Optional</span>
          </td>
          <td>Base64 encoded string of the PDF file to attach (recommended for <code>invoice</code> and <code>report</code> types).</td>
        </tr>
        <tr>
          <td class="param-name">filename</td>
          <td>
            <span class="param-type">string</span><br>
            <span class="optional-badge">Optional</span>
          </td>
          <td>Custom name for the attached PDF document. Defaults to <code>document.pdf</code>.</td>
        </tr>
      </tbody>
    </table>

    <h2>HTTP Status Responses</h2>
    <table>
      <thead>
        <tr>
          <th>Code</th>
          <th>Condition</th>
          <th>JSON Payload Response</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><code>200 OK</code></td>
          <td>Message sent successfully.</td>
          <td><code>{"success":true,"message":"Message sent successfully.","message_id":"...","credits_remaining":142}</code></td>
        </tr>
        <tr>
          <td><code>400 Bad Request</code></td>
          <td>Missing required fields (e.g. target or message text empty).</td>
          <td><code>{"success":false,"error":"Bad Request: Recipient phone number (to) is required."}</code></td>
        </tr>
        <tr>
          <td><code>401 Unauthorized</code></td>
          <td>Incorrect, missing, or malformed API Key.</td>
          <td><code>{"success":false,"error":"Unauthorized: Invalid API Key."}</code></td>
        </tr>
        <tr>
          <td><code>402 Payment Required</code></td>
          <td>Out of credits on a limited account balance.</td>
          <td><code>{"success":false,"error":"Payment Required: Your account is out of credits."}</code></td>
        </tr>
        <tr>
          <td><code>403 Forbidden</code></td>
          <td>The API Key is currently suspended by the Admin.</td>
          <td><code>{"success":false,"error":"Forbidden: This API key is suspended."}</code></td>
        </tr>
        <tr>
          <td><code>502 Bad Gateway</code></td>
          <td>The underlying WhatsApp server gateway failed to process the request.</td>
          <td><code>{"success":false,"error":"Message dispatch failed: HTTP Request error: ..."}</code></td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Right Side: Code Examples Panel -->
  <div class="code-panel">
    <h3 style="color: #f8fafc; margin-bottom: 20px;">Code Integration Examples</h3>
    
    <div class="tabs">
      <button class="tab-btn active" onclick="switchTab(event, 'curl-tab')">cURL</button>
      <button class="tab-btn" onclick="switchTab(event, 'php-tab')">PHP</button>
      <button class="tab-btn" onclick="switchTab(event, 'js-tab')">JavaScript</button>
      <button class="tab-btn" onclick="switchTab(event, 'python-tab')">Python</button>
    </div>

    <!-- cURL -->
    <div id="curl-tab" class="tab-content active">
      <p style="font-size: 13px; margin-bottom: 8px;">Triggering a message using standard cURL:</p>
      <pre>
<button class="btn-copy-code" onclick="copyPreContent(this)">Copy</button>curl -X POST "<?= htmlspecialchars($api_url) ?>" \
  -H "Authorization: Bearer teh_api_your_key_here" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "+919876543210",
    "message": "*Dear Client,* \nYour OTP verification code is *884729*.",
    "type": "otp"
  }'</pre>
    </div>

    <!-- PHP -->
    <div id="php-tab" class="tab-content">
      <p style="font-size: 13px; margin-bottom: 8px;">Sending via PHP using <code>curl_exec</code>:</p>
      <pre>
<button class="btn-copy-code" onclick="copyPreContent(this)">Copy</button>&lt;?php
$api_url = "<?= htmlspecialchars($api_url) ?>";
$api_key = "teh_api_your_key_here";

$data = [
    "to" => "919876543210",
    "message" => "Hello! This is a promotional alert.",
    "type" => "promotion"
];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $api_key",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
curl_close($ch);

echo $response;</pre>
    </div>

    <!-- JS -->
    <div id="js-tab" class="tab-content">
      <p style="font-size: 13px; margin-bottom: 8px;">Sending via Javascript using <code>Fetch API</code>:</p>
      <pre>
<button class="btn-copy-code" onclick="copyPreContent(this)">Copy</button>const apiKey = 'teh_api_your_key_here';
const payload = {
  to: '+919876543210',
  message: 'Hello, your invoice statement report is ready.',
  type: 'report'
};

fetch('<?= htmlspecialchars($api_url) ?>', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${apiKey}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify(payload)
})
.then(res => res.json())
.then(data => console.log(data))
.catch(err => console.error(err));</pre>
    </div>

    <!-- Python -->
    <div id="python-tab" class="tab-content">
      <p style="font-size: 13px; margin-bottom: 8px;">Sending via Python using the <code>requests</code> library:</p>
      <pre>
<button class="btn-copy-code" onclick="copyPreContent(this)">Copy</button>import requests
import json

url = "<?= htmlspecialchars($api_url) ?>"
api_key = "teh_api_your_key_here"

headers = {
    "Authorization": f"Bearer {api_key}",
    "Content-Type": "application/json"
}

payload = {
    "to": "+919876543210",
    "message": "*Invoice delivery:* Click here to download.",
    "type": "invoice"
}

response = requests.post(url, headers=headers, data=json.dumps(payload))
print(response.json())</pre>
    </div>

    <!-- Response JSON panel -->
    <h3 style="color: #f8fafc; margin-top: 40px; margin-bottom: 12px; font-size: 14px;">Example API Success Response</h3>
    <pre style="background: #020617; border-color: #1e293b;">
{
  "success": true,
  "message": "Message sent successfully.",
  "message_id": "e3b0c44298fc1c14",
  "credits_remaining": 99
}</pre>

  </div>

</div>

<script>
function switchTab(evt, tabId) {
  // Hide all contents
  const tabContents = document.getElementsByClassName("tab-content");
  for (let i = 0; i < tabContents.length; i++) {
    tabContents[i].classList.remove("active");
  }
  
  // Deactivate all buttons
  const tabBtns = document.getElementsByClassName("tab-btn");
  for (let i = 0; i < tabBtns.length; i++) {
    tabBtns[i].classList.remove("active");
  }
  
  // Show target, activate button
  document.getElementById(tabId).classList.add("active");
  evt.currentTarget.classList.add("active");
}

function copyPreContent(btn) {
  const pre = btn.parentElement;
  // Get text without the copy button text itself
  let text = pre.textContent.trim();
  if (text.startsWith("Copy")) {
    text = text.substring(4).trim();
  }
  navigator.clipboard.writeText(text).then(() => {
    const originalText = btn.textContent;
    btn.textContent = "Copied!";
    setTimeout(() => {
      btn.textContent = originalText;
    }, 1500);
  });
}
</script>
</body>
</html>
