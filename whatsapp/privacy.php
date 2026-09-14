<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Privacy Policy – ZAMZY WhatsApp Gateway</title>
  <meta name="description" content="Privacy Policy detailing data collection, storage, and WhatsApp gateway security settings at ZAMZY.">
  <link rel="shortcut icon" href="../images/logo.png" type="image/png">
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700;800&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;600&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --bg-main: #06060b;
      --bg-surface: #0a0c16;
      --bg-card: rgba(14, 17, 30, 0.85);
      --text-primary: #F3F4F6;
      --text-secondary: #9CA3AF;
      --text-muted: #6B7280;
      
      --cyan: #00ffcc;
      --cyan-glow: rgba(0, 255, 204, 0.15);
      --purple: #8B5CF6;
      --purple-glow: rgba(139, 92, 246, 0.15);
      
      --border-color: rgba(255, 255, 255, 0.08);
      
      --font-heading: 'Space Grotesk', sans-serif;
      --font-body: 'Inter', sans-serif;
      --font-mono: 'IBM Plex Mono', monospace;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: var(--font-body);
      background-color: var(--bg-main);
      color: var(--text-primary);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      overflow-x: hidden;
      position: relative;
    }

    .glow-cyan {
      position: absolute;
      top: -10%;
      right: -10%;
      width: 50vw;
      height: 50vw;
      background: radial-gradient(circle, var(--cyan-glow) 0%, transparent 70%);
      pointer-events: none;
      z-index: 1;
    }

    header {
      width: 100%;
      max-width: 900px;
      margin: 0 auto;
      padding: 32px 20px 20px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: relative;
      z-index: 10;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
    }

    .brand-name {
      font-family: var(--font-heading);
      font-size: 24px;
      font-weight: 800;
      color: #fff;
      letter-spacing: 1px;
    }

    .brand-name span {
      color: var(--cyan);
      text-shadow: 0 0 10px var(--cyan);
    }

    .nav-btn {
      text-decoration: none;
      font-family: var(--font-mono);
      font-size: 11px;
      font-weight: 700;
      color: var(--cyan);
      background: rgba(0, 255, 204, 0.1);
      border: 1px solid rgba(0, 255, 204, 0.3);
      padding: 8px 16px;
      border-radius: 8px;
      transition: all 0.3s ease;
      letter-spacing: 0.5px;
    }

    .nav-btn:hover {
      background: var(--cyan);
      color: #06060b;
      box-shadow: 0 0 15px rgba(0, 255, 204, 0.4);
    }

    main {
      flex: 1;
      width: 100%;
      max-width: 900px;
      margin: 0 auto;
      padding: 20px 20px 80px 20px;
      position: relative;
      z-index: 10;
    }

    .content-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 20px 50px rgba(0,0,0,0.5);
      backdrop-filter: blur(16px);
    }

    h1 {
      font-family: var(--font-heading);
      font-size: clamp(26px, 4vw, 36px);
      font-weight: 800;
      color: #fff;
      margin-bottom: 8px;
      letter-spacing: -0.5px;
    }

    .last-updated {
      font-family: var(--font-mono);
      font-size: 12px;
      color: var(--cyan);
      margin-bottom: 30px;
      display: inline-block;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    h2 {
      font-family: var(--font-heading);
      font-size: 18px;
      font-weight: 700;
      color: #fff;
      margin-top: 30px;
      margin-bottom: 12px;
    }

    p {
      font-size: 14.5px;
      color: var(--text-secondary);
      line-height: 1.7;
      margin-bottom: 16px;
    }

    ul {
      margin-left: 20px;
      margin-bottom: 20px;
    }

    li {
      font-size: 14px;
      color: var(--text-secondary);
      line-height: 1.6;
      margin-bottom: 8px;
    }

    code {
      font-family: var(--font-mono);
      background: rgba(255, 255, 255, 0.06);
      padding: 2px 6px;
      border-radius: 4px;
      color: var(--cyan);
      font-size: 13px;
    }

    footer {
      width: 100%;
      border-top: 1px solid var(--border-color);
      padding: 24px 20px;
      text-align: center;
      font-family: var(--font-mono);
      font-size: 11.5px;
      color: var(--text-muted);
      position: relative;
      z-index: 10;
      background: rgba(6, 6, 11, 0.85);
      backdrop-filter: blur(10px);
    }
  </style>
</head>
<body>

  <div class="glow-cyan"></div>

  <header>
    <a href="index.php" class="brand">
      <span class="brand-name">ZAMZY<span>.</span></span>
    </a>
    <a href="index.php" class="nav-btn">➔ Back to Gateway</a>
  </header>

  <main>
    <article class="content-card">
      <h1>Privacy Policy</h1>
      <div class="last-updated">Last Updated: September 2026</div>

      <p>At ZAMZY Technologies, we take your privacy seriously. This Privacy Policy details how we collect, store, and secure your data when utilizing our 2FA and WhatsApp gateway portal.</p>

      <h2>1. Information We Collect</h2>
      <p>To provide our services, we collect limited structural data, including:</p>
      <ul>
        <li><strong>Account Details:</strong> Usernames, phone numbers, and encrypted password hashes provided during signup.</li>
        <li><strong>WhatsApp Session Data:</strong> Auth credentials generated during QR scan (stored strictly inside secure, isolated directories in <code>auth_info_baileys/</code> on the server).</li>
        <li><strong>API Logs:</strong> Timestamp, recipient phone numbers, and delivery status logs for debugging and rate validation.</li>
      </ul>
      <p>We do NOT store or read the contents of your personal WhatsApp chats, contact lists, or media files.</p>

      <h2>2. How We Secure Your Data</h2>
      <p>We implement industry-standard server protections to keep your active WhatsApp connections safe:</p>
      <ul>
        <li><strong>Session Isolation:</strong> Each device session is written to a unique, isolated folder on the server.</li>
        <li><strong>Password Hashing:</strong> All login and administrative passwords are hashed using high-strength bcrypt algorithms.</li>
        <li><strong>Token Security:</strong> API Bearer tokens are generated cryptographically to prevent guessing or unauthorized extraction.</li>
      </ul>

      <h2>3. Data Deletion &amp; Unlinking</h2>
      <p>You have full ownership of your data. If you click <strong>"Disconnect Device"</strong> on the Developer or Creator portals:</p>
      <ul>
        <li>Your credentials directory is instantly deleted from the server disk.</li>
        <li>Your database connection flags are cleared.</li>
        <li>Your WhatsApp socket is actively logged out from our systems.</li>
      </ul>

      <h2>4. Cookies &amp; Tracking</h2>
      <p>We use essential cookies strictly to maintain user sessions (keeping you logged in to your developer/creator dashboard). We do not load advertising or tracking scripts that monitor your behavior across third-party websites.</p>

      <h2>5. Updates to this Policy</h2>
      <p>We may update this Privacy Policy periodically to reflect shifts in gateway technologies or security compliance. Significant changes will be announced directly on the portal homepage.</p>
    </article>
  </main>

  <footer>
    <div>
      <span>&copy; 2026 ZAMZY Technologies. All rights reserved.</span>
    </div>
  </footer>

</body>
</html>
