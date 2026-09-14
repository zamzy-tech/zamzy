<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ZAMZY CRM — Intelligent Client Relationship Management Platform</title>
  <meta name="description" content="ZAMZY CRM is a powerful, AI-assisted client relationship management system built for modern digital agencies. Manage leads, invoices, projects, and teams — all in one place." />
  <meta name="robots" content="index, follow" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet" />
  <style>
    :root {
      --bg:#060612;--surface:#0d0d1f;--card:rgba(255,255,255,0.04);--border:rgba(255,255,255,0.08);
      --cyan:#00ffcc;--purple:#9d4edd;--neon-purple:#c77dff;--gold:#ffbe0b;--green:#10b981;
      --text:#e2e8f0;--dim:#64748b;--faint:#334155;
      --display:"Space Grotesk",sans-serif;--mono:"JetBrains Mono",monospace;
    }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    html{scroll-behavior:smooth;}
    body{font-family:var(--display);background:var(--bg);color:var(--text);min-height:100vh;overflow-x:hidden;}
    .aura{position:fixed;inset:0;pointer-events:none;z-index:0;
      background:radial-gradient(ellipse 80vw 60vh at 20% 20%,rgba(157,78,221,0.12) 0%,transparent 65%),
      radial-gradient(ellipse 70vw 50vh at 85% 80%,rgba(0,255,204,0.08) 0%,transparent 65%);}
    /* NAV */
    .crm-nav{position:fixed;top:0;left:0;right:0;z-index:100;display:flex;align-items:center;justify-content:space-between;
      padding:1rem 5vw;background:rgba(6,6,18,0.9);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);}
    .crm-nav__logo{display:flex;align-items:center;gap:0.75rem;text-decoration:none;}
    .crm-nav__logo img{height:36px;width:auto;}
    .crm-nav__badge{font-family:var(--mono);font-size:0.65rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;
      color:var(--cyan);background:rgba(0,255,204,0.1);border:1px solid rgba(0,255,204,0.3);padding:2px 8px;border-radius:4px;}
    .crm-nav__actions{display:flex;align-items:center;gap:1rem;}
    .btn-nav-login{font-family:var(--mono);font-size:0.82rem;font-weight:700;color:var(--cyan);
      background:rgba(0,255,204,0.1);border:1px solid rgba(0,255,204,0.4);padding:0.5rem 1.2rem;
      border-radius:8px;text-decoration:none;transition:all 0.2s ease;}
    .btn-nav-login:hover{background:rgba(0,255,204,0.2);border-color:var(--cyan);box-shadow:0 0 20px rgba(0,255,204,0.2);}
    .btn-nav-back{font-family:var(--mono);font-size:0.82rem;color:var(--dim);text-decoration:none;transition:color 0.2s;}
    .btn-nav-back:hover{color:var(--text);}
    /* HERO */
    .crm-hero{min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;
      padding:120px 5vw 80px;position:relative;z-index:1;}
    .crm-hero__inner{max-width:860px;margin:0 auto;}
    .hero-pill{display:inline-flex;align-items:center;gap:0.5rem;background:rgba(157,78,221,0.12);
      border:1px solid rgba(157,78,221,0.35);color:var(--neon-purple);font-family:var(--mono);font-size:0.72rem;
      font-weight:700;letter-spacing:0.15em;text-transform:uppercase;padding:0.4rem 1rem;border-radius:999px;
      margin-bottom:1.8rem;box-shadow:0 0 24px rgba(157,78,221,0.2);}
    .pulse{width:7px;height:7px;border-radius:50%;background:var(--neon-purple);animation:pls 1.6s infinite;}
    @keyframes pls{0%,100%{opacity:1;transform:scale(1);}50%{opacity:0.4;transform:scale(0.7);}}
    .crm-hero__headline{font-family:var(--display);font-size:clamp(2.5rem,6vw,4.5rem);font-weight:800;
      line-height:1.1;color:#fff;margin-bottom:1.2rem;}
    .grad{background:linear-gradient(135deg,#c77dff 0%,#00ffcc 100%);-webkit-background-clip:text;
      -webkit-text-fill-color:transparent;background-clip:text;}
    .crm-hero__sub{font-family:var(--mono);font-size:1rem;color:var(--dim);line-height:1.7;
      max-width:600px;margin:0 auto 2.5rem;}
    .crm-hero__actions{display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;margin-bottom:3rem;}
    .btn-primary-crm{display:inline-flex;align-items:center;gap:0.5rem;
      background:linear-gradient(135deg,#9d4edd 0%,#6366f1 100%);color:#fff;font-family:var(--display);
      font-size:1rem;font-weight:700;padding:0.9rem 2rem;border-radius:12px;text-decoration:none;
      border:none;cursor:pointer;transition:all 0.3s ease;box-shadow:0 8px 30px rgba(157,78,221,0.35);}
    .btn-primary-crm:hover{transform:translateY(-2px);box-shadow:0 12px 40px rgba(157,78,221,0.5);}
    .btn-outline-crm{display:inline-flex;align-items:center;gap:0.5rem;background:transparent;color:var(--text);
      font-family:var(--display);font-size:1rem;font-weight:600;padding:0.9rem 2rem;border-radius:12px;
      text-decoration:none;border:1px solid var(--border);cursor:pointer;transition:all 0.3s ease;}
    .btn-outline-crm:hover{border-color:rgba(255,255,255,0.2);background:rgba(255,255,255,0.05);}
    /* STATS */
    .stats-row{display:flex;gap:2.5rem;justify-content:center;flex-wrap:wrap;padding:1.5rem 2rem;
      background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:16px;
      max-width:620px;margin:0 auto;}
    .stat-item{text-align:center;}
    .stat-item__num{font-family:var(--display);font-size:1.8rem;font-weight:800;color:var(--cyan);line-height:1;}
    .stat-item__label{font-family:var(--mono);font-size:0.7rem;color:var(--dim);text-transform:uppercase;letter-spacing:0.1em;margin-top:4px;}
    /* FEATURES */
    .features-section{padding:80px 5vw;position:relative;z-index:1;max-width:1200px;margin:0 auto;}
    .section-label{font-family:var(--mono);font-size:0.72rem;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;
      color:var(--cyan);text-align:center;margin-bottom:0.8rem;}
    .section-title{font-family:var(--display);font-size:clamp(1.8rem,3.5vw,2.8rem);font-weight:800;color:#fff;
      text-align:center;margin-bottom:0.6rem;}
    .section-sub{font-family:var(--mono);font-size:0.9rem;color:var(--dim);text-align:center;margin-bottom:3.5rem;}
    .features-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.5rem;}
    .feature-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:1.8rem;
      transition:all 0.3s ease;position:relative;overflow:hidden;}
    .feature-card::before{content:"";position:absolute;top:0;left:0;right:0;height:2px;opacity:0;transition:opacity 0.3s;}
    .feature-card:hover{border-color:rgba(157,78,221,0.3);background:rgba(157,78,221,0.05);transform:translateY(-4px);}
    .feature-card:hover::before{opacity:1;background:linear-gradient(90deg,var(--purple),var(--cyan));}
    .feature-icon{font-size:2rem;margin-bottom:1rem;}
    .feature-title{font-family:var(--display);font-size:1.1rem;font-weight:700;color:#fff;margin-bottom:0.5rem;}
    .feature-desc{font-family:var(--mono);font-size:0.8rem;color:var(--dim);line-height:1.65;}
    /* LOGIN */
    .login-section{padding:80px 5vw;position:relative;z-index:1;display:flex;justify-content:center;}
    .login-card{background:rgba(255,255,255,0.04);border:1px solid rgba(157,78,221,0.25);border-radius:24px;
      padding:3rem;max-width:480px;width:100%;text-align:center;
      box-shadow:0 30px 80px rgba(0,0,0,0.5),0 0 60px rgba(157,78,221,0.08);}
    .login-card img{height:52px;width:auto;margin-bottom:1.5rem;}
    .login-card h2{font-family:var(--display);font-size:1.6rem;font-weight:800;color:#fff;margin-bottom:0.4rem;}
    .login-card p.sub{font-family:var(--mono);font-size:0.78rem;color:var(--dim);margin-bottom:2rem;}
    .login-access-btn{display:block;width:100%;background:linear-gradient(135deg,#9d4edd 0%,#6366f1 100%);
      color:#fff;font-family:var(--display);font-size:1.05rem;font-weight:700;padding:1rem;border-radius:12px;
      text-decoration:none;text-align:center;transition:all 0.3s ease;box-shadow:0 8px 30px rgba(157,78,221,0.35);margin-bottom:1rem;}
    .login-access-btn:hover{transform:translateY(-2px);box-shadow:0 12px 40px rgba(157,78,221,0.5);}
    .secure-note{font-family:var(--mono);font-size:0.72rem;color:var(--faint);}
    .divider{margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid rgba(255,255,255,0.07);}
    .divider p{font-family:var(--mono);font-size:0.75rem;color:var(--dim);}
    .divider a{color:var(--cyan);}
    /* FOOTER */
    .crm-footer{text-align:center;padding:2.5rem 5vw;border-top:1px solid var(--border);position:relative;z-index:1;}
    .crm-footer__links{display:flex;gap:1.5rem;justify-content:center;flex-wrap:wrap;margin-bottom:1rem;}
    .crm-footer a{color:var(--dim);text-decoration:none;font-family:var(--mono);font-size:0.78rem;transition:color 0.2s;}
    .crm-footer a:hover{color:var(--cyan);}
    .crm-footer__copy{font-family:var(--mono);font-size:0.72rem;color:var(--faint);}
    @media(max-width:600px){.crm-hero__actions{flex-direction:column;align-items:center;}.login-card{padding:2rem 1.5rem;}}
  </style>
</head>
<body>
  <div class="aura"></div>
  <!-- NAV -->
  <nav class="crm-nav">
    <a href="https://zamzy.in" class="crm-nav__logo">
      <img src="../images/logo.png" alt="ZAMZY" />
      <span class="crm-nav__badge">CRM</span>
    </a>
    <div class="crm-nav__actions">
      <a href="https://zamzy.in" class="btn-nav-back">&#8592; Back to zamzy.in</a>
      <a href="https://zamzy.in/crm/" class="btn-nav-login">Login to CRM &#8594;</a>
    </div>
  </nav>
  <!-- HERO -->
  <section class="crm-hero">
    <div class="crm-hero__inner">
      <div class="hero-pill">
        <span class="pulse"></span>
        ZAMZY &#xB7; Client Relationship Management System
      </div>
      <h1 class="crm-hero__headline">
        Manage Every Client,<br />
        <span class="grad">Project &amp; Invoice</span><br />
        In One Place.
      </h1>
      <p class="crm-hero__sub">
        ZAMZY CRM is a powerful, agency-built platform to track leads, run projects, 
        generate invoices, and keep your entire team in sync &#x2014; without the complexity.
      </p>
      <div class="crm-hero__actions">
        <a href="https://zamzy.in/crm/" class="btn-primary-crm">&#x1F680; Access CRM Dashboard</a>
        <a href="https://wa.me/917287060553?text=Hello%20ZAMZY!%20I%20want%20to%20know%20more%20about%20the%20CRM%20platform." target="_blank" rel="noopener" class="btn-outline-crm">&#x1F4AC; Request a Demo</a>
      </div>
      <div class="stats-row">
        <div class="stat-item"><div class="stat-item__num">500+</div><div class="stat-item__label">Clients Managed</div></div>
        <div class="stat-item"><div class="stat-item__num">99.9%</div><div class="stat-item__label">Uptime SLA</div></div>
        <div class="stat-item"><div class="stat-item__num">15+</div><div class="stat-item__label">Modules</div></div>
        <div class="stat-item"><div class="stat-item__num">24/7</div><div class="stat-item__label">Support</div></div>
      </div>
    </div>
  </section>
  <!-- FEATURES -->
  <section class="features-section">
    <div class="section-label">&#x26A1; Platform Capabilities</div>
    <h2 class="section-title">Everything Your Agency Needs</h2>
    <p class="section-sub">A complete operating system for client-facing digital teams</p>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">&#x1F3AF;</div>
        <div class="feature-title">Lead &amp; Pipeline Management</div>
        <div class="feature-desc">Track every lead from first contact to signed contract. Kanban views, status tracking, and automated follow-up reminders.</div>
      </div>
      <div class="feature-card">
        <div class="feature-icon">&#x1F4CB;</div>
        <div class="feature-title">Project &amp; Task Tracking</div>
        <div class="feature-desc">Milestone-based project management with task assignments, deadlines, and real-time progress visibility for your team.</div>
      </div>
      <div class="feature-card">
        <div class="feature-icon">&#x1F9FE;</div>
        <div class="feature-title">Invoicing &amp; Payments</div>
        <div class="feature-desc">Generate professional invoices, track payment status, send automated payment reminders, and reconcile accounts.</div>
      </div>
      <div class="feature-card">
        <div class="feature-icon">&#x1F465;</div>
        <div class="feature-title">Team &amp; Role Management</div>
        <div class="feature-desc">Granular role-based access control. Admin, Manager, Staff each with precise permission sets for secure operations.</div>
      </div>
      <div class="feature-card">
        <div class="feature-icon">&#x1F4CA;</div>
        <div class="feature-title">Analytics &amp; Reporting</div>
        <div class="feature-desc">Revenue dashboards, client retention metrics, project completion rates, and custom report generation at a glance.</div>
      </div>
      <div class="feature-card">
        <div class="feature-icon">&#x1F916;</div>
        <div class="feature-title">WhatsApp &amp; Email Automation</div>
        <div class="feature-desc">Automated client communications via WhatsApp API and Email triggers for onboarding, updates, and payment confirmations.</div>
      </div>
    </div>
  </section>
  <!-- LOGIN -->
  <section class="login-section" id="login">
    <div class="login-card">
      <img src="../images/logo.png" alt="ZAMZY" />
      <h2>Access Your Dashboard</h2>
      <p class="sub">Authorized ZAMZY team members and clients only.</p>
      <a href="https://zamzy.in/crm/" class="login-access-btn">&#x1F510; Login to ZAMZY CRM &#x2192;</a>
      <p class="secure-note">&#x1F512; 256-bit SSL Encrypted &#xB7; Session Protected &#xB7; Role-Based Access</p>
      <div class="divider">
        <p>Need access? Contact <a href="https://wa.me/917287060553" target="_blank">+91 72870 60553</a> or <a href="mailto:contact@zamzy.in">contact@zamzy.in</a></p>
      </div>
    </div>
  </section>
  <!-- FOOTER -->
  <footer class="crm-footer">
    <div class="crm-footer__links">
      <a href="https://zamzy.in">ZAMZY Home</a>
      <a href="https://zamzy.in/privacy-policy.php">Privacy Policy</a>
      <a href="https://zamzy.in/terms-and-conditions.php">Terms</a>
      <a href="https://zamzy.in/contact.php">Contact</a>
      <a href="https://zamzy.in/fullstack-webinar">Webinar</a>
    </div>
    <p class="crm-footer__copy">&#xA9; 2026 ZAMZY Digital Engineering Agency &#xB7; Hitech City, Hyderabad, India</p>
  </footer>
</body>
</html>
