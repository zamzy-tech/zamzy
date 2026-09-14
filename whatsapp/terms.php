<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Terms &amp; Conditions – THE EXPERT HUB 2FA</title>
  <meta name="description" content="Terms of Service and Acceptable Use Policy for THE EXPERT HUB WhatsApp Gateway &amp; 2FA API services.">
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

    .glow-purple {
      position: absolute;
      top: -10%;
      left: -10%;
      width: 50vw;
      height: 50vw;
      background: radial-gradient(circle, var(--purple-glow) 0%, transparent 70%);
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

  <div class="glow-purple"></div>

  <header>
    <a href="index.php" class="brand">
      <img src="teh_logo.png" alt="THE EXPERT HUB Logo">
      <span class="brand-name">THE EXPERT HUB</span>
    </a>
    <a href="index.php" class="nav-btn">➔ Back to Home</a>
  </header>

  <main>
    <article class="content-card">
      <h1>Terms &amp; Conditions</h1>
      <div class="last-updated">Last Updated: July 2026</div>

      <p>Welcome to THE EXPERT HUB 2FA Portal. By accessing or using our website, APIs, WhatsApp gateway, and automated notification services (collectively, the "Services"), you agree to be bound by these Terms &amp; Conditions. Please read them carefully.</p>

      <h2>1. Acceptable Use Policy</h2>
      <p>Our WhatsApp Gateway and API services are designed for legitimate transaction updates, verification codes (OTPs), automated customer support, and creator upload alerts. You agree NOT to use the service for:</p>
      <ul>
        <li>Sending unsolicited bulk commercial messages (Spam).</li>
        <li>Distributing phishing links, malware, or fraudulent content.</li>
        <li>Harassing, threatening, or impersonating any individual or business entity.</li>
        <li>Violating Meta/WhatsApp's official Terms of Service.</li>
      </ul>
      <p>Any violation of the acceptable use policy will result in immediate termination of your API slots and developer keys without refund.</p>

      <h2>2. Account Security &amp; Keys</h2>
      <p>You are solely responsible for maintaining the confidentiality of your account credentials, session states, and Bearer API keys. You agree to notify us immediately of any unauthorized use of your keys. We are not liable for any losses caused by third-party access to your active WhatsApp gateway connection slots.</p>

      <h2>3. API Usage Limits &amp; Fair Use</h2>
      <p>Your subscription tier controls the number of active WhatsApp scanner slots and message delivery volumes. Standard rate limits apply to prevent gateway buffer overload. Any script or integration that attempts to bypass rate limits or compromise server stability will be automatically throttled or temporarily suspended.</p>

      <h2>4. Service Availability &amp; Disclaimer</h2>
      <p>The WhatsApp gateway relies on active Baileys socket connections and third-party networks. While we strive to maintain 99.9% uptime, the service is provided on an "as-is" and "as-available" basis. We do not guarantee that message delivery will be instantaneous or error-free in the event of Meta platform updates or network maintenance.</p>

      <h2>5. Limitation of Liability</h2>
      <p>To the maximum extent permitted by law, THE EXPERT HUB and its developers shall not be liable for any direct, indirect, incidental, or consequential damages resulting from the use or inability to use the services, including loss of business profits, data corruption, or account banning by WhatsApp.</p>

      <h2>6. Modifications to Terms</h2>
      <p>We reserve the right to modify these terms at any time. Changes will be posted directly on this page. Your continued use of the services following term modifications constitutes acceptance of the updated terms.</p>
    </article>
  </main>

  <footer>
    <div>
      <span>© 2026 THE EXPERT HUB. All rights reserved.</span>
    </div>
  </footer>

</body>
</html>
