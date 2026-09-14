<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Secure WhatsApp Gateway &amp; Developer Hub – THE EXPERT HUB</title>
  <meta name="description" content="Securely connect WhatsApp gateway, configure automated YouTube alerts, manage developer REST APIs, and access invoice generation from THE EXPERT HUB.">
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
  <link rel="icon" href="favicon.ico" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Inter:wght@400;500;600;700&family=Syne:wght@700;800;900&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --bg-main: #060609;
      --bg-surface: #0E0E14;
      --bg-card: #15151F;
      --text-primary: #F3F4F6;
      --text-secondary: #9CA3AF;
      --text-muted: #6B7280;
      
      --lime: #D4FF3D;
      --lime-glow: rgba(212, 255, 61, 0.15);
      --purple: #8B5CF6;
      --purple-glow: rgba(139, 92, 246, 0.15);
      
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

    /* Ambient Background Glows */
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

    /* Header styling */
    header {
      width: 100%;
      max-width: 1300px;
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
      gap: 10px;
      text-decoration: none;
    }

    .brand-box {
      border: 2px solid var(--lime);
      color: var(--lime);
      font-family: var(--font-heading);
      font-size: 13px;
      font-weight: 900;
      padding: 2px 6px;
      border-radius: 6px;
      letter-spacing: 0.5px;
    }

    .brand-name {
      font-family: var(--font-heading);
      font-size: 20px;
      font-weight: 800;
      color: #fff;
      letter-spacing: 0.5px;
    }

    nav {
      display: flex;
      align-items: center;
      gap: 32px;
    }

    nav a {
      text-decoration: none;
      font-size: 11.5px;
      font-weight: 800;
      color: var(--text-secondary);
      letter-spacing: 1.5px;
      transition: color 0.3s;
    }

    nav a:hover {
      color: #fff;
    }

    .nav-cta {
      color: #060609 !important;
      background: var(--lime);
      padding: 10px 20px;
      border-radius: 30px;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
    }

    .nav-cta:hover {
      background: #9ccb1f;
      box-shadow: 0 0 15px rgba(212, 255, 61, 0.4);
    }

    /* Main Container */
    main {
      flex: 1;
      width: 100%;
      max-width: 1300px;
      margin: 0 auto;
      padding: 40px 20px 80px 20px;
      position: relative;
      z-index: 10;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    /* Hero Section Grid */
    .hero-container {
      display: grid;
      grid-template-columns: 1.2fr 0.8fr;
      gap: 60px;
      align-items: center;
      width: 100%;
      min-height: 70vh;
    }

    .hero-text {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      animation: fadeInLeft 0.6s ease-out;
    }

    .hero-badge {
      font-size: 10.5px;
      font-weight: 800;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 2px;
      margin-bottom: 24px;
    }

    .hero-title {
      font-family: var(--font-heading);
      font-size: clamp(36px, 5vw, 56px);
      font-weight: 900;
      line-height: 1.05;
      color: #fff;
      margin-bottom: 24px;
      letter-spacing: -1.5px;
      text-transform: uppercase;
    }

    .hero-title span {
      color: var(--lime);
    }

    .hero-description {
      font-size: 15.5px;
      color: var(--text-secondary);
      line-height: 1.65;
      max-width: 600px;
      margin-bottom: 32px;
    }

    .hero-buttons {
      display: flex;
      gap: 16px;
      margin-bottom: 48px;
    }

    .btn-primary {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--lime);
      color: #060609;
      font-size: 13.5px;
      font-weight: 800;
      text-decoration: none;
      padding: 14px 28px;
      border-radius: 30px;
      transition: all 0.3s ease;
      box-shadow: 0 0 15px rgba(212, 255, 61, 0.2);
    }

    .btn-primary:hover {
      background: #9ccb1f;
      box-shadow: 0 0 25px rgba(212, 255, 61, 0.4);
      transform: translateY(-2px);
    }

    .btn-secondary {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: transparent;
      color: #fff;
      font-size: 13.5px;
      font-weight: 800;
      text-decoration: none;
      padding: 14px 28px;
      border-radius: 30px;
      border: 1px solid var(--border-color);
      transition: all 0.3s ease;
    }

    .btn-secondary:hover {
      background: rgba(255,255,255,0.03);
      border-color: rgba(255,255,255,0.2);
    }

    .hero-stats {
      font-size: 12px;
      color: var(--text-muted);
      letter-spacing: 0.5px;
    }

    .hero-stats span {
      margin: 0 10px;
    }

    /* Right Column Mockup Card */
    .hero-visual {
      display: flex;
      justify-content: center;
      align-items: center;
      animation: fadeInRight 0.6s ease-out;
    }

    .mockup-card {
      background: var(--bg-surface);
      border: 1px solid var(--border-color);
      border-radius: 28px;
      padding: 24px;
      width: 100%;
      max-width: 440px;
      box-shadow: none;
      display: flex;
      flex-direction: column;
      gap: 16px;
      position: relative;
    }

    .mockup-badge {
      display: inline-flex;
      align-self: flex-start;
      background: rgba(212, 255, 61, 0.08);
      border: 1px solid rgba(212, 255, 61, 0.2);
      color: var(--lime);
      font-size: 10px;
      font-weight: 800;
      padding: 4px 12px;
      border-radius: 30px;
      letter-spacing: 1px;
      text-transform: uppercase;
    }

    .mockup-image {
      width: 100%;
      aspect-ratio: 4 / 4.8;
      object-fit: cover;
      border-radius: 18px;
      border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .mockup-tech {
      background: rgba(0, 0, 0, 0.4);
      border-radius: 12px;
      padding: 10px 14px;
      font-size: 10px;
      font-weight: 800;
      color: var(--text-secondary);
      letter-spacing: 1.5px;
      text-align: center;
      text-transform: uppercase;
    }

    /* Footer styling */
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

    footer a {
      color: var(--text-secondary);
      text-decoration: none;
      margin-left: 14px;
      transition: color 0.2s;
    }

    footer a:hover {
      color: var(--lime);
    }

    /* Keyframes Animations */
    @keyframes fadeInLeft {
      from {
        opacity: 0;
        transform: translateX(-40px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    @keyframes fadeInRight {
      from {
        opacity: 0;
        transform: translateX(40px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    /* Responsive adjustments */
    @media (min-width: 992px) {
      body {
        height: 100vh;
        overflow: hidden;
      }
      main {
        padding: 20px 20px 80px 20px;
      }
      footer {
        position: absolute;
        bottom: 0;
        left: 0;
      }
    }

    @media (max-width: 992px) {
      .hero-container {
        grid-template-columns: 1fr;
        gap: 40px;
      }
      header {
        padding: 20px 16px;
      }
      nav {
        gap: 16px;
      }
      nav a {
        display: none;
      }
      nav a.nav-cta {
        display: inline-flex;
      }
      .hero-text {
        align-items: center;
        text-align: center;
      }
      .hero-description {
        margin-left: auto;
        margin-right: auto;
      }
      .hero-buttons {
        justify-content: center;
      }
    }
  </style>
</head>
<body>

  <div class="glow-purple"></div>
  <div class="glow-lime"></div>

  <header>
    <a href="index.php" class="brand">
      <img src="teh_logo.png" alt="THE EXPERT HUB Logo" style="height: 38px; width: auto;">
      <span class="brand-name">THE EXPERT HUB</span>
    </a>
    <nav>
      <a href="yt_login.php">CREATOR PORTAL</a>
      <a href="api_link.php">DEVELOPER HUB</a>
      <a href="login.php">STAFF PORTAL</a>
      <a href="yt_login.php" class="nav-cta">START AUTOMATION ➔</a>
    </nav>
  </header>

  <main>
    <section class="hero-container">
      <!-- Left Info Area -->
      <div class="hero-text">
        <div class="hero-badge">• WHATSAPP . 2FA . GATEWAY . AUTOMATION . EST. 2026</div>
        <h1 class="hero-title">
          WE INTEGRATE<br>
          <span>CHANNELS &amp; APIS,</span><br>
          NOT<br>
          JUST THE / CHATS.
        </h1>
        <p class="hero-description">
          2FA GATEWAY is a premium WhatsApp automation engine. We power high-performance message delivery pipelines for content creators, secure client verification OTP APIs, and intelligent auto-responder chatbot systems.
        </p>
        <div class="hero-buttons">
          <a href="yt_login.php" class="btn-primary">CREATOR PORTAL ➔</a>
          <a href="api_link.php" class="btn-secondary">DEVELOPER ACCESS</a>
        </div>
        <div class="hero-shadow-line"></div>
        <div class="hero-stats">
          350M+ messages sent <span>•</span> 99.99% gateway uptime <span>•</span> 0.1s average latency
        </div>
      </div>

      <!-- Right Visual Card -->
      <div class="hero-visual">
        <div class="mockup-card">
          <div class="mockup-badge">V2.4 ATRIUM • SAAS ENGINE</div>
           <video class="mockup-image" autoplay loop muted playsinline>
             <source src="ui_design/assets/img/WHATSAPP.MP4" type="video/mp4">
             Your browser does not support the video tag.
           </video>
          <div class="mockup-tech">NODE.JS • BAILEYS • SQLITE • cPANEL</div>
        </div>
      </div>
    </section>
  </main>

  <footer>
    <div>
      <span>© 2026 THE EXPERT HUB. All rights reserved.</span>
      <a href="terms.php">Terms</a>
      <a href="privacy.php">Privacy Policy</a>
    </div>
  </footer>

</body>
</html>
