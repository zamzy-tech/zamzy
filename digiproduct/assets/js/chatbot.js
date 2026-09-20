/**
 * ZAMZY Neural Assistant & Scroll Dock Component
 */
(function() {
  if (document.getElementById('zamzyFloatDock')) return;

  // 1. Inject CSS if not loaded
  if (!document.querySelector('link[href*="chatbot.css"]')) {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = '/digiproduct/assets/css/chatbot.css';
    document.head.appendChild(link);
  }

  // 2. Inject HTML Dock & Chat Window
  const dockHtml = `
    <div class="zamzy-float-dock" id="zamzyFloatDock">
      <button type="button" class="float-btn float-btn-top" id="btnScrollTop" title="Scroll to Top" aria-label="Scroll to Top">
        ↑
      </button>
      <button type="button" class="float-btn float-btn-chat" id="btnChatToggle" title="Chat with ZAMZY AI" aria-label="Chat with Support">
        💬
        <span class="float-chat-pulse"></span>
      </button>
    </div>

    <div class="zamzy-chat-window" id="zamzyChatWindow">
      <div class="chat-header">
        <div class="chat-header-info">
          <div class="chat-avatar">⚡</div>
          <div>
            <h4 class="chat-title">ZAMZY Assistant</h4>
            <div class="chat-status"><span class="chat-status-dot"></span> Online &amp; Ready</div>
          </div>
        </div>
        <button class="chat-close-btn" id="btnChatClose">✕</button>
      </div>

      <div class="chat-messages" id="chatMessages">
        <div class="chat-msg chat-msg-bot">
          👋 Hello! I am your ZAMZY AI Assistant. How can I help you today?
        </div>
      </div>

      <div class="chat-quick-chips">
        <button class="chip-btn" onclick="zamzyChat.ask('download')">📥 Access Purchases</button>
        <button class="chip-btn" onclick="zamzyChat.ask('coupon')">🏷️ Coupon Code</button>
        <button class="chip-btn" onclick="zamzyChat.ask('otp')">🔐 OTP Issues</button>
        <button class="chip-btn" onclick="zamzyChat.ask('support')">📩 Email Support</button>
      </div>

      <form class="chat-input-row" id="chatInputForm" onsubmit="return zamzyChat.handleSubmit(event)">
        <input type="text" class="chat-input" id="chatInput" placeholder="Type a question..." autocomplete="off">
        <button type="submit" class="chat-send-btn">➤</button>
      </form>
    </div>
  `;

  const dockContainer = document.createElement('div');
  dockContainer.innerHTML = dockHtml;
  document.body.appendChild(dockContainer);

  // 3. Scroll-to-Top behavior
  const btnTop = document.getElementById('btnScrollTop');
  window.addEventListener('scroll', () => {
    if (window.scrollY > 250) {
      btnTop.classList.add('visible');
    } else {
      btnTop.classList.remove('visible');
    }
  });
  btnTop.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // 4. Chat Modal Toggle
  const chatWindow = document.getElementById('zamzyChatWindow');
  const btnChat = document.getElementById('btnChatToggle');
  const btnClose = document.getElementById('btnChatClose');

  btnChat.addEventListener('click', () => {
    chatWindow.classList.toggle('active');
    if (chatWindow.classList.contains('active')) {
      document.getElementById('chatInput').focus();
    }
  });

  btnClose.addEventListener('click', () => {
    chatWindow.classList.remove('active');
  });

  // 5. Chat Engine
  window.zamzyChat = {
    ask: function(topic) {
      if (topic === 'download') {
        this.addMsg('How do I download my purchased products?', 'user');
        setTimeout(() => {
          this.addMsg('To access and download your products, visit our <strong><a href="/digiproduct/access" style="color:#818cf8; text-decoration:underline;">Customer Access Vault</a></strong>. Simply enter your registered Email or WhatsApp mobile number to receive a 6-digit OTP and unlock your files instantly!', 'bot');
        }, 400);
      } else if (topic === 'coupon') {
        this.addMsg('Do you have discount coupons?', 'user');
        setTimeout(() => {
          this.addMsg('🎉 Yes! You can use promo code <strong>SAS</strong> at checkout for an instant <strong>50% discount</strong> on all digital products!', 'bot');
        }, 400);
      } else if (topic === 'otp') {
        this.addMsg('I did not receive my OTP code', 'user');
        setTimeout(() => {
          this.addMsg('Please ensure you entered a valid 10-digit WhatsApp number without spaces. We dispatch codes via WhatsApp and Email simultaneously. Check your WhatsApp chats and spam folder, or contact <strong>work@zamzy.in</strong>.', 'bot');
        }, 400);
      } else if (topic === 'support') {
        this.addMsg('How do I contact support?', 'user');
        setTimeout(() => {
          this.addMsg('Our engineering team is ready to help! 📧 Email: <strong><a href="mailto:work@zamzy.in" style="color:#818cf8;">work@zamzy.in</a></strong><br>📱 WhatsApp: <strong>+91 7287060553</strong>', 'bot');
        }, 400);
      }
    },

    handleSubmit: function(e) {
      e.preventDefault();
      const input = document.getElementById('chatInput');
      const text = input.value.trim();
      if (!text) return false;

      this.addMsg(text, 'user');
      input.value = '';

      const lower = text.toLowerCase();
      setTimeout(() => {
        if (lower.includes('download') || lower.includes('access') || lower.includes('file') || lower.includes('pdf')) {
          this.addMsg('You can access your PDF downloads anytime via the <a href="/digiproduct/access" style="color:#818cf8; text-decoration:underline;">Customer Vault</a> using your registered WhatsApp phone or Email.', 'bot');
        } else if (lower.includes('coupon') || lower.includes('discount') || lower.includes('code') || lower.includes('offer')) {
          this.addMsg('Use code <strong>SAS</strong> at checkout for 50% OFF!', 'bot');
        } else if (lower.includes('webinar') || lower.includes('class') || lower.includes('course')) {
          this.addMsg('Learn more about our live Full Stack Cloud Webinar at <a href="/fullstack-webinar" target="_blank" style="color:#818cf8; text-decoration:underline;">zamzy.in/fullstack-webinar</a>.', 'bot');
        } else if (lower.includes('contact') || lower.includes('email') || lower.includes('help') || lower.includes('phone') || lower.includes('whatsapp')) {
          this.addMsg('You can reach our helpdesk at <strong>work@zamzy.in</strong> or WhatsApp at <strong>+91 7287060553</strong>.', 'bot');
        } else {
          this.addMsg('Thank you for reaching out! For specific orders or inquiries, please check our <a href="/digiproduct/access" style="color:#818cf8; text-decoration:underline;">Access Vault</a> or email us at <strong>work@zamzy.in</strong>.', 'bot');
        }
      }, 500);

      return false;
    },

    addMsg: function(htmlText, sender) {
      const msgs = document.getElementById('chatMessages');
      const div = document.createElement('div');
      div.className = `chat-msg chat-msg-${sender}`;
      div.innerHTML = htmlText;
      msgs.appendChild(div);
      msgs.scrollTop = msgs.scrollHeight;
    }
  };
})();
