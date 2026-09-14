<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Privacy Policy – THE EXPERT HUB 2FA</title>
  <meta name="description" content="Privacy Policy detailing data collection, storage, and WhatsApp gateway security settings at THE EXPERT HUB.">
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Inter:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --bg-main: #060609;
      --bg-surface: #0E0E14;
      --bg-card: #15151F;
      --text-primary: #F3F4F6;
      --text-secondary: #9CA3AF;
      --text-muted: #6B7280;
      
      --lime: #D4FF3D;
      --lime-glow: rgba(212, 255, 61, 0.1);
      --purple: #8B5CF6;
      --purple-glow: rgba(139, 92, 246, 0.1);
      
      --border-color: rgba(255, 255, 255, 0.06);
      --font-heading: 'Syne', 'Outfit', sans-serif;
      --font-body: 'Inter', sans-serif;
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

    .glow-lime {
      position: absolute;
      bottom: -10%;
      right: -10%;
      width: 50vw;
      height: 50vw;
      background: radial-gradient(circle, var(--lime-glow) 0%, transparent 70%);
      pointer-events: none;
      z-index: 1;
    }

    header {
      width: 100%;
      max-width: 1000px;
      margin: 0 auto;
      padding: 24px 20px;
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

    .brand img {
      height: 38px;
      width: auto;
    }

    .brand-name {
      font-family: var(--font-heading);
      font-size: 18px;
      font-weight: 800;
      color: #fff;
    }

    .nav-btn {
      text-decoration: none;
      font-size: 13px;
      font-weight: 600;
      color: var(--text-secondary);
      border: 1px solid var(--border-color);
      padding: 8px 16px;
      border-radius: 30px;
      transition: all 0.3s ease;
      background: rgba(255,255,255,0.02);
    }

    .nav-btn:hover {
      color: #fff;
      border-color: var(--lime);
      background: rgba(255,255,255,0.05);
    }

    main {
      flex: 1;
      width: 100%;
      max-width: 1000px;
      margin: 0 auto;
      padding: 40px 20px 80px 20px;
      position: relative;
      z-index: 10;
    }

    .content-card {
      background: var(--bg-surface);
      border: 1px solid var(--border-color);
      border-radius: 24px;
      padding: 48px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
    }

    h1 {
      font-family: var(--font-heading);
      font-size: clamp(28px, 4vw, 38px);
      font-weight: 800;
      color: #fff;
      margin-bottom: 8px;
    }

    .last-updated {
      font-size: 12px;
      color: var(--lime);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 40px;
    }

    h2 {
      font-family: var(--font-heading);
      font-size: 20px;
      font-weight: 700;
      color: #fff;
      margin-top: 32px;
      margin-bottom: 12px;
      border-left: 3px solid var(--lime);
      padding-left: 12px;
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
      color: var(--text-secondary);
      font-size: 14.5px;
      line-height: 1.7;
    }

    li {
      margin-bottom: 8px;
    }

    footer {
      width: 100%;
      border-top: 1px solid var(--border-color);
      padding: 24px 20px;
      text-align: center;
      font-size: 12px;
      color: var(--text-muted);
      position: relative;
      z-index: 10;
      background: rgba(6, 6, 9, 0.8);
      backdrop-filter: blur(10px);
    }

    @media (max-width: 768px) {
      .content-card {
        padding: 30px 20px;
      }
    }
  </style>
</head>
<body>

  <div class="glow-lime"></div>

  <header>
    <a href="index.php" class="brand">
      <img src="teh_logo.png" alt="THE EXPERT HUB Logo">
      <span class="brand-name">THE EXPERT HUB</span>
    </a>
    <a href="index.php" class="nav-btn">➔ Back to Home</a>
  </header>

  <main>
    <article class="content-card">
      <h1>Privacy Policy</h1>
      <div class="last-updated">Last Updated: July 2026</div>

      <p>At THE EXPERT HUB, we take your privacy seriously. This Privacy Policy details how we collect, store, and secure your data when utilizing our 2FA and WhatsApp gateway portal.</p>

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
      <span>© 2026 THE EXPERT HUB. All rights reserved.</span>
    </div>
  </footer>

</body>
</html>
