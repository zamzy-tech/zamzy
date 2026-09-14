<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['yt_user_id'])) {
    header('Location: yt_login.php');
    exit;
}

require_once __DIR__ . '/db.php';
$youtuber_id = $_SESSION['yt_user_id'];

try {
    // 1. Fetch YouTuber info
    $stmt = $pdo->prepare("SELECT * FROM youtubers WHERE id = ? LIMIT 1");
    $stmt->execute([$youtuber_id]);
    $yt = $stmt->fetch();
    
    if (!$yt) {
        session_destroy();
        header('Location: yt_login.php');
        exit;
    }

    // 2. Fetch history logs
    $hist_stmt = $pdo->prepare("SELECT * FROM youtube_history WHERE youtuber_id = ? ORDER BY id DESC LIMIT 50");
    $hist_stmt->execute([$youtuber_id]);
    $logs = $hist_stmt->fetchAll();
    
    // 3. Fetch gateway URL
    $gateway_stmt = $pdo->query("SELECT whatsapp_gateway_url FROM settings LIMIT 1");
    $gateway_url = $gateway_stmt->fetchColumn() ?: 'http://localhost:3000/send';

    // 4. Fetch linked channels
    $channels_stmt = $pdo->prepare("SELECT * FROM youtuber_channels WHERE youtuber_id = ? ORDER BY id DESC");
    $channels_stmt->execute([$youtuber_id]);
    $channels = $channels_stmt->fetchAll();
    
} catch (PDOException $e) {
    die("Database retrieval error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Creator &amp; Broadcast Dashboard — ZAMZY</title>
<link rel="shortcut icon" href="zamzy_logo.png" type="image/png">
<link rel="icon" href="zamzy_logo.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700;800&family=IBM+Plex+Mono:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root {
    --bg-main: #05060b;
    --bg-surface: #0a0c16;
    --bg-card: rgba(14, 17, 30, 0.85);
    --bg-elev: #141829;
    --border-color: rgba(255, 255, 255, 0.08);
    --border-color-soft: rgba(255, 255, 255, 0.04);
    
    --text-primary: #f8fafc;
    --text-secondary: #94a3b8;
    --text-muted: #64748b;
    
    --cyan: #00ffcc;
    --cyan-glow: rgba(0, 255, 204, 0.22);
    --lime: #FF3D3D; /* YouTube Red */
    --lime-deep: #CC2222;
    --lime-glow: rgba(255, 61, 61, 0.25);
    
    --font-heading: 'Space Grotesk', sans-serif;
    --font-body: 'Inter', sans-serif;
    --font-mono: 'IBM Plex Mono', monospace;
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: var(--font-body);
    background-color: var(--bg-main);
    color: var(--text-primary);
    min-height: 100vh;
    padding: 40px 20px;
    background-image: 
      radial-gradient(circle at 15% 15%, rgba(157, 78, 221, 0.12) 0%, transparent 45%),
      radial-gradient(circle at 85% 85%, rgba(0, 255, 204, 0.10) 0%, transparent 45%),
      linear-gradient(to bottom, #05060b, #070913);
    background-attachment: fixed;
  }
  .container {
    width: 100%;
    max-width: 1150px;
    margin: 0 auto;
  }
  header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border-color);
  }
  .logo {
    font-size: 22px;
    font-weight: 800;
    font-family: var(--font-heading);
    letter-spacing: 0.5px;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .logo-highlight {
    color: var(--lime);
  }
  .user-badge {
    display: flex;
    align-items: center;
    gap: 16px;
  }
  .username {
    font-size: 14px;
    font-weight: 600;
  }
  .btn-logout {
    padding: 8px 16px;
    background-color: transparent;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-secondary);
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
  }
  .btn-logout:hover {
    border-color: var(--lime);
    color: var(--lime);
  }
  
  .dashboard-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 30px;
  }
  @media (min-width: 850px) {
    .dashboard-grid {
      grid-template-columns: 1.2fr 0.8fr;
    }
  }

  .card {
    background-color: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
  }
  .card h3 {
    font-size: 18px;
    font-weight: 600;
    font-family: var(--font-heading);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  
  .form-group {
    margin-bottom: 22px;
    text-align: left;
  }
  label {
    display: block;
    font-size: 11.5px;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 8px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }
  input[type="text"], textarea, select {
    width: 100%;
    padding: 12px 16px;
    background-color: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    font-size: 14px;
    color: var(--text-primary);
    font-family: var(--font-body);
    transition: all 0.2s ease;
  }
  textarea {
    resize: vertical;
    min-height: 100px;
  }
  input:focus, textarea:focus, select:focus {
    outline: none;
    border-color: var(--lime);
    box-shadow: 0 0 0 3px var(--lime-glow);
  }
  
  .btn-primary {
    padding: 12px 24px;
    background-color: var(--lime);
    color: #FFFFFF;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .btn-primary:hover {
    background-color: var(--lime-deep);
  }
  .btn-secondary {
    padding: 12px 24px;
    background-color: transparent;
    border: 1px solid var(--border-color);
    color: var(--text-secondary);
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-left: 12px;
  }
  .btn-secondary:hover {
    border-color: var(--text-primary);
    color: var(--text-primary);
  }
  
  .status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
  }
  .status-badge.sent {
    background-color: rgba(39, 174, 96, 0.15);
    color: #2ECC71;
  }
  .status-badge.failed {
    background-color: rgba(231, 76, 60, 0.15);
    color: #E74C3C;
  }
  
  .logs-table-wrapper {
    overflow-x: auto;
    margin-top: 20px;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
  }
  th, td {
    padding: 12px 16px;
    font-size: 13.5px;
    border-bottom: 1px solid var(--border-color-soft);
  }
  th {
    font-weight: 600;
    color: var(--text-secondary);
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.5px;
  }
  td {
    color: var(--text-primary);
  }
  
  .helper-text {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 6px;
  }
  .flex-row {
    display: flex;
    gap: 12px;
  }
  
  .banner {
    padding: 16px 20px;
    border-radius: 10px;
    font-size: 14px;
    margin-bottom: 24px;
    text-align: left;
    display: none;
  }
  .banner.success {
    background-color: rgba(46, 204, 113, 0.15);
    border: 1px solid rgba(46, 204, 113, 0.25);
    color: #2ECC71;
  }
  .banner.error {
    background-color: rgba(231, 76, 60, 0.15);
    border: 1px solid rgba(231, 76, 60, 0.25);
    color: #E74C3C;
  }
  /* Premium Modal CSS */
  .modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(5px);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1000;
  }
  .modal-container {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 30px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
    max-height: 90vh;
    overflow-y: auto;
  }
  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    border-bottom: 1px solid var(--border-color-soft);
    padding-bottom: 12px;
  }
  .modal-header h3 {
    margin: 0;
    font-size: 18px;
    color: var(--text-primary);
  }
  .modal-close {
    background: transparent;
    border: none;
    color: var(--text-muted);
    font-size: 24px;
    cursor: pointer;
    line-height: 1;
    transition: color 0.2s;
  }
  .modal-close:hover {
    color: var(--lime);
  }
  .channel-actions {
    display: flex;
    gap: 8px;
  }
  .btn-danger {
    background: #e53e3e;
    color: #fff;
    border: none;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
  }
  .btn-danger:hover {
    background: #c53030;
  }
  .btn-sm {
    padding: 8px 12px;
    font-size: 12px;
    border-radius: 8px;
    margin-left: 0;
  }
