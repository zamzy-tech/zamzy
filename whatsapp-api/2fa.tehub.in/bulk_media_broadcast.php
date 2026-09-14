<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    require_once __DIR__ . '/db.php';
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Verify client session
$api_key = $_SESSION['client_key'] ?? $_GET['api_key'] ?? '';
$client = null;

if (!empty($api_key)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM api_keys WHERE api_key = ? LIMIT 1");
        $stmt->execute([$api_key]);
        $client = $stmt->fetch();
    } catch (Exception $e) {
        $client = null;
    }
}

if (!$client || ($client['status'] ?? '') !== 'active') {
    header("Location: api_link.php");
    exit;
}

// Get global settings
$settings = [];
try {
    $stmt_set = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt_set->fetch() ?: [];
} catch (Exception $e) {}

$gateway_url = !empty($client['whatsapp_gateway_url']) ? $client['whatsapp_gateway_url'] : ($settings['whatsapp_gateway_url'] ?? 'https://2fa.tehub.in/whatsapp/send');
$gateway_token = !empty($client['whatsapp_gateway_token']) ? $client['whatsapp_gateway_token'] : ($settings['whatsapp_gateway_token'] ?? '');

// Fetch saved contacts safely with try-catch
$contacts = [];
try {
    $contacts_stmt = $pdo->prepare("
        SELECT DISTINCT client_name, client_phone 
        FROM historical_clients 
        WHERE client_phone IS NOT NULL AND client_phone != '' 
        ORDER BY client_name ASC
    ");
    $contacts_stmt->execute();
    $contacts = $contacts_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    try {
        $contacts_stmt = $pdo->prepare("
            SELECT client_name, login_id as client_phone 
            FROM api_keys 
            WHERE login_id IS NOT NULL AND login_id != '' 
            ORDER BY client_name ASC
        ");
        $contacts_stmt->execute();
        $contacts = $contacts_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $ex) {
        $contacts = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bulk Media Broadcast (4 Images + 1 Video) - THE EXPERT HUB</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg-main: #07090e;
      --bg-surface: #0e131f;
      --bg-card: #141b2d;
      --bg-elev: #1a243b;
      --border-color: rgba(255, 255, 255, 0.08);
      --border-color-soft: rgba(255, 255, 255, 0.04);
      --lime: #d4ff3d;
      --lime-deep: #b8e62e;
      --lime-glow: rgba(212, 255, 61, 0.15);
      --text-primary: #f8fafc;
      --text-secondary: #94a3b8;
      --text-muted: #64748b;
      --font-body: 'Plus Jakarta Sans', sans-serif;
      --font-mono: 'Geist Mono', monospace;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background-color: var(--bg-main);
      color: var(--text-primary);
      font-family: var(--font-body);
      min-height: 100vh;
      padding: 30px 20px;
    }

    .container {
      max-width: 1100px;
      margin: 0 auto;
    }

    .header-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
      padding-bottom: 16px;
      border-bottom: 1px solid var(--border-color);
    }
    .header-bar h1 {
      font-size: 22px;
      font-weight: 800;
      color: var(--lime);
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .header-bar .btn-back {
      padding: 8px 16px;
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      color: var(--text-primary);
      border-radius: 8px;
      text-decoration: none;
      font-size: 13px;
      font-weight: 600;
      transition: all 0.2s;
    }
    .header-bar .btn-back:hover {
      border-color: var(--lime);
      color: var(--lime);
    }

    .grid-2col {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
    }

    @media (max-width: 900px) {
      .grid-2col { grid-template-columns: 1fr; }
    }

    .card {
      background: var(--bg-surface);
      border: 1px solid var(--border-color);
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
    }

    .card-title {
      font-size: 14px;
      font-weight: 700;
      color: var(--lime);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .form-group {
      margin-bottom: 18px;
    }
    label {
      display: block;
      font-size: 12px;
      font-weight: 700;
      color: var(--text-secondary);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 6px;
    }

    input[type="text"], textarea, select {
      width: 100%;
      background: var(--bg-card);
      border: 1.5px solid var(--border-color);
      border-radius: 10px;
      padding: 12px 14px;
      font-size: 14px;
      color: var(--text-primary);
      outline: none;
      font-family: inherit;
      transition: all 0.2s;
    }
    input[type="text"]:focus, textarea:focus, select:focus {
      border-color: var(--lime);
      box-shadow: 0 0 0 3px var(--lime-glow);
    }

    .contact-list-box {
      max-height: 280px;
      overflow-y: auto;
      border: 1px solid var(--border-color);
      background: var(--bg-card);
      border-radius: 10px;
      padding: 8px;
    }
    .contact-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 8px 12px;
      border-bottom: 1px solid var(--border-color-soft);
    }
    .contact-item:last-child { border-bottom: none; }
    .contact-item label {
      font-size: 13.5px;
      font-weight: 500;
      color: var(--text-primary);
      text-transform: none;
      letter-spacing: normal;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 0;
    }

    .btn-sm {
      padding: 6px 12px;
      background: var(--bg-elev);
      border: 1px solid var(--border-color);
      color: var(--text-primary);
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-sm:hover {
      border-color: var(--lime);
      color: var(--lime);
    }

    .file-input-group {
      background: var(--bg-card);
      border: 1px dashed var(--border-color);
      border-radius: 10px;
      padding: 12px;
      margin-bottom: 12px;
    }
    .file-input-group input[type="file"] {
      width: 100%;
      font-size: 12.5px;
      color: var(--text-secondary);
    }

    .btn-dispatch {
      width: 100%;
      background: var(--lime);
      color: var(--bg-main);
      border: none;
      padding: 14px;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 800;
      cursor: pointer;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: all 0.3s;
    }
    .btn-dispatch:hover {
      background: var(--lime-deep);
      box-shadow: 0 6px 24px rgba(212, 255, 61, 0.25);
    }
    .btn-dispatch:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    .progress-box {
      margin-top: 20px;
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      padding: 16px;
      display: none;
    }
    .progress-bar-bg {
      width: 100%;
      height: 10px;
      background: rgba(255, 255, 255, 0.08);
      border-radius: 5px;
      overflow: hidden;
      margin: 10px 0;
    }
    .progress-bar-fill {
      width: 0%;
      height: 100%;
      background: var(--lime);
      transition: width 0.3s ease;
    }
    .log-table {
      width: 100%;
      margin-top: 14px;
      border-collapse: collapse;
      font-size: 12px;
    }
    .log-table th, .log-table td {
      padding: 8px 10px;
      text-align: left;
      border-bottom: 1px solid var(--border-color-soft);
    }
    .log-table th {
      color: var(--text-muted);
      text-transform: uppercase;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="header-bar">
    <h1>📢 BULK MEDIA BROADCAST <span style="font-size:12px; font-weight:600; color:var(--text-secondary);">(4 Images + 1 Video)</span></h1>
    <a href="api_link.php" class="btn-back">← Back to Dashboard</a>
  </div>

  <div class="grid-2col">
    
    <!-- Left Column: Contacts Directory & Bulk Importer -->
    <div>
      <!-- Card 1: Bulk Number Importer / Paste Box -->
      <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
          <span>📋 1. Paste Numbers (Up to 10,000)</span>
          <span style="font-size:11px; color:var(--text-muted);" id="parsedCountBadge">0 Loaded</span>
        </div>
        <div class="form-group">
          <label>Paste Raw Numbers (comma, space, or line separated):</label>
          <textarea id="rawNumbersBox" rows="5" placeholder="e.g. 9876543210&#10;919876543211&#10;+91 98765 43212"></textarea>
        </div>
        <button type="button" class="btn-sm" onclick="parseAndLoadNumbers()" style="width:100%; padding:10px;">⚡ Parse & Clean Phone Numbers</button>
      </div>

      <!-- Card 2: Contact List Directory -->
      <div class="card">
        <div class="card-title">
          <span>📇 2. Select Recipients</span>
          <span id="selectedBadge" style="font-size:11px; color:var(--lime);">0 Selected</span>
        </div>

        <div class="form-group">
          <input type="text" id="contactSearch" placeholder="Type phone number or name to search..." onkeyup="filterContacts()">
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 10px;">
          <span style="font-size:12px; color:var(--text-muted);">Saved Directory:</span>
          <div style="display:flex; gap:6px;">
            <button type="button" class="btn-sm" onclick="selectAll(true)">Select All</button>
            <button type="button" class="btn-sm" onclick="selectAll(false)">Deselect All</button>
          </div>
        </div>

        <div class="contact-list-box" id="contactListBox">
          <?php if (!empty($contacts)): ?>
            <?php foreach ($contacts as $c): ?>
              <div class="contact-item">
                <label>
                  <input type="checkbox" class="contact-cb" value="<?= htmlspecialchars($c['client_phone']) ?>" onchange="updateSelectedBadge()">
                  <?= htmlspecialchars($c['client_name']) ?> (<?= htmlspecialchars($c['client_phone']) ?>)
                </label>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div style="padding:15px; text-align:center; color:var(--text-muted); font-size:13px;">No saved directory contacts found. Paste numbers above to broadcast.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Right Column: Media Uploads & Dispatch Controls -->
    <div>
      <div class="card">
        <div class="card-title">
          <span>🎬 3. Upload Media Attachments</span>
        </div>

        <!-- 4 Images Upload -->
        <div class="form-group">
          <label>🖼️ Up to 4 Images (.jpg, .png, .webp):</label>
          <div class="file-input-group"><input type="file" id="imgFile1" accept="image/*"></div>
          <div class="file-input-group"><input type="file" id="imgFile2" accept="image/*"></div>
          <div class="file-input-group"><input type="file" id="imgFile3" accept="image/*"></div>
          <div class="file-input-group"><input type="file" id="imgFile4" accept="image/*"></div>
        </div>

        <!-- 1 Video Upload -->
        <div class="form-group">
          <label>🎥 1 Video (.mp4, .mov, max 16MB):</label>
          <div class="file-input-group"><input type="file" id="videoFile1" accept="video/*"></div>
        </div>

        <!-- Optional Message Caption -->
        <div class="form-group">
          <label>💬 Text Message / Caption:</label>
          <textarea id="broadcastCaption" rows="3" placeholder="Type text caption to accompany the media dispatches..."></textarea>
        </div>

        <!-- Delay configuration -->
        <div class="form-group">
          <label>⏱️ Anti-Ban Interval Delay (Seconds):</label>
          <select id="delayInterval">
            <option value="2">2 Seconds per message</option>
            <option value="3" selected>3 Seconds per message (Recommended)</option>
            <option value="5">5 Seconds per message (Ultra Safe)</option>
          </select>
        </div>

        <button type="button" class="btn-dispatch" id="btnDispatch" onclick="startMediaBroadcast()">🚀 Launch Bulk Broadcast</button>

        <!-- Progress Box -->
        <div class="progress-box" id="progressBox">
          <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:700;">
            <span id="progressText">Broadcasting... 0 / 0</span>
            <span id="progressPercent" style="color:var(--lime);">0%</span>
          </div>
          <div class="progress-bar-bg">
            <div class="progress-bar-fill" id="progressBarFill"></div>
          </div>
          
          <div style="display:flex; gap:8px; margin-top:10px;">
            <button type="button" class="btn-sm" id="btnPause" onclick="togglePause()">Pause</button>
            <button type="button" class="btn-sm" onclick="cancelBroadcast()" style="color:#f87171; border-color:#f87171;">Cancel</button>
          </div>

          <table class="log-table">
            <thead>
              <tr>
                <th>Recipient</th>
                <th>Status</th>
                <th>Details</th>
              </tr>
            </thead>
            <tbody id="logTableBody"></tbody>
          </table>
        </div>

      </div>
    </div>

  </div>
</div>

<script>
  let isPaused = false;
  let isCancelled = false;
  const GATEWAY_URL = <?= json_encode($gateway_url) ?>;
  const GATEWAY_TOKEN = <?= json_encode($gateway_token) ?>;
  const LOGIN_ID = <?= json_encode($client['login_id'] ?? '') ?>;

  function filterContacts() {
    let query = document.getElementById('contactSearch').value.toLowerCase();
    let items = document.querySelectorAll('.contact-item');
    items.forEach(item => {
      let text = item.textContent.toLowerCase();
      item.style.display = text.includes(query) ? 'flex' : 'none';
    });
  }

  function selectAll(flag) {
    document.querySelectorAll('.contact-cb').forEach(cb => cb.checked = flag);
    updateSelectedBadge();
  }

  function updateSelectedBadge() {
    let count = document.querySelectorAll('.contact-cb:checked').length;
    document.getElementById('selectedBadge').textContent = count + ' Selected';
  }

  function parseAndLoadNumbers() {
    let raw = document.getElementById('rawNumbersBox').value;
    if (!raw.trim()) {
      alert('Please paste phone numbers first.');
      return;
    }

    let split = raw.split(/[\s,\n\r]+/);
    let cleanedSet = new Set();

    split.forEach(num => {
      let clean = num.replace(/[^0-9]/g, '');
      if (clean.length === 10) {
        clean = '91' + clean;
      }
      if (clean.length >= 10) {
        cleanedSet.add(clean);
      }
    });

    let cleanedArr = Array.from(cleanedSet);
    let box = document.getElementById('contactListBox');

    cleanedArr.forEach(phone => {
      let existing = document.querySelector(`.contact-cb[value="${phone}"]`);
      if (!existing) {
        let div = document.createElement('div');
        div.className = 'contact-item';
        div.innerHTML = `
          <label>
            <input type="checkbox" class="contact-cb" value="${phone}" checked onchange="updateSelectedBadge()">
            Pasted Number (+${phone})
          </label>
        `;
        box.prepend(div);
      } else {
        existing.checked = true;
      }
    });

    document.getElementById('parsedCountBadge').textContent = cleanedArr.length + ' Parsed';
    updateSelectedBadge();
    alert(`Successfully parsed and loaded ${cleanedArr.length} unique phone numbers!`);
  }

  function fileToBase64(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onload = () => resolve(reader.result);
      reader.onerror = error => reject(error);
    });
  }

  async function startMediaBroadcast() {
    let selectedCbs = Array.from(document.querySelectorAll('.contact-cb:checked'));
    if (selectedCbs.length === 0) {
      alert('Please select or paste at least one contact.');
      return;
    }

    let recipients = selectedCbs.map(cb => cb.value);

    let files = [];
    ['imgFile1', 'imgFile2', 'imgFile3', 'imgFile4', 'videoFile1'].forEach(id => {
      let el = document.getElementById(id);
      if (el && el.files[0]) {
        files.push(el.files[0]);
      }
    });

    let caption = document.getElementById('broadcastCaption').value.trim();

    if (files.length === 0 && !caption) {
      alert('Please attach at least one image/video or type a text message.');
      return;
    }

    let confirmMsg = `Start broadcast to ${recipients.length} contacts with ${files.length} media file(s)?`;
    if (!confirm(confirmMsg)) return;

    let mediaPayloads = [];
    for (let file of files) {
      let b64 = await fileToBase64(file);
      mediaPayloads.push({
        base64: b64,
        filename: file.name
      });
    }

    document.getElementById('btnDispatch').disabled = true;
    document.getElementById('progressBox').style.display = 'block';
    let logBody = document.getElementById('logTableBody');
    logBody.innerHTML = '';

    isPaused = false;
    isCancelled = false;

    let delaySec = parseInt(document.getElementById('delayInterval').value) || 3;
    let total = recipients.length;
    let successCount = 0;
    let failCount = 0;

    for (let i = 0; i < recipients.length; i++) {
      if (isCancelled) {
        alert('Broadcast cancelled by user.');
        break;
      }

      while (isPaused) {
        await new Promise(r => setTimeout(r, 500));
      }

      let phone = recipients[i];
      let currentNum = i + 1;
      let percent = Math.round((currentNum / total) * 100);

      document.getElementById('progressText').textContent = `Broadcasting... ${currentNum} / ${total}`;
      document.getElementById('progressPercent').textContent = percent + '%';
      document.getElementById('progressBarFill').style.width = percent + '%';

      try {
        if (mediaPayloads.length > 0) {
          for (let media of mediaPayloads) {
            await sendSingleMessage(phone, caption, media.base64, media.filename);
          }
        } else {
          await sendSingleMessage(phone, caption, null, null);
        }

        successCount++;
        addLogRow(phone, '✅ Sent', 'Dispatched successfully');
      } catch (err) {
        failCount++;
        addLogRow(phone, '❌ Failed', err.message);
      }

      if (i < recipients.length - 1) {
        await new Promise(r => setTimeout(r, delaySec * 1000));
      }
    }

    document.getElementById('btnDispatch').disabled = false;
    alert(`Broadcast Completed! Total: ${total} | Success: ${successCount} | Failed: ${failCount}`);
  }

  function sendSingleMessage(phone, body, pdfBase64, filename) {
    return new Promise((resolve, reject) => {
      let targetUrl = GATEWAY_URL;
      targetUrl += (targetUrl.indexOf('?') !== -1 ? '&' : '?') + 'session=' + encodeURIComponent(LOGIN_ID);

      let payload = {
        session: LOGIN_ID,
        to: phone,
        phone: phone,
        number: phone,
        body: body,
        message: body,
        token: GATEWAY_TOKEN,
        apikey: GATEWAY_TOKEN
      };

      if (pdfBase64) {
        payload.pdf = pdfBase64;
        payload.filename = filename || 'media';
      }

      fetch(targetUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
      })
      .then(res => res.json())
      .then(data => {
        if (data.success || data.messageId || data.message) {
          resolve(data);
        } else {
          reject(new Error(data.error || 'Gateway returned error'));
        }
      })
      .catch(err => reject(err));
    });
  }

  function addLogRow(phone, status, details) {
    let body = document.getElementById('logTableBody');
    let tr = document.createElement('tr');
    tr.innerHTML = `
      <td style="font-family:var(--font-mono); font-weight:600;">+${phone}</td>
      <td>${status}</td>
      <td style="color:var(--text-secondary);">${details}</td>
    `;
    body.prepend(tr);
  }

  function togglePause() {
    isPaused = !isPaused;
    let btn = document.getElementById('btnPause');
    btn.textContent = isPaused ? 'Resume' : 'Pause';
    btn.style.borderColor = isPaused ? 'var(--lime)' : 'var(--border-color)';
    btn.style.color = isPaused ? 'var(--lime)' : 'var(--text-primary)';
  }

  function cancelBroadcast() {
    if (confirm('Are you sure you want to cancel the active broadcast?')) {
      isCancelled = true;
    }
  }
</script>

</body>
</html>
