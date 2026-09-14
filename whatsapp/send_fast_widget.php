<!-- "Send Fast" Background Testing Tool Floating Widget -->
<div id="send-fast-container" style="position: fixed; bottom: 24px; left: 24px; z-index: 999999; font-family: 'Inter', sans-serif;">
  
  <!-- Floating Action Button (FAB) -->
  <button id="send-fast-fab" onclick="toggleSendFastModal()" style="
    background: #0E0E14;
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 30px;
    padding: 12px 20px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5), 0 0 10px rgba(212, 255, 61, 0.05);
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    outline: none;
  ">
    <span style="font-size: 16px;">🧪</span>
    <span>Send Fast</span>
    <span id="send-fast-status-dot" style="
      width: 8px;
      height: 8px;
      background: #D4FF3D;
      border-radius: 50%;
      display: inline-block;
      box-shadow: 0 0 8px #D4FF3D;
    "></span>
  </button>

  <!-- Modal Popup -->
  <div id="send-fast-modal" style="
    display: none;
    position: absolute;
    bottom: 60px;
    left: 0;
    width: 340px;
    background: #0E0E14;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6), 0 0 20px rgba(139, 92, 246, 0.05);
    animation: sendFastFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  ">
    
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
      <h4 style="
        margin: 0;
        font-family: 'Syne', 'Outfit', sans-serif;
        font-size: 15px;
        font-weight: 800;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 6px;
      ">
        <span>🧪</span> "Send Fast" Testing
      </h4>
      <button onclick="toggleSendFastModal()" style="
        background: transparent;
        border: none;
        color: #9CA3AF;
        font-size: 18px;
        cursor: pointer;
        outline: none;
        padding: 0 4px;
        transition: color 0.2s;
      " onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#9CA3AF'">×</button>
    </div>

    <!-- Description -->
    <p style="margin: 0 0 16px 0; font-size: 11.5px; color: #9CA3AF; line-height: 1.4;">
      Quickly dispatch a background test WhatsApp message to verify gateway connectivity and routing.
    </p>

    <!-- Form -->
    <div style="display: flex; flex-direction: column; gap: 12px;">
      
      <!-- Phone/JID Input -->
      <div>
        <label style="display: block; font-size: 10px; font-weight: 800; color: #9CA3AF; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">Recipient Phone / JID</label>
        <input type="text" id="send-fast-phone" placeholder="e.g. 919876543210 or group@g.us" style="
          width: 100%;
          background: rgba(15, 23, 42, 0.6);
          border: 1px solid rgba(255, 255, 255, 0.08);
          border-radius: 8px;
          color: #fff;
          padding: 10px 12px;
          font-size: 12.5px;
          outline: none;
          box-sizing: border-box;
          transition: border-color 0.2s;
        " onfocus="this.style.borderColor='#D4FF3D'" onblur="this.style.borderColor='rgba(255,255,255,0.08)'">
      </div>

      <!-- Message Textarea -->
      <div>
        <label style="display: block; font-size: 10px; font-weight: 800; color: #9CA3AF; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">Test Message</label>
        <textarea id="send-fast-message" rows="3" style="
          width: 100%;
          background: rgba(15, 23, 42, 0.6);
          border: 1px solid rgba(255, 255, 255, 0.08);
          border-radius: 8px;
          color: #fff;
          padding: 10px 12px;
          font-size: 12.5px;
          outline: none;
          box-sizing: border-box;
          resize: none;
          transition: border-color 0.2s;
        " onfocus="this.style.borderColor='#D4FF3D'" onblur="this.style.borderColor='rgba(255,255,255,0.08)'">Hello! This is a test message from THE EXPERT HUB 2FA Gateway 🧪</textarea>
      </div>

      <!-- Send button -->
      <button id="send-fast-btn" onclick="submitSendFastMsg()" style="
        background: #D4FF3D;
        color: #060609;
        font-family: 'Syne', 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 13px;
        border: none;
        border-radius: 8px;
        padding: 11px;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 0 10px rgba(212, 255, 61, 0.15);
        outline: none;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
      ">
        <span>🚀</span> Send Test Msg
      </button>

      <!-- Response status box -->
      <div id="send-fast-response" style="
        display: none;
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 11.5px;
        line-height: 1.4;
        word-break: break-word;
        box-sizing: border-box;
      "></div>

    </div>

  </div>
</div>

<style>
  #send-fast-fab:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6), 0 0 15px rgba(212, 255, 61, 0.2);
    border-color: rgba(212, 255, 61, 0.2);
  }
  @keyframes sendFastFadeIn {
    from {
      opacity: 0;
      transform: translateY(10px) scale(0.98);
    }
    to {
      opacity: 1;
      transform: translateY(0) scale(1);
    }
  }
</style>

<script>
function toggleSendFastModal() {
  const modal = document.getElementById('send-fast-modal');
  if (modal.style.display === 'none' || modal.style.display === '') {
    modal.style.display = 'block';
    document.getElementById('send-fast-phone').focus();
  } else {
    modal.style.display = 'none';
  }
}

async function submitSendFastMsg() {
  const phone = document.getElementById('send-fast-phone').value.trim();
  const message = document.getElementById('send-fast-message').value.trim();
  const btn = document.getElementById('send-fast-btn');
  const responseBox = document.getElementById('send-fast-response');

  if (!phone) {
    showSendFastResponse("Please enter a valid recipient phone number or group JID.", false);
    return;
  }

  // Set loading state
  btn.disabled = true;
  btn.style.opacity = '0.7';
  btn.style.cursor = 'not-allowed';
  btn.innerHTML = '<span>⏳</span> Sending...';
  responseBox.style.display = 'none';

  try {
    const formData = new FormData();
    formData.append('phone', phone);
    formData.append('message', message);

    const response = await fetch('send_test_fast_ajax.php', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();
    if (result.success) {
      showSendFastResponse(result.message || "🚀 Test message sent successfully!", true);
      // Flash the status dot green
      const dot = document.getElementById('send-fast-status-dot');
      if (dot) {
        dot.style.background = '#34D399';
        dot.style.boxShadow = '0 0 12px #34D399';
        setTimeout(() => {
          dot.style.background = '#D4FF3D';
          dot.style.boxShadow = '0 0 8px #D4FF3D';
        }, 3000);
      }
    } else {
      showSendFastResponse(result.error || "Failed to dispatch test message.", false);
    }
  } catch (error) {
    showSendFastResponse("Network error: Could not connect to testing endpoint.", false);
  } finally {
    btn.disabled = false;
    btn.style.opacity = '1';
    btn.style.cursor = 'pointer';
    btn.innerHTML = '<span>🚀</span> Send Test Msg';
  }
}

function showSendFastResponse(msg, isSuccess) {
  const box = document.getElementById('send-fast-response');
  box.style.display = 'block';
  box.textContent = msg;
  if (isSuccess) {
    box.style.background = 'rgba(52, 211, 153, 0.08)';
    box.style.border = '1px solid rgba(52, 211, 153, 0.2)';
    box.style.color = '#34D399';
  } else {
    box.style.background = 'rgba(248, 113, 113, 0.08)';
    box.style.border = '1px solid rgba(248, 113, 113, 0.2)';
    box.style.color = '#F87171';
  }
}
</script>
