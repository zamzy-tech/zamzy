<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ZAMZY — Enterprise WhatsApp Gateway, 2FA API &amp; Autonomous Automation Engine</title>
  <meta name="description" content="High-concurrency WhatsApp automation engine, 2FA OTP verification API, YouTube creator broadcast pipelines, and multi-tenant Baileys clusters by ZAMZY Technologies.">
  <link rel="shortcut icon" href="../images/logo.png" type="image/png">
  <link rel="icon" href="../images/logo.png" type="image/png">
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Barlow+Condensed:wght@400;600;700;800&family=IBM+Plex+Mono:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --bg-deep: #05060b;
      --bg-surface: #0a0c16;
      --bg-card: rgba(14, 17, 30, 0.75);
      
      --cyan: #00ffcc;
      --cyan-glow: rgba(0, 255, 204, 0.22);
      --purple: #9d4edd;
      --purple-neon: #c77dff;
      --purple-glow: rgba(157, 78, 221, 0.25);
      --wa-green: #25D366;
      --wa-glow: rgba(37, 211, 102, 0.25);
      
      --text-main: #f8fafc;
      --text-muted: #94a3b8;
      --text-dim: #64748b;
      --border-subtle: rgba(255, 255, 255, 0.08);
      --border-glow: rgba(0, 255, 204, 0.35);

      --font-display: 'Space Grotesk', sans-serif;
      --font-accent: 'Barlow Condensed', sans-serif;
      --font-mono: 'IBM Plex Mono', monospace;
      --font-body: 'Inter', sans-serif;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: var(--font-body);
      background-color: var(--bg-deep);
      color: var(--text-main);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      overflow-x: hidden;
      position: relative;
      background-image: 
        radial-gradient(circle at 15% 15%, rgba(157, 78, 221, 0.12) 0%, transparent 45%),
        radial-gradient(circle at 85% 85%, rgba(0, 255, 204, 0.1) 0%, transparent 45%),
        linear-gradient(to bottom, #05060b, #070913);
    }

    /* Subtle Cyber Grid Background */
    body::before {
      content: "";
      position: absolute;
      inset: 0;
      background-size: 50px 50px;
      background-image: 
        linear-gradient(to right, rgba(255, 255, 255, 0.02) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
      pointer-events: none;
      z-index: 0;
    }

    /* Ambient Glow Blobs */
    .ambient-glow-1 {
      position: absolute;
      top: -150px;
      left: 10%;
      width: 550px;
      height: 550px;
      background: radial-gradient(circle, var(--purple-glow) 0%, transparent 70%);
      pointer-events: none;
      z-index: 1;
      filter: blur(50px);
    }

    .ambient-glow-2 {
      position: absolute;
      bottom: -100px;
      right: 5%;
      width: 600px;
      height: 600px;
      background: radial-gradient(circle, var(--cyan-glow) 0%, transparent 70%);
      pointer-events: none;
      z-index: 1;
      filter: blur(60px);
    }

    /* ═══════════════════════════════════════════════
       HEADER & NAVIGATION
    ═══════════════════════════════════════════════ */
    header {
      width: 100%;
      max-width: 1400px;
      margin: 0 auto;
      padding: 24px 28px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: relative;
      z-index: 100;
    }

    .brand-wrap {
      display: flex;
      align-items: center;
      gap: 14px;
      text-decoration: none;
    }

    .brand-logo-text {
      font-family: var(--font-display);
      font-size: 26px;
      font-weight: 800;
      letter-spacing: 1.5px;
      color: #ffffff;
      display: flex;
      align-items: baseline;
    }

    .brand-logo-text span {
      color: var(--cyan);
      text-shadow: 0 0 14px var(--cyan);
    }

    .brand-badge {
      font-family: var(--font-mono);
      font-size: 9.5px;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      background: rgba(37, 211, 102, 0.12);
      border: 1px solid rgba(37, 211, 102, 0.4);
      color: var(--wa-green);
      padding: 3px 8px;
      border-radius: 4px;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .brand-badge::before {
      content: "";
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: var(--wa-green);
      box-shadow: 0 0 8px var(--wa-green);
    }

    nav {
      display: flex;
      align-items: center;
      gap: 28px;
    }

    nav a {
      text-decoration: none;
      font-family: var(--font-mono);
      font-size: 11.5px;
      font-weight: 600;
      color: var(--text-muted);
      letter-spacing: 1px;
      text-transform: uppercase;
      transition: all 0.25s ease;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    nav a:hover {
      color: var(--cyan);
      text-shadow: 0 0 10px rgba(0, 255, 204, 0.35);
    }

    .nav-cta-btn {
      color: #06070e !important;
      background: linear-gradient(135deg, var(--cyan) 0%, #38bdf8 100%);
      padding: 10px 22px;
      border-radius: 8px;
      font-family: var(--font-mono);
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1px;
      box-shadow: 0 0 20px rgba(0, 255, 204, 0.3);
      transition: all 0.3s ease;
    }

    .nav-cta-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 0 30px rgba(0, 255, 204, 0.6);
      color: #000 !important;
    }

    /* ═══════════════════════════════════════════════
       MAIN HERO SECTION (FULL SCREEN)
    ═══════════════════════════════════════════════ */
    main {
      flex: 1;
      width: 100%;
      max-width: 1400px;
      margin: 0 auto;
      padding: 20px 28px 60px 28px;
      position: relative;
      z-index: 10;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .hero-grid {
      display: grid;
      grid-template-columns: 1.15fr 0.85fr;
      gap: 50px;
      align-items: center;
      width: 100%;
      min-height: calc(100vh - 180px);
    }

    .hero-content {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      z-index: 5;
    }

    /* Live Cluster Status Pill */
    .status-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(0, 255, 204, 0.08);
      border: 1px solid rgba(0, 255, 204, 0.3);
      border-radius: 999px;
      padding: 5px 14px;
      font-family: var(--font-mono);
      font-size: 10.5px;
      font-weight: 700;
      letter-spacing: 1.5px;
      color: var(--cyan);
      text-transform: uppercase;
      margin-bottom: 22px;
      box-shadow: 0 0 20px rgba(0, 255, 204, 0.15);
    }

    .status-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: var(--cyan);
      box-shadow: 0 0 10px var(--cyan);
      animation: pulseActive 1.8s infinite;
    }

    @keyframes pulseActive {
      0%, 100% { transform: scale(1); opacity: 1; }
      50% { transform: scale(1.4); opacity: 0.5; }
    }

    .hero-title {
      font-family: var(--font-display);
      font-size: clamp(38px, 4.4vw, 64px);
      font-weight: 800;
      line-height: 1.05;
      color: #ffffff;
      margin-bottom: 22px;
      letter-spacing: -1px;
      text-transform: uppercase;
    }

    .hero-title .highlight-cyan {
      color: var(--cyan);
      text-shadow: 0 0 25px rgba(0, 255, 204, 0.4);
    }

    .hero-title .highlight-purple {
      background: linear-gradient(135deg, #c77dff 0%, #8b5cf6 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .hero-description {
      font-size: 15.5px;
      color: var(--text-muted);
      line-height: 1.7;
      max-width: 620px;
      margin-bottom: 34px;
      font-weight: 400;
    }

    .hero-description strong {
      color: #ffffff;
      font-weight: 600;
    }

    .hero-actions {
      display: flex;
      gap: 16px;
      flex-wrap: wrap;
      margin-bottom: 38px;
    }

    .btn-action-primary {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: linear-gradient(135deg, var(--cyan) 0%, #38bdf8 100%);
      color: #06070e;
      font-family: var(--font-mono);
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      text-decoration: none;
      padding: 15px 30px;
      border-radius: 10px;
      box-shadow: 0 8px 30px rgba(0, 255, 204, 0.35);
      transition: all 0.3s ease;
    }

    .btn-action-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 40px rgba(0, 255, 204, 0.6);
      color: #000;
    }

    .btn-action-secondary {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: rgba(255, 255, 255, 0.04);
      color: #ffffff;
      font-family: var(--font-mono);
      font-size: 13px;
      font-weight: 600;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      text-decoration: none;
      padding: 15px 28px;
      border-radius: 10px;
      border: 1px solid rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(10px);
      transition: all 0.3s ease;
    }

    .btn-action-secondary:hover {
      background: rgba(255, 255, 255, 0.08);
      border-color: var(--cyan);
      color: var(--cyan);
      transform: translateY(-2px);
    }

    /* Live KPI Stats Row */
    .kpi-stats-strip {
      display: grid;
      grid-template-columns: repeat(4, auto);
      gap: 24px;
      padding-top: 24px;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      width: 100%;
    }

    .kpi-stat-item {
      display: flex;
      flex-direction: column;
    }

    .kpi-stat-val {
      font-family: var(--font-display);
      font-size: 20px;
      font-weight: 700;
      color: #ffffff;
    }

    .kpi-stat-label {
      font-family: var(--font-mono);
      font-size: 10px;
      color: var(--text-dim);
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-top: 3px;
    }

    /* ═══════════════════════════════════════════════
       RIGHT COLUMN: DEVICE SIMULATOR & LIVE CONSOLE
    ═══════════════════════════════════════════════ */
    .hero-device-wrapper {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      position: relative;
    }

    .device-card {
      background: var(--bg-card);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(0, 255, 204, 0.3);
      border-radius: 24px;
      padding: 20px;
      width: 100%;
      max-width: 460px;
      box-shadow: 0 25px 70px rgba(0, 0, 0, 0.8), 0 0 45px rgba(0, 255, 204, 0.15);
      position: relative;
      overflow: hidden;
    }

    .device-card::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 2px;
      background: linear-gradient(90deg, transparent, var(--cyan), var(--purple), transparent);
    }

    .device-topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 14px;
    }

    .device-node-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(37, 211, 102, 0.1);
      border: 1px solid rgba(37, 211, 102, 0.3);
      color: var(--wa-green);
      font-family: var(--font-mono);
      font-size: 9.5px;
      font-weight: 700;
      padding: 3px 10px;
      border-radius: 999px;
      text-transform: uppercase;
    }

    .device-controls-dots {
      display: flex;
      gap: 5px;
    }

    .control-dot {
      width: 9px;
      height: 9px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.15);
    }

    .device-video-frame {
      position: relative;
      border-radius: 16px;
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, 0.08);
      background: #000;
      aspect-ratio: 4 / 4.7;
    }

    .device-video-frame video {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    /* Live REST API Terminal Simulator */
    .terminal-box {
      margin-top: 14px;
      background: rgba(0, 0, 0, 0.65);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 12px;
      padding: 12px 16px;
      font-family: var(--font-mono);
      font-size: 10.5px;
      line-height: 1.6;
      color: #94a3b8;
    }

    .terminal-box .route {
      color: var(--cyan);
      font-weight: 700;
    }

    .terminal-box .code-val {
      color: #a78bfa;
    }

    .terminal-box .success-tag {
      color: var(--wa-green);
      font-weight: 700;
    }

    /* ═══════════════════════════════════════════════
       FEATURE PILLARS BAR
    ═══════════════════════════════════════════════ */
    .features-section {
      width: 100%;
      max-width: 1400px;
      margin: 20px auto 40px auto;
      padding: 0 28px;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 18px;
    }

    .feature-card {
      background: rgba(14, 17, 30, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.06);
      border-radius: 16px;
      padding: 22px 20px;
      backdrop-filter: blur(12px);
      transition: all 0.3s ease;
    }

    .feature-card:hover {
      border-color: rgba(0, 255, 204, 0.35);
      transform: translateY(-4px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }

    .feature-icon {
      font-size: 24px;
      margin-bottom: 12px;
      display: inline-block;
    }

    .feature-title {
      font-family: var(--font-display);
      font-size: 15px;
      font-weight: 700;
      color: #ffffff;
      margin-bottom: 6px;
    }

    .feature-desc {
      font-size: 12px;
      color: var(--text-muted);
      line-height: 1.55;
    }

    /* ═══════════════════════════════════════════════
       FOOTER
    ═══════════════════════════════════════════════ */
    footer {
      width: 100%;
      border-top: 1px solid rgba(255, 255, 255, 0.06);
      padding: 24px 28px;
      font-family: var(--font-mono);
      font-size: 11.5px;
      color: var(--text-dim);
      position: relative;
      z-index: 10;
      background: rgba(5, 6, 11, 0.85);
      backdrop-filter: blur(12px);
    }

    .footer-inner {
      max-width: 1400px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 14px;
    }

    .footer-links {
      display: flex;
      gap: 20px;
    }

    .footer-links a {
      color: var(--text-muted);
      text-decoration: none;
      transition: color 0.2s;
    }

    .footer-links a:hover {
      color: var(--cyan);
    }

    /* ═══════════════════════════════════════════════
       RESPONSIVENESS
    ═══════════════════════════════════════════════ */
    @media (max-width: 1100px) {
      .hero-grid {
        grid-template-columns: 1fr;
        gap: 40px;
        min-height: auto;
      }
      .features-grid {
        grid-template-columns: repeat(2, 1fr);
      }
      .kpi-stats-strip {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
      }
    }

    @media (max-width: 768px) {
      header {
        padding: 18px 20px;
      }
      nav a {
        display: none;
      }
      nav a.nav-cta-btn {
        display: inline-flex;
      }
      .features-grid {
        grid-template-columns: 1fr;
      }
      .footer-inner {
        flex-direction: column;
        text-align: center;
      }
    }
  </style>
</head>
<body>

  <!-- Ambient Glow Backgrounds -->
  <div class="ambient-glow-1"></div>
  <div class="ambient-glow-2"></div>

  <!-- Header -->
  <header>
    <a href="index.php" class="brand-wrap" style="display:flex; align-items:center; gap:12px;">
      <img src="zamzy_logo.png" alt="ZAMZY" style="height: 52px; width: auto; max-width: 230px; object-fit: contain;">
      <div class="brand-badge">GATEWAY 2FA</div>
    </a>
    
    <nav>
      <a href="login.php"><span>⚡</span> Staff Console</a>
      <a href="api_link.php"><span>🔑</span> Developer Hub</a>
      <a href="yt_login.php"><span>🎬</span> Creator Portal</a>
      <a href="api_docs.php"><span>📖</span> API Docs</a>
      <a href="../index.html" style="color:var(--cyan);"><span>↗</span> Main ZAMZY.IN</a>
      <a href="login.php" class="nav-cta-btn">LAUNCH CONSOLE ⚡</a>
    </nav>
  </header>

  <!-- Main Hero (Full Screen) -->
  <main>
    <section class="hero-grid">
      
      <!-- Left Column: Headline & Controls -->
      <div class="hero-content">
        <div class="status-pill">
          <span class="status-dot"></span>
          NODE.JS BAILEYS CLUSTERS ACTIVE · 99.99% UPTIME
        </div>

        <h1 class="hero-title">
          AUTONOMOUS<br>
          <span class="highlight-cyan">WHATSAPP GATEWAY</span><br>
          &amp; <span class="highlight-purple">2FA API ENGINE</span>
        </h1>

        <p class="hero-description">
          Industrial-grade WhatsApp infrastructure engineered for high-velocity transactional OTP delivery, autonomous YouTube audience alerts, smart auto-responder chatbot networks, and low-latency multi-channel webhooks.
        </p>

        <div class="hero-actions">
          <a href="login.php" class="btn-action-primary">
            <span>⚡ Launch Staff Console</span>
            <span>→</span>
          </a>
          <a href="api_docs.php" class="btn-action-secondary">
            <span>🔑 REST API Reference</span>
          </a>
          <a href="https://wa.me/917287060553?text=Hello%20ZAMZY!%20I%20am%20inquiring%20about%20your%20WhatsApp%20Gateway%20and%20API%20Solutions." target="_blank" rel="noopener" class="btn-action-secondary" style="border-color:rgba(37,211,102,0.4); color:var(--wa-green);">
            <span>💬 Live Support</span>
          </a>
        </div>

        <!-- Live Infrastructure KPI Stats Strip -->
        <div class="kpi-stats-strip">
          <div class="kpi-stat-item">
            <span class="kpi-stat-val" style="color:var(--cyan);">350M+</span>
            <span class="kpi-stat-label">Messages Dispatched</span>
          </div>
          <div class="kpi-stat-item">
            <span class="kpi-stat-val" style="color:#a78bfa;">0.12s</span>
            <span class="kpi-stat-label">Mean Gateway Latency</span>
          </div>
          <div class="kpi-stat-item">
            <span class="kpi-stat-val" style="color:var(--wa-green);">256-Bit</span>
            <span class="kpi-stat-label">End-to-End Encryption</span>
          </div>
          <div class="kpi-stat-item">
            <span class="kpi-stat-val" style="color:#f8fafc;">99.99%</span>
            <span class="kpi-stat-label">Cluster SLA Uptime</span>
          </div>
        </div>
      </div>

      <!-- Right Column: Visual Simulator Card & Live Terminal -->
      <div class="hero-device-wrapper">
        <div class="device-card">
          <div class="device-topbar">
            <div class="device-node-pill">
              <span style="width:6px; height:6px; border-radius:50%; background:var(--wa-green); display:inline-block;"></span>
              SESSION #01 · ACTIVE NODE
            </div>
            <div class="device-controls-dots">
              <div class="control-dot"></div>
              <div class="control-dot"></div>
              <div class="control-dot"></div>
            </div>
          </div>

          <!-- Video Showcase -->
          <div class="device-video-frame">
            <video autoplay loop muted playsinline>
              <source src="ui_design/assets/img/WHATSAPP.MP4" type="video/mp4">
              Your browser does not support video playback.
            </video>
          </div>

          <!-- Interactive Terminal Status -->
          <div class="terminal-box">
            <div><span class="route">POST</span> /api/v2/broadcast-dispatch</div>
            <div>STATUS: <span class="success-tag">200 OK</span> · ACK: <span class="code-val">baileys_ack_recv</span></div>
            <div>LATENCY: <span class="code-val">82ms</span> · PROTOCOL: <span style="color:#fff;">WSS / TLS 1.3</span></div>
          </div>
        </div>
      </div>

    </section>
  </main>

  <!-- Feature Pillars Grid -->
  <section class="features-section">
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">⚡</div>
        <h3 class="feature-title">2FA &amp; Transactional OTP</h3>
        <p class="feature-desc">Sub-second WhatsApp OTP dispatch with automatic fallback, rate limiting, and tamper-proof verification.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon">🎬</div>
        <h3 class="feature-title">Creator &amp; Media Broadcasts</h3>
        <p class="feature-desc">Automated YouTube video push notifications, group broadcasts, and rich thumbnail / caption delivery.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon">🤖</div>
        <h3 class="feature-title">AI Auto-Responder Engine</h3>
        <p class="feature-desc">Keyword routers, natural language conversational bots, and automated customer qualification pipelines.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon">🛡️</div>
        <h3 class="feature-title">Baileys Cluster Nodes</h3>
        <p class="feature-desc">High-concurrency multi-device WebSocket pairing, failover state retention, and SQLite persistence.</p>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="footer-inner">
      <div>
        <span>&copy; 2026 ZAMZY Technologies. All rights reserved. Cloud Infrastructure &amp; Autonomous Systems.</span>
      </div>
      <div class="footer-links">
        <a href="terms.php">Terms of Service</a>
        <a href="privacy.php">Privacy Policy</a>
        <a href="login.php">Staff Console</a>
        <a href="../index.html">ZAMZY Main Studio</a>
      </div>
    </div>
  </footer>

</body>
</html>