</style>
</head>
<body>

<div class="container">
  <header>
    <div class="logo">
      <img src="zamzy_logo.png" alt="ZAMZY" style="height:32px;width:auto;">
      <span>ZAMZY</span>
      <span class="logo-highlight" style="color:var(--cyan); font-family:var(--font-mono); font-size:12px; margin-left:6px;">[YOUTUBE PIPELINE]</span>
    </div>
    
    <div class="user-badge">
      <span class="username">👋 @<?= htmlspecialchars($yt['username']) ?></span>
      <a href="yt_logout.php" class="btn-logout">Logout</a>
    </div>
  </header>
  
  <div id="statusBanner" class="banner"></div>
  
  <div class="dashboard-grid">
    <!-- Configuration Column -->
    <div>
      <!-- Linked Channels Card -->
      <div class="card" style="margin-bottom: 30px;">
        <h3>📺 Connected YouTube Channels</h3>
        
        <div class="logs-table-wrapper" style="margin-top: 10px;">
          <?php if (empty($channels)): ?>
            <p style="font-size: 13.5px; color: var(--text-muted); text-align: center; padding: 30px 0;">No YouTube channels connected yet. Use the form below to connect one.</p>
          <?php else: ?>
            <table>
              <thead>
                <tr>
                  <th>Channel Info</th>
                  <th>Target WhatsApp</th>
                  <th style="text-align: right;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($channels as $chan): ?>
                  <tr id="channel_row_<?= $chan['id'] ?>">
                    <td>
                      <strong style="color: var(--text-primary); font-size: 14px;"><?= htmlspecialchars($chan['channel_name']) ?></strong>
                      <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">ID: <code><?= htmlspecialchars($chan['channel_id']) ?></code></div>
                    </td>
                    <td>
                      <strong style="color: var(--lime);"><?= htmlspecialchars($chan['whatsapp_target_name'] ?: 'None') ?></strong>
                      <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">JID: <span style="font-size:10px; word-break:break-all;"><?= htmlspecialchars($chan['whatsapp_target_jid'] ?: 'Not Configured') ?></span></div>
                    </td>
                    <td>
                      <div class="channel-actions" style="justify-content: flex-end;">
                        <button type="button" class="btn-primary btn-sm" onclick="triggerTestPost(<?= $chan['id'] ?>)" style="background: #128c7e; margin-left: 0;">Test</button>
                        <button type="button" class="btn-secondary btn-sm" onclick='openEditModal(<?= json_encode($chan) ?>)' style="margin-left: 0;">Edit</button>
                        <button type="button" class="btn-danger btn-sm" onclick="removeChannel(<?= $chan['id'] ?>)" style="padding: 8px 12px;">Delete</button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>

      <!-- Add Channel Card -->
      <div class="card">
        <h3>🔗 Connect a New Channel</h3>
        
        <form id="addChannelForm">
          <div class="form-group">
            <label for="newChannelId">YouTube Channel ID</label>
            <input type="text" id="newChannelId" name="channel_id" placeholder="e.g., UC6yJ_VGVy0Dz3O3n6wHyhTQ" required>
          </div>
          
          <div class="form-group">
            <label for="newChatTarget">Target WhatsApp Group/Community JID</label>
            <div class="flex-row">
              <select class="chatTargetSelect" id="newChatTargetSelect">
                <option value="">-- Fetching Chats... --</option>
              </select>
              <button type="button" class="btn-secondary btnRefreshChats" style="margin: 0; padding: 12px 16px;">🔄</button>
            </div>
            <input type="text" id="newChatTargetJid" name="whatsapp_target_jid" placeholder="e.g., 120363426187610725@g.us" style="margin-top: 10px;" required>
            <input type="hidden" id="newChatTargetName" name="whatsapp_target_name">
            <div class="helper-text">Select from the dropdown or manually paste the WhatsApp target JID (`@g.us`).</div>
          </div>
          
          <div class="form-group">
            <label for="newTemplateUpload">New Video Message Template</label>
            <textarea id="newTemplateUpload" name="template_upload">🎥 *New Video Alert!*

{title}

Watch now: {url}</textarea>
            <div class="helper-text">Supported variables: <code>{title}</code>, <code>{url}</code></div>
          </div>
          
          <div class="form-group">
            <label for="newTemplateLive">Live Stream Message Template</label>
            <textarea id="newTemplateLive" name="template_live">🔴 *We are LIVE now!*

{title}

Join the stream here: {url}</textarea>
            <div class="helper-text">Supported variables: <code>{title}</code>, <code>{url}</code></div>
          </div>
          
          <div style="margin-top: 30px; text-align: left;">
            <button type="submit" class="btn-primary">Connect Channel</button>
          </div>
        </form>
      </div>
    </div>
    
    <!-- Quick Stats & Info Card -->
    <div>
      <!-- WhatsApp Device Link Card -->
      <div class="card" style="margin-bottom: 30px;">
        <h3>🔌 WhatsApp Device Link</h3>
        
        <!-- Live Connected State -->
        <div id="deviceConnectedContainer" style="display: <?= $yt['whatsapp_is_connected'] ? 'block' : 'none' ?>;">
          <div style="background: rgba(37, 211, 102, 0.1); border: 1px solid rgba(37, 211, 102, 0.3); color: #25D366; padding: 16px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; text-align: left; line-height: 1.5;">
            🟢 <strong>Device Linked!</strong> Automated channel notifications will post from your connected WhatsApp number: <strong id="deviceLinkedNumber"><?= htmlspecialchars($yt['whatsapp_linked_number'] ?? 'Connected') ?></strong>
          </div>
          <button type="button" onclick="disconnectDevice()" class="btn-secondary" style="background: #e53e3e; color: #fff; border: none; font-size: 12px; padding: 8px 16px; margin: 0;">Disconnect Device</button>
        </div>

        <!-- Live Disconnected / Scan QR State -->
        <div id="deviceDisconnectedContainer" style="display: <?= !$yt['whatsapp_is_connected'] ? 'block' : 'none' ?>;">
          <p style="font-size: 13.5px; color: var(--text-secondary); line-height: 1.6; margin-bottom: 20px; text-align: left;">
            Link your personal WhatsApp number to send automated channel alerts directly from your own phone.
          </p>
          
          <div id="qrLoadingContainer" style="display: none; justify-content: center; align-items: center; width: 220px; height: 220px; border: 1px solid var(--border-color); border-radius: 12px; margin: 0 auto 20px; background: rgba(0,0,0,0.2);">
            <div style="font-size: 13px; color: var(--text-muted);">Generating QR Code...</div>
          </div>
          
          <div id="qrCodeContainer" style="display: none; justify-content: center; align-items: center; width: 220px; height: 220px; border: 1px solid var(--border-color); border-radius: 12px; margin: 0 auto 20px; background: #fff; overflow: hidden; padding: 10px;">
            <img id="qrImage" src="" style="width: 100%; height: 100%; object-fit: contain;" alt="WhatsApp Scan QR">
          </div>
          
          <div id="qrMsg" style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 20px; font-weight: 500; text-align: center;">
            No device is connected. Click the button below to display the QR Code.
          </div>
          
          <button type="button" id="btnGenQR" onclick="startQRGeneration()" class="btn-primary" style="font-size: 12px; padding: 8px 16px; background: #128c7e; border: none; margin: 0 auto; display: block;">Generate QR Code</button>
        </div>
      </div>

      <div class="card" style="margin-bottom: 30px;">
        <h3>📌 How to Setup RSS Cron Job</h3>
        <p style="font-size: 13.5px; color: var(--text-secondary); line-height: 1.6; margin-bottom: 12px;">
          To automate posting new videos, add a cron job in cPanel that runs every minute to call our background worker:
        </p>
        <div style="background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; font-family: var(--font-mono); font-size: 12px; word-break: break-all; color: var(--lime); line-height: 1.4;">
          curl -s "https://2fa.tehub.in/yt_cron.php" >/dev/null 2>&1
        </div>
      </div>
      
      <div class="card">
        <h3>📊 Automated Posting History</h3>
        
        <div class="logs-table-wrapper">
          <?php if (empty($logs)): ?>
            <p style="font-size: 13.5px; color: var(--text-muted); text-align: center; padding: 20px 0;">No history logs found.</p>
          <?php else: ?>
            <table>
              <thead>
                <tr>
                  <th>Video Title</th>
                  <th>Status</th>
                  <th>Sent At</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($logs as $log): ?>
                  <tr>
                    <td><?= htmlspecialchars(mb_strimwidth($log['title'], 0, 30, '...')) ?></td>
                    <td>
                      <span class="status-badge <?= $log['status'] ?>">
                        <?= htmlspecialchars($log['status']) ?>
                      </span>
                    </td>
                    <td><?= date('d M, H:i', strtotime($log['sent_at'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const statusBanner = document.getElementById('statusBanner');
    
    function showBanner(message, type) {
        statusBanner.textContent = message;
        statusBanner.className = `banner ${type}`;
        statusBanner.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    let allGroups = [];
    
    // Fetch WhatsApp Groups
    async function loadGroups() {
        const selects = document.querySelectorAll('.chatTargetSelect');
        selects.forEach(sel => {
            sel.innerHTML = '<option value="">-- Fetching Chats... --</option>';
        });
        
        try {
            const res = await fetch('yt_api.php?action=get_groups');
            const data = await res.json();
            if (data.success) {
                allGroups = data.groups;
                selects.forEach(sel => {
                    sel.innerHTML = '<option value="">-- Select Target Group/Feed --</option>';
                    allGroups.forEach(g => {
                        const opt = document.createElement('option');
                        opt.value = g.jid;
                        opt.textContent = g.name;
                        sel.appendChild(opt);
                    });
                });
            } else {
                selects.forEach(sel => {
                    sel.innerHTML = '<option value="">-- Error Fetching Chats --</option>';
                });
                showBanner('Failed to fetch groups: ' + data.error, 'error');
            }
        } catch (e) {
            selects.forEach(sel => {
                sel.innerHTML = '<option value="">-- Network Error --</option>';
            });
        }
    }
    
    // Bind group change and refresh event handlers for both forms dynamically
    document.querySelectorAll('.btnRefreshChats').forEach(btn => {
        btn.addEventListener('click', loadGroups);
    });

    function appendTarget(selectEl, jidInput, nameInput) {
        const jid = selectEl.value;
        const name = selectEl.options[selectEl.selectedIndex].text;
        if (!jid) return;
        
        let currentJids = jidInput.value.split(',').map(s => s.trim()).filter(s => s);
        let currentNames = nameInput.value.split(',').map(s => s.trim()).filter(s => s);
        
        if (!currentJids.includes(jid)) {
            currentJids.push(jid);
            currentNames.push(name);
        }
        
        jidInput.value = currentJids.join(', ');
        nameInput.value = currentNames.join(', ');
        
        // Reset the select dropdown to prompt so they can select another one
        selectEl.selectedIndex = 0;
    }

    // Add Channel form logic
    const newChatTargetSelect = document.getElementById('newChatTargetSelect');
    const newChatTargetJid = document.getElementById('newChatTargetJid');
    const newChatTargetName = document.getElementById('newChatTargetName');
    
    if (newChatTargetSelect) {
        newChatTargetSelect.addEventListener('change', () => {
            appendTarget(newChatTargetSelect, newChatTargetJid, newChatTargetName);
        });
    }

    // Edit Channel form logic
    const editChatTargetSelect = document.getElementById('editChatTargetSelect');
    const editChatTargetJid = document.getElementById('editChatTargetJid');
    const editChatTargetName = document.getElementById('editChatTargetName');
    
    if (editChatTargetSelect) {
        editChatTargetSelect.addEventListener('change', () => {
            appendTarget(editChatTargetSelect, editChatTargetJid, editChatTargetName);
        });
    }

    loadGroups();

    // Add Channel submit handler
    const addChannelForm = document.getElementById('addChannelForm');
    if (addChannelForm) {
        addChannelForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('action', 'add_channel');
            
            showBanner('Connecting channel... Please wait...', 'success');
            try {
                const res = await fetch('yt_api.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    showBanner('Channel connected successfully!', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showBanner(data.error, 'error');
                }
            } catch (e) {
                showBanner('Connection failed.', 'error');
            }
        });
    }

    // Edit Channel modal handlers
    const editChannelModal = document.getElementById('editChannelModal');
    
    window.openEditModal = function(chan) {
        document.getElementById('editChannelLinkId').value = chan.id;
        document.getElementById('editChannelLabelName').textContent = chan.channel_name;
        document.getElementById('editChannelLabelId').textContent = 'ID: ' + chan.channel_id;
        document.getElementById('editChatTargetJid').value = chan.whatsapp_target_jid || '';
        document.getElementById('editChatTargetName').value = chan.whatsapp_target_name || '';
        document.getElementById('editTemplateUpload').value = chan.template_upload || '';
        document.getElementById('editTemplateLive').value = chan.template_live || '';

        // Select the active group if available in dropdown
        if (allGroups.length > 0) {
            editChatTargetSelect.value = chan.whatsapp_target_jid || '';
        }
        
        editChannelModal.style.display = 'flex';
    }

    window.closeEditModal = function() {
        editChannelModal.style.display = 'none';
    }

    const editChannelForm = document.getElementById('editChannelForm');
    if (editChannelForm) {
        editChannelForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('action', 'update_channel');
            
            showBanner('Saving changes... Please wait...', 'success');
            try {
                const res = await fetch('yt_api.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    showBanner('Changes saved successfully!', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showBanner(data.error, 'error');
                }
            } catch (e) {
                showBanner('Connection failed.', 'error');
            }
        });
    }

    // Test Post trigger
    window.triggerTestPost = async function(channelLinkId) {
        showBanner('Triggering test post... Please wait...', 'success');
        try {
            const res = await fetch('yt_api.php?action=test_post&channel_link_id=' + channelLinkId);
            const data = await res.json();
            if (data.success) {
                showBanner(data.message, 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showBanner(data.error, 'error');
            }
        } catch (e) {
            showBanner('Connection failed.', 'error');
        }
    }

    // Remove Channel link
    window.removeChannel = async function(channelLinkId) {
        if (!confirm('Are you sure you want to disconnect this YouTube Channel link?')) return;
        
        showBanner('Removing channel... Please wait...', 'success');
        try {
            const res = await fetch('yt_api.php?action=delete_channel&channel_link_id=' + channelLinkId);
            const data = await res.json();
            if (data.success) {
                showBanner('Channel removed successfully!', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showBanner(data.error, 'error');
            }
        } catch (e) {
            showBanner('Connection failed.', 'error');
        }
    }

    // WhatsApp Device Scan & Polling Logic
    const GATEWAY_URL = <?= json_encode($gateway_url) ?>;
    const isDbConnected = <?= empty($yt['whatsapp_is_connected']) ? 'false' : 'true' ?>;
    const youtuberId = <?= json_encode($youtuber_id) ?>;
    
    let qrInterval = null;
    let isPolling = false;

    window.startQRGeneration = function() {
        const placeholder = document.getElementById('qrLoadingContainer');
        const msg = document.getElementById('qrMsg');
        const btn = document.getElementById('btnGenQR');
        
        if (placeholder) placeholder.style.display = 'flex';
        if (msg) msg.textContent = 'Contacting gateway server...';
        if (btn) btn.style.display = 'none';
        
        startStatusPolling();
    }

    function startStatusPolling() {
        if (isPolling) return;
        isPolling = true;
        
        pollWhatsAppStatus();
        qrInterval = setInterval(pollWhatsAppStatus, 2500);
    }

    function stopStatusPolling() {
        if (qrInterval) {
            clearInterval(qrInterval);
            qrInterval = null;
        }
        isPolling = false;
    }

    async function pollWhatsAppStatus() {
        const container = document.getElementById('qrCodeContainer');
        const placeholder = document.getElementById('qrLoadingContainer');
        const qrImage = document.getElementById('qrImage');
        const msg = document.getElementById('qrMsg');
        const btn = document.getElementById('btnGenQR');
        
        const connectedContainer = document.getElementById('deviceConnectedContainer');
        const disconnectedContainer = document.getElementById('deviceDisconnectedContainer');
        const deviceLinkedNumber = document.getElementById('deviceLinkedNumber');

        if (!connectedContainer || !disconnectedContainer) return;

        let statusUrl = GATEWAY_URL.replace('/send', '/status');
        statusUrl += (statusUrl.indexOf('?') !== -1 ? '&' : '?') + 'session=youtuber_' + youtuberId;

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 4000);
            
            const res = await fetch(statusUrl, { signal: controller.signal });
            clearTimeout(timeoutId);
            
            if (!res.ok) throw new Error('Offline');
            
            const data = await res.json();
            
            if (data.status === 'CONNECTED') {
                stopStatusPolling();
                connectedContainer.style.display = 'block';
                disconnectedContainer.style.display = 'none';
                if (deviceLinkedNumber) deviceLinkedNumber.textContent = data.number || 'Connected';
                
                // If DB says not connected, sync with DB
                if (!isDbConnected) {
                    const formData = new FormData();
                    formData.append('action', 'connect_whatsapp');
                    formData.append('whatsapp_linked_number', data.number || 'Connected');
                    await fetch('yt_api.php', { method: 'POST', body: formData });
                    setTimeout(() => window.location.reload(), 1000);
                }
            } else {
                connectedContainer.style.display = 'none';
                disconnectedContainer.style.display = 'block';
                
                if (data.status === 'DISCONNECTED') {
                    if (placeholder) placeholder.style.display = 'none';
                    if (container) container.style.display = 'none';
                    if (msg) msg.textContent = 'Disconnected. Click Generate QR Code to begin.';
                    if (btn) btn.style.display = 'block';
                } 
                else if (data.status === 'INITIALIZING') {
                    if (placeholder) placeholder.style.display = 'flex';
                    if (container) container.style.display = 'none';
                    if (msg) msg.textContent = 'Initializing WhatsApp session... Please wait...';
                    if (btn) btn.style.display = 'none';
                } 
                else if (data.status === 'QR_READY') {
                    if (placeholder) placeholder.style.display = 'none';
                    if (container) container.style.display = 'flex';
                    if (msg) msg.textContent = 'QR Code is ready. Please scan with your phone.';
                    if (btn) btn.style.display = 'none';
                    
                    if (qrImage && data.qr && qrImage.src !== data.qr) {
                        qrImage.src = data.qr;
                    }
                }
            }
        } catch (err) {
            console.log('Gateway status check failed: ', err);
        }
    }

    window.disconnectDevice = async function() {
        if (!confirm('Are you sure you want to disconnect your WhatsApp device?')) return;
        
        showBanner('Disconnecting device... Please wait...', 'success');
        
        try {
            const formData = new FormData();
            formData.append('action', 'disconnect_whatsapp');
            const res = await fetch('yt_api.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                showBanner('Device disconnected successfully!', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showBanner(data.error, 'error');
            }
        } catch (e) {
            showBanner('Connection failed.', 'error');
        }
    }

    // Auto check/poll on load to sync connection state
    startStatusPolling();
});
</script>

<!-- Edit Channel Modal -->
<div class="modal-overlay" id="editChannelModal">
  <div class="modal-container">
    <div class="modal-header">
      <h3>⚙️ Edit Channel Settings</h3>
      <button class="modal-close" onclick="closeEditModal()">&times;</button>
    </div>
    <form id="editChannelForm">
      <input type="hidden" name="channel_link_id" id="editChannelLinkId">
      
      <div class="form-group">
        <label>YouTube Channel</label>
        <div style="font-size: 14px; font-weight: bold; color: var(--text-primary);" id="editChannelLabelName">Channel Name</div>
        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;" id="editChannelLabelId">ID: Channel ID</div>
      </div>

      <div class="form-group">
        <label for="editChatTarget">Target WhatsApp Group/Community JID</label>
        <div class="flex-row">
          <select class="chatTargetSelect" id="editChatTargetSelect">
            <option value="">-- Fetching Chats... --</option>
          </select>
          <button type="button" class="btn-secondary btnRefreshChats" style="margin: 0; padding: 12px 16px;">🔄</button>
        </div>
        <input type="text" id="editChatTargetJid" name="whatsapp_target_jid" placeholder="e.g., 120363426187610725@g.us" style="margin-top: 10px;" required>
        <input type="hidden" id="editChatTargetName" name="whatsapp_target_name">
        <div class="helper-text">Select from the dropdown or manually paste the WhatsApp target JID (`@g.us`).</div>
      </div>
      
      <div class="form-group">
        <label for="editTemplateUpload">New Video Message Template</label>
        <textarea id="editTemplateUpload" name="template_upload"></textarea>
        <div class="helper-text">Supported variables: <code>{title}</code>, <code>{url}</code></div>
      </div>
      
      <div class="form-group">
        <label for="editTemplateLive">Live Stream Message Template</label>
        <textarea id="editTemplateLive" name="template_live"></textarea>
        <div class="helper-text">Supported variables: <code>{title}</code>, <code>{url}</code></div>
      </div>
      
      <div style="margin-top: 30px; text-align: left; display: flex; gap: 12px;">
        <button type="submit" class="btn-primary" style="flex: 1; margin: 0;">Save Changes</button>
        <button type="button" class="btn-secondary" onclick="closeEditModal()" style="margin: 0; flex: 1;">Cancel</button>
      </div>
    </form>
  </div>
</div>

<?php include_once __DIR__ . '/send_fast_widget.php'; ?>
</body>
</html>
