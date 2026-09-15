<?php
require_once __DIR__ . '/db.php';
$pdo = getDbConnection();

$jobs = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM `zamzy_careers_jobs` WHERE `is_active` = 1 ORDER BY `id` DESC");
        $jobs = $stmt->fetchAll();
    } catch (Exception $e) {
        $jobs = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ZAMZY Careers &amp; Freelance Developer Guild | Earn Commissions &amp; Build Real SaaS</title>
  <meta name="description" content="Join ZAMZY's Freelance Developer Network &amp; Student Engineering Guild. Build production apps, Flutter systems, and enterprise software for milestone commissions in Chennai &amp; Remote." />
  <meta name="keywords" content="Freelance Developer Network Chennai, Student Freelance Coding, Flutter Developer Jobs Anna Nagar, Next.js Freelance, ZAMZY Careers, Zamzy.in, College Developer Community" />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="https://zamzy.in/careers" />

  <!-- Meta Pixel Code -->
  <script>
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', '1063958663209939');
  fbq('track', 'PageView');
  </script>
  <noscript><img height="1" width="1" style="display:none"
  src="https://www.facebook.com/tr?id=1063958663209939&ev=PageView&noscript=1"
  /></noscript>
  <!-- End Meta Pixel Code -->

  <!-- Open Graph -->
  <meta property="og:type" content="website" />
  <meta property="og:url" content="https://zamzy.in/careers" />
  <meta property="og:title" content="Join the ZAMZY Developer Guild — Freelancers &amp; Students Welcome" />
  <meta property="og:description" content="Build live software projects, earn milestone commissions, and work on production enterprise stacks with ZAMZY." />
  <meta property="og:image" content="https://zamzy.in/images/logo.png" />

  <!-- Fonts & Core Styles -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&family=Barlow+Condensed:wght@400;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="tooplate-vora-bold-style.css" />

  <style>
    .careers-hero {
      padding: 130px 5vw 60px 5vw;
      text-align: center;
      position: relative;
      z-index: 2;
    }
    .careers-hero__eyebrow {
      font-family: var(--mono);
      font-size: 0.75rem;
      letter-spacing: 0.25em;
      color: var(--cyan);
      text-transform: uppercase;
      margin-bottom: 1rem;
    }
    .careers-hero__title {
      font-family: var(--display);
      font-size: clamp(2.2rem, 5vw, 3.8rem);
      font-weight: 700;
      line-height: 1.12;
      color: var(--white);
      max-width: 900px;
      margin: 0 auto 1.4rem auto;
    }
    .careers-hero__desc {
      font-family: var(--mono);
      font-size: clamp(0.85rem, 1.3vw, 1rem);
      color: var(--dim);
      max-width: 680px;
      margin: 0 auto 2.2rem auto;
      line-height: 1.7;
    }
    .guild-perks-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 1.5rem;
      padding: 0 5vw 70px 5vw;
      position: relative;
      z-index: 2;
    }
    .guild-perk-card {
      border: 1px solid rgba(255, 255, 255, 0.08);
      background: rgba(255, 255, 255, 0.02);
      backdrop-filter: blur(12px);
      border-radius: 12px;
      padding: 1.8rem 1.6rem;
      display: flex;
      flex-direction: column;
      gap: 0.8rem;
      transition: var(--transition);
    }
    .guild-perk-card:hover {
      transform: translateY(-4px);
      border-color: rgba(6, 182, 212, 0.45);
      box-shadow: 0 0 24px rgba(6, 182, 212, 0.2);
    }
    .guild-perk-num {
      font-family: var(--mono);
      font-size: 0.72rem;
      color: var(--cyan);
      font-weight: 700;
      letter-spacing: 0.15em;
    }
    .guild-perk-title {
      font-family: var(--display);
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--white);
    }
    .guild-perk-desc {
      font-family: var(--mono);
      font-size: 0.76rem;
      line-height: 1.65;
      color: var(--dim);
    }
    .jobs-section {
      padding: 70px 5vw;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      position: relative;
      z-index: 2;
    }
    .job-card {
      border: 1px solid rgba(255, 255, 255, 0.08);
      background: rgba(255, 255, 255, 0.02);
      backdrop-filter: blur(12px);
      border-radius: 12px;
      padding: 2.2rem 1.8rem;
      margin-bottom: 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 2rem;
      flex-wrap: wrap;
      transition: var(--transition);
    }
    .job-card:hover {
      border-color: rgba(6, 182, 212, 0.45);
      box-shadow: 0 0 25px rgba(6, 182, 212, 0.16);
    }
    .job-card__left {
      flex: 1;
      min-width: 280px;
    }
    .job-card__dept {
      font-family: var(--mono);
      font-size: 0.68rem;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: var(--cyan);
      font-weight: 700;
      margin-bottom: 0.4rem;
    }
    .job-card__title {
      font-family: var(--display);
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--white);
      margin-bottom: 0.6rem;
    }
    .job-card__meta {
      display: flex;
      gap: 0.8rem;
      flex-wrap: wrap;
      margin-bottom: 1rem;
    }
    .job-card__desc {
      font-family: var(--mono);
      font-size: 0.78rem;
      line-height: 1.7;
      color: var(--dim);
      margin-bottom: 0.8rem;
    }
    .job-card__reqs {
      font-family: var(--mono);
      font-size: 0.72rem;
      color: var(--faint);
    }
    .job-card__right {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 1rem;
      min-width: 200px;
    }
    .job-card__payout {
      font-family: var(--mono);
      font-size: 0.88rem;
      font-weight: 700;
      color: var(--cyan);
      text-align: right;
    }
    .apply-section {
      padding: 80px 5vw;
      position: relative;
      z-index: 2;
    }
  </style>
</head>
<body class="visuals-on">

  <!-- Ambient Nebula Aura -->
  <div class="aura"></div>

  <!-- Navigation Bar -->
  <nav class="nav">
    <a href="index.html#hero" class="brand-logo-wrap">
      <img src="images/logo.png" alt="ZAMZY" class="brand-logo-img" />
    </a>
    <ul class="nav-links">
      <li><a href="index.html#about">Studio</a></li>
      <li><a href="index.html#launchpad">Launchpad</a></li>
      <li><a href="index.html#products">Products</a></li>
      <li><a href="index.html#services">Services</a></li>
      <li class="nav-dropdown">
        <a href="fullstack-webinar" style="color:var(--brand-neon-purple); font-weight:700;">
          Events <span style="font-size:8px; opacity:0.8;">▼</span>
        </a>
        <ul class="nav-dropdown-menu">
          <li>
            <a href="fullstack-webinar" class="nav-dropdown-item">
              <span class="nav-dropdown-item-title">🚀 Full Stack Live Webinar <span style="font-size:9px; background:#a855f7; color:#fff; padding:2px 6px; border-radius:10px; font-weight:800;">LIVE</span></span>
              <span class="nav-dropdown-item-desc">From Scratch to Cloud Deployment · ₹96</span>
            </a>
          </li>
        </ul>
      </li>
      <li><a href="index.html#testimonials">Reviews</a></li>
      <li><a href="#apply-guild" class="active" style="color:var(--cyan);">Careers &amp; Guild</a></li>
      <li><a href="contact.php">Contact</a></li>
    </ul>
    <button class="menu-toggle" aria-label="Open menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </nav>

  <!-- Mobile Drawer Menu -->
  <div class="mobile-menu">
    <a href="index.html#hero">Hero</a>
    <a href="index.html#about">Studio</a>
    <a href="index.html#launchpad">Launchpad</a>
    <a href="index.html#products">Products</a>
    <a href="index.html#services">Services</a>
    <a href="fullstack-webinar" style="color:#a855f7; font-weight:700;">🎟️ Events — Full Stack Webinar (₹96)</a>
    <a href="index.html#testimonials">Reviews</a>
    <a href="index.html#rates">Rates</a>
    <a href="#apply-guild" style="color:var(--cyan);">Careers &amp; Guild</a>
    <a href="contact.php">Contact</a>
  </div>

  <!-- ═══════════════════════════════════════════════
       CAREERS HERO SECTION
  ═══════════════════════════════════════════════ -->
  <header class="careers-hero">
    <p class="careers-hero__eyebrow">§ TALENT NETWORK · FREELANCE GUILD · COMMISSIONS</p>
    <h1 class="careers-hero__title">Build Production Software. Earn Milestone Commissions.</h1>
    <p class="careers-hero__desc">
      Join the ZAMZY Developer Guild. Open to college students, freelance coders, UI/UX designers, and campus networkers across Chennai and remote. We match you with paid client projects and high revenue commissions.
    </p>
    <!-- Trust Badges / Capability Pills (Like Reference) -->
    <div class="hero-pills-row" style="margin-bottom:1.5rem;">
      <span class="hero-trust-pill"><span class="pill-icon">🛡</span> 10–25% Deal Commissions</span>
      <span class="hero-trust-pill"><span class="pill-icon">⚡</span> Direct Milestone Payouts</span>
      <span class="hero-trust-pill"><span class="pill-icon">💻</span> Production Enterprise Repos</span>
      <span class="hero-trust-pill"><span class="pill-icon">📈</span> Senior Architect Code Reviews</span>
    </div>

    <div class="hero-cta-group" style="margin-top:0;">
      <a href="#open-roles" class="hero-cta-btn hero-cta-btn--services">
        <span>🚀 View Active Projects &amp; Sprints ↓</span>
      </a>
      <a href="#apply-guild" class="hero-cta-btn hero-cta-btn--talk">
        <span>💼 Apply for Guild &amp; Upload Resume ↓</span>
      </a>
    </div>
  </header>

  <!-- ═══════════════════════════════════════════════
       GUILD PERKS & BENEFITS
  ═══════════════════════════════════════════════ -->
  <div class="guild-perks-grid">
    <div class="guild-perk-card">
      <span class="guild-perk-num">01 / COMMISSIONS</span>
      <h3 class="guild-perk-title">Direct Milestone Payouts</h3>
      <p class="guild-perk-desc">Earn ₹10,000 to ₹60,000+ per module or 10%–25% deal commissions for client acquisition. Zero delayed billing.</p>
    </div>

    <div class="guild-perk-card">
      <span class="guild-perk-num">02 / REAL EXPERIENCE</span>
      <h3 class="guild-perk-title">Enterprise SaaS Repos</h3>
      <p class="guild-perk-desc">Contribute to real production codebases: Flutter mobile apps, Next.js client portals, ERP databases, and IVR gateways.</p>
    </div>

    <div class="guild-perk-card">
      <span class="guild-perk-num">03 / ARCHITECTURE MENTORSHIP</span>
      <h3 class="guild-perk-title">Senior Code Reviews</h3>
      <p class="guild-perk-desc">Learn scalable architecture, clean code standards, database concurrency, and CI/CD pipelines directly from our core team.</p>
    </div>

    <div class="guild-perk-card">
      <span class="guild-perk-num">04 / FLEXIBLE SCHEDULE</span>
      <h3 class="guild-perk-title">College &amp; Remote Friendly</h3>
      <p class="guild-perk-desc">Take on assignments according to your semester schedule (10 to 25 hrs/week). Work from anywhere or our engineering hub.</p>
    </div>
  </div>

  <!-- ═══════════════════════════════════════════════
       CURRENT ACTIVE OPENINGS & CLIENT PROJECTS
  ═══════════════════════════════════════════════ -->
  <section class="jobs-section" id="open-roles">
    <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:2.5rem; flex-wrap:wrap; gap:1.2rem;">
      <div>
        <span class="badge">Open Roles &amp; Active Client Sprints</span>
        <h2 class="section-title" style="margin-top:0.6rem;">Current Engineering Openings</h2>
      </div>
      <p style="font-family:var(--mono); font-size:0.78rem; color:var(--dim); max-width:44ch;">
        Apply directly for an active client project sprint below or submit your talent profile &amp; resume to join the freelance network.
      </p>
    </div>

    <?php if (empty($jobs)): ?>
      <div class="job-card" style="justify-content:center; text-align:center; padding:3rem;">
        <p style="font-family:var(--mono); color:var(--dim);">All core roles are currently matched. Submit your talent profile below to get notified of new client sprints!</p>
      </div>
    <?php else: ?>
      <?php foreach ($jobs as $job): ?>
        <div class="job-card">
          <div class="job-card__left">
            <span class="job-card__dept"><?= htmlspecialchars($job['department']) ?></span>
            <h3 class="job-card__title"><?= htmlspecialchars($job['title']) ?></h3>
            <div class="job-card__meta">
              <span class="badge"><?= htmlspecialchars($job['employment_type']) ?></span>
              <span class="badge">📍 <?= htmlspecialchars($job['location']) ?></span>
              <span class="badge">🎓 <?= htmlspecialchars($job['experience_level']) ?></span>
            </div>
            <p class="job-card__desc"><?= htmlspecialchars($job['description']) ?></p>
            <p class="job-card__reqs"><strong>Key Requirements:</strong> <?= htmlspecialchars($job['requirements']) ?></p>
          </div>
          <div class="job-card__right">
            <span class="job-card__payout"><?= htmlspecialchars($job['stipend_salary']) ?></span>
            <button type="button" class="btn btn-sm apply-role-btn" 
              data-id="<?= $job['id'] ?>" 
              data-title="<?= htmlspecialchars($job['title'], ENT_QUOTES) ?>">
              Apply For Project →
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>

  <!-- ═══════════════════════════════════════════════
       FREELANCER & STUDENT TALENT INTAKE FORM
  ═══════════════════════════════════════════════ -->
  <section class="apply-section" id="apply-guild">
    <div style="max-width:900px; margin:0 auto;">
      
      <div class="intake-form-wrap" style="padding:3rem 2.5rem; position:relative;">
        <!-- Floating Z Brand Animation -->
        <div class="z-floating-container" aria-hidden="true" style="top:25px; right:30px;">
          <div class="z z-1">Z</div>
          <div class="z z-2">Z</div>
          <div class="z z-3">Z</div>
          <div class="z z-4">Z</div>
        </div>

        <span class="badge">Developer &amp; Creator Intake</span>
        <h2 class="intake-form-title" style="margin-top:0.6rem; font-size:2rem;">Join the ZAMZY Engineering Guild</h2>
        <p class="intake-form-sub" style="margin-bottom:2rem;">
          Tell us your skills, college/location, and upload your resume. We assign you active client modules and disburse project commissions directly to you.
        </p>

        <form id="guild-application-form" enctype="multipart/form-data">
          <input type="hidden" name="job_id" id="app-job-id" value="" />

          <div class="form-grid">
            
            <!-- 1. Full Name -->
            <div class="form-group">
              <label class="form-label" for="app-name">Full Name <span class="req">*</span></label>
              <input type="text" id="app-name" class="form-input" placeholder="e.g. Vignesh Sundaram" required />
            </div>

            <!-- 2. WhatsApp Number -->
            <div class="form-group">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.45rem;">
                <label class="form-label" for="app-phone" style="margin-bottom:0;">WhatsApp Number <span class="req">*</span></label>
                <span id="app-phone-verified-badge" style="display:none; font-family:var(--mono); font-size:0.68rem; color:#10b981; font-weight:700;">✓ Verified</span>
              </div>
              <div style="display:flex; gap:6px;">
                <input type="tel" id="app-phone" class="form-input" placeholder="+91 98765 43210" style="margin-bottom:0; flex:1;" required />
                <button type="button" id="btn-send-app-otp" class="btn btn-sm" style="background:rgba(37,211,102,0.15); border:1px solid #25D366; color:#25D366; font-family:var(--mono); font-size:0.75rem; font-weight:700; padding:0 12px; border-radius:8px; cursor:pointer; white-space:nowrap; transition:all 0.2s ease;">
                  💬 Verify
                </button>
              </div>

              <!-- OTP Verification Row (Hidden until requested) -->
              <div id="app-otp-row" style="display:none; margin-top:8px; padding:10px; background:rgba(37,211,102,0.06); border:1px dashed rgba(37,211,102,0.4); border-radius:8px;">
                <div style="font-size:0.7rem; color:#86efac; font-family:var(--mono); margin-bottom:6px;">
                  📲 Enter 4-digit code sent to your WhatsApp:
                </div>
                <div style="display:flex; gap:6px;">
                  <input type="text" id="app-otp-input" maxlength="6" class="form-input" placeholder="e.g. 1234" style="padding:0.5rem 0.8rem; font-family:var(--mono); letter-spacing:0.15em; font-weight:700; text-align:center; width:120px; margin-bottom:0;" />
                  <button type="button" id="btn-confirm-app-otp" class="btn btn-sm" style="background:#25D366; color:#03230e; font-family:var(--mono); font-weight:800; font-size:0.75rem; padding:0 14px; border-radius:6px; border:none; cursor:pointer;">
                    Confirm OTP
                  </button>
                </div>
                <div id="app-otp-feedback" style="font-family:var(--mono); font-size:0.72rem; margin-top:5px; line-height:1.4;"></div>
              </div>
            </div>

            <!-- 3. Email Address -->
            <div class="form-group">
              <label class="form-label" for="app-email">Email Address <span class="req">*</span></label>
              <input type="email" id="app-email" class="form-input" placeholder="vignesh@gmail.com" required />
            </div>

            <!-- 4. Location / College Name -->
            <div class="form-group">
              <label class="form-label" for="app-location">Location / College Name <span class="req">*</span></label>
              <input type="text" id="app-location" class="form-input" placeholder="e.g. Anna University, Chennai / JNTU, Hyderabad" required />
            </div>

            <!-- 5. Primary Skillset / Target Project -->
            <div class="form-group full-width">
              <label class="form-label" for="app-skills">Target Project / Skillsets <span class="req">*</span></label>
              <input list="skills-suggestions" type="text" id="app-skills" class="form-input" placeholder="e.g. Flutter & Dart, React & Next.js, Node.js, Python AI, Figma UI/UX, Deals" required />
              <datalist id="skills-suggestions">
                <option value="Flutter & Mobile App Engineer">
                <option value="Full-Stack React & Node.js Developer">
                <option value="UI/UX Product Designer">
                <option value="Campus Tech Ambassador & Project Scout">
                <option value="Python, FastAPI & AI Automation">
                <option value="PHP, Laravel & MySQL ERPs">
                <option value="AWS, Docker & DevOps Cloud">
              </datalist>
            </div>

            <!-- 6. Experience Level -->
            <div class="form-group">
              <label class="form-label" for="app-exp">Experience Level</label>
              <input list="exp-list" type="text" id="app-exp" class="form-input" placeholder="e.g. College Student / 2nd Year" value="College Student / Fresher" />
              <datalist id="exp-list">
                <option value="College Student (1st - 4th Year)">
                <option value="Fresh Graduate / Junior Freelancer">
                <option value="Mid-Level Developer (1-3 yrs experience)">
                <option value="Senior Freelance Consultant">
              </datalist>
            </div>

            <!-- 7. Weekly Availability -->
            <div class="form-group">
              <label class="form-label" for="app-avail">Weekly Availability</label>
              <input list="avail-list" type="text" id="app-avail" class="form-input" placeholder="e.g. 15-20 hrs/week" value="15-20 hrs/week" />
              <datalist id="avail-list">
                <option value="10-15 hrs/week (Part-Time / College)">
                <option value="15-25 hrs/week (Flexible Freelance)">
                <option value="30+ hrs/week (Full-Time Project)">
                <option value="Weekends Only">
              </datalist>
            </div>

            <!-- 8. Expected Payout / Commission Model -->
            <div class="form-group">
              <label class="form-label" for="app-payout">Preferred Payout Model</label>
              <input list="payout-list" type="text" id="app-payout" class="form-input" placeholder="e.g. Per Project Milestone" value="Per Project Milestone" />
              <datalist id="payout-list">
                <option value="Per Project Milestone (₹15k - ₹60k)">
                <option value="Per Screen / Hourly UI Rate">
                <option value="Deal Commission (10% - 25% of Project)">
                <option value="Monthly Retainer">
              </datalist>
            </div>

            <!-- 9. Portfolio / GitHub Link -->
            <div class="form-group">
              <label class="form-label" for="app-portfolio">GitHub / Portfolio Link</label>
              <input type="url" id="app-portfolio" class="form-input" placeholder="https://github.com/yourhandle or portfolio URL" />
            </div>

            <!-- 10. Resume / CV Upload (Mandatory) -->
            <div class="form-group">
              <label class="form-label" for="app-resume">Upload Resume / CV <span class="req">* (Mandatory: PDF/DOCX up to 10MB)</span></label>
              <input type="file" id="app-resume" name="resume" class="form-input" accept=".pdf,.doc,.docx" required style="padding:0.6rem; cursor:pointer;" />
            </div>

            <!-- 11. Past Projects / What can you build -->
            <div class="form-group full-width">
              <label class="form-label" for="app-notes">What Have You Built? (Past Projects, Tools, or Ideas)</label>
              <textarea id="app-notes" class="form-textarea" placeholder="Tell us about any apps or projects you built in college, hackathons, freelance work, or what you're excited to code..."></textarea>
            </div>

          </div>

          <p class="form-disclaimer">
            By submitting, your resume and developer profile enter our active project matching pool. You will be contacted directly via WhatsApp when a matching client sprint or paid milestone is ready.
          </p>

          <button type="submit" id="guild-submit-btn" class="btn" style="width:100%; padding:1.2rem;">
            Submit Talent Profile &amp; Resume →
          </button>
        </form>

      </div>

    </div>
  </section>

  <!-- Footer Bar -->
  <footer class="footer">
    <div class="footer-inner">
      <!-- Column 1: Brand & Bio -->
      <div class="footer-brand">
        <div class="footer-logo">
          <img src="images/logo.png" alt="ZAMZY" class="brand-logo-img" />
        </div>
        <p class="footer-tagline">
          ZAMZY.IN — Engineering High-Performance SaaS Platforms, Mobile Ecosystems &amp; Automated Cloud Systems.
        </p>
        <span class="footer-copy">© 2026 ZAMZY Digital Engineering Agency. All rights reserved.</span>
      </div>

      <!-- Column 2: Navigation Links -->
      <div>
        <div class="footer-col-title">Navigation</div>
        <div class="footer-links-col">
          <a href="index.html#hero">Home</a>
          <a href="index.html#about">Studio</a>
          <a href="index.html#launchpad">Launchpad</a>
          <a href="index.html#products">Products</a>
          <a href="index.html#services">Services</a>
          <a href="#apply-guild" style="color:var(--cyan);">Careers &amp; Guild</a>
          <a href="contact.php">Contact Us</a>
        </div>
      </div>

      <!-- Column 3: Merchant Compliance Policies -->
      <div>
        <div class="footer-col-title">Merchant Policies</div>
        <div class="footer-links-col">
          <a href="privacy-policy.php">Privacy Policy</a>
          <a href="terms-and-conditions.php">Terms &amp; Conditions</a>
          <a href="refund-policy.php">Refund &amp; Cancellation</a>
          <a href="shipping-policy.php">Shipping &amp; Delivery</a>
          <a href="contact.php">Contact Support</a>
        </div>
      </div>

      <!-- Column 4: Contact & Office -->
      <div>
        <div class="footer-col-title">Studio Office</div>
        <div class="footer-links-col" style="font-size:0.82rem; color:var(--dim); line-height:1.7;">
          <p><strong>ZAMZY DIGITAL SOLUTIONS</strong></p>
          <p>📍 Hitech City, Hyderabad, Telangana, India</p>
          <p>💬 <a href="https://wa.me/917287060553?text=Hello%20ZAMZY!%20I%20am%20contacting%20from%20your%20official%20website%20(zamzy.in).%20I%20would%20like%20to%20inquire%20about%20your%20software%20and%20digital%20services." target="_blank" style="color:var(--cyan); text-decoration:underline;">+91 72870 60553 (WhatsApp)</a></p>
          <p>✉️ <a href="mailto:contact@zamzy.in" style="color:var(--cyan); text-decoration:underline;">contact@zamzy.in</a></p>
          <div class="social-circle-links">
            <a href="https://www.instagram.com/zamzy.in" target="_blank" rel="noopener" class="social-circle-btn instagram" aria-label="Instagram" title="Follow us on Instagram">
              <svg viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
            </a>
            <a href="https://www.facebook.com/share/1BwUK4uNSr/" target="_blank" rel="noopener" class="social-circle-btn facebook" aria-label="Facebook" title="Follow us on Facebook">
              <svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
            <a href="https://www.linkedin.com/company/zamzy/" target="_blank" rel="noopener" class="social-circle-btn linkedin" aria-label="LinkedIn" title="Connect with us on LinkedIn">
              <svg viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
            </a>
            <a href="https://wa.me/917287060553?text=Hello%20ZAMZY!%20I%20am%20contacting%20from%20your%20official%20website%20(zamzy.in)." target="_blank" rel="noopener" class="social-circle-btn whatsapp" aria-label="WhatsApp" title="Chat on WhatsApp (+91 72870 60553)">
              <svg viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Bar -->
    <div class="footer-bottom-bar">
      <div class="footer-bottom-text">
        ⚡ High-Concurrency Systems · 99.9% Uptime SLA · Hitech City, Hyderabad
      </div>
      <div class="footer-social-links">
        <a href="privacy-policy.php" class="footer-social-link">Privacy Policy</a>
        <a href="terms-and-conditions.php" class="footer-social-link">Terms &amp; Conditions</a>
        <a href="refund-policy.php" class="footer-social-link">Refund Policy</a>
        <a href="shipping-policy.php" class="footer-social-link">Shipping Policy</a>
      </div>
    </div>
  </footer>

  <!-- Toast Notification -->
  <div class="toast-msg" id="toast">
    <span>✓</span>
    <span id="toast-text">Action completed successfully!</span>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    // Mobile menu toggle
    const toggle = document.querySelector('.menu-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');
    if (toggle && mobileMenu) {
      toggle.addEventListener('click', () => {
        const open = mobileMenu.classList.toggle('open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    }

    // Role application auto-fill and jump to form
    document.querySelectorAll('.apply-role-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const jobId = btn.dataset.id;
        const jobTitle = btn.dataset.title;
        const jobIdInput = document.getElementById('app-job-id');
        const jobSkillsInput = document.getElementById('app-skills');
        if (jobIdInput) jobIdInput.value = jobId;
        if (jobSkillsInput) jobSkillsInput.value = jobTitle;
        const applySec = document.getElementById('apply-guild');
        if (applySec) {
          applySec.scrollIntoView({ behavior: 'smooth' });
          showToast(`Selected: ${jobTitle}. Upload your Resume and submit below.`);
        }
      });
    });

    // Auto-scroll to application form if accessed directly or via #apply-guild
    if (window.location.hash === '#apply-guild' || window.location.hash === '#apply' || window.location.search.includes('apply')) {
      const applySec = document.getElementById('apply-guild');
      if (applySec) {
        setTimeout(() => {
          applySec.scrollIntoView({ behavior: 'smooth' });
        }, 150);
      }
    }

    function showToast(message) {
      const toast = document.getElementById('toast');
      const toastText = document.getElementById('toast-text');
      if (toast && toastText) {
        toastText.textContent = message;
        toast.classList.add('show');
        setTimeout(() => {
          toast.classList.remove('show');
        }, 5000);
      }
    }
  });
  </script>

  <!-- ═══ WHATSAPP FLOATING BUTTON (LEFT SIDE) ═══ -->
  <a href="https://wa.me/917287060553?text=Hello%20ZAMZY!%20I%20am%20contacting%20from%20your%20official%20website%20(zamzy.in).%20I%20would%20like%20to%20inquire%20about%20your%20software%20and%20digital%20services."
     id="whatsapp-btn"
     class="whatsapp-float-btn"
     target="_blank"
     rel="noopener noreferrer"
     aria-label="Chat on WhatsApp"
     title="Chat with us on WhatsApp (+91 72870 60553)">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="28" height="28" fill="#ffffff">
      <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
    </svg>
    <span class="whatsapp-tooltip">Chat with us</span>
  </a>

  <!-- Back To Top -->
  <button id="back-to-top" class="back-to-top" aria-label="Back to top" title="Back to top">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
  </button>

  <!-- AI Chatbot -->
  <button id="ai-chat-btn" class="ai-chat-btn" aria-label="Chat with ZAMZY AI" title="Chat with ZAMZY AI">
    <img src="images/ai-chat-icon.jpg" alt="ZAMZY AI" />
    <span class="ai-chat-badge">AI</span>
  </button>
  <div id="ai-chat-modal" class="ai-chat-modal">
    <div class="ai-chat-panel">
      <div class="ai-chat-header">
        <div class="ai-chat-header-info">
          <div class="ai-chat-avatar" style="overflow:hidden; padding:0;">
            <img src="images/ai-chat-icon.jpg" alt="AI" style="width:100%; height:100%; object-fit:cover; border-radius:50%;" />
          </div>
          <div>
            <div class="ai-chat-name">ZAMZY Assistant</div>
            <div class="ai-chat-status"><span class="ai-online-dot"></span> Online</div>
          </div>
        </div>
        <button id="close-ai-chat" class="ai-chat-close">&times;</button>
      </div>
      <div class="ai-chat-messages" id="ai-chat-messages">
        <div class="ai-msg ai-msg--bot"><div class="ai-msg-bubble">👋 Looking to join ZAMZY's Freelance Guild? Ask me anything about our openings, commission rates, or application process!</div></div>
        <div class="ai-quick-btns">
          <button class="ai-quick-btn" data-msg="How do I apply for the Guild?">How to Apply</button>
          <button class="ai-quick-btn" data-msg="What roles are available?">Open Roles</button>
          <button class="ai-quick-btn" data-msg="What is the freelance commission rate?">Commission</button>
        </div>
      </div>
      <form class="ai-chat-input-row" id="ai-chat-form" autocomplete="off">
        <input type="text" id="ai-chat-input" class="ai-chat-input" placeholder="Ask anything..." autocomplete="off" />
        <button type="submit" class="ai-send-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
        </button>
      </form>
    </div>
  </div>

  <script>
  // Back to top
  (function(){
    var b=document.getElementById('back-to-top');
    if(!b) return;
    window.addEventListener('scroll',function(){b.classList.toggle('visible',window.scrollY>400);},{passive:true});
    b.addEventListener('click',function(){window.scrollTo({top:0,behavior:'smooth'});});
  })();

  // DeepSeek Powered Chatbot with Lead Intake
  (function(){
    var btn=document.getElementById('ai-chat-btn'),modal=document.getElementById('ai-chat-modal'),cls=document.getElementById('close-ai-chat'),form=document.getElementById('ai-chat-form'),inp=document.getElementById('ai-chat-input'),box=document.getElementById('ai-chat-messages');
    if(!btn||!modal||!box) return;

    var chatHistory = [];
    var sessionToken = localStorage.getItem('zamzy_chat_token') || ('zamzy_' + Math.random().toString(36).substring(2, 12) + Date.now().toString(36));
    localStorage.setItem('zamzy_chat_token', sessionToken);

    var savedUser = null;
    try {
      savedUser = JSON.parse(localStorage.getItem('zamzy_chat_user') || 'null');
    } catch(e) { savedUser = null; }

    var leadData = {
      name: (savedUser && savedUser.name) ? savedUser.name : '',
      phone: (savedUser && savedUser.phone) ? savedUser.phone : '',
      email: (savedUser && savedUser.email) ? savedUser.email : ''
    };

    var onboardingStep = (leadData.name && leadData.phone && leadData.email) ? 'ready' : 'ask_name';

    function setPlaceholder() {
      if (!inp) return;
      if (onboardingStep === 'ask_name') {
        inp.placeholder = 'Enter your Full Name...';
      } else if (onboardingStep === 'ask_phone') {
        inp.placeholder = 'Enter your WhatsApp / Phone Number...';
      } else if (onboardingStep === 'ask_email') {
        inp.placeholder = 'Enter your Email Address...';
      } else {
        inp.placeholder = 'Ask anything about freelance roles, payouts...';
      }
    }

    function initChatView() {
      box.innerHTML = '';
      if (onboardingStep === 'ready') {
        add(`👋 Welcome back, <strong>${escapeHtml(leadData.name)}</strong>! Looking to join ZAMZY's Freelance Guild? Ask me anything about our openings, commission payouts, or projects.`, 'bot');
        showQuickButtons();
      } else {
        add(`👋 Hello! Welcome to <strong>ZAMZY Freelance Guild</strong>.<br><br>Before we begin, may I have your <strong>Full Name</strong>?`, 'bot');
      }
      setPlaceholder();
    }

    btn.addEventListener('click',function(){
      modal.classList.toggle('open');
      if(modal.classList.contains('open')&&inp) {
        if (box.children.length === 0) initChatView();
        setTimeout(function(){inp.focus();},280);
      }
    });
    if(cls) cls.addEventListener('click',function(){modal.classList.remove('open');});
    document.addEventListener('click',function(e){if(!modal.contains(e.target)&&!btn.contains(e.target))modal.classList.remove('open');});

    function escapeHtml(str) {
      if (!str) return '';
      return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function add(t,type){
      var d=document.createElement('div');
      d.className='ai-msg ai-msg--'+type;
      d.innerHTML='<div class="ai-msg-bubble">'+t+'</div>';
      box.appendChild(d);
      box.scrollTop=box.scrollHeight;
    }
    function typing(){
      var t=document.createElement('div');
      t.className='ai-msg ai-msg--bot';
      t.id='ai-typing';
      t.innerHTML='<div class="ai-typing"><span></span><span></span><span></span></div>';
      box.appendChild(t);
      box.scrollTop=box.scrollHeight;
    }
    function rmTyping(){
      var t=document.getElementById('ai-typing');
      if(t) t.remove();
    }

    function showQuickButtons() {
      var old = box.querySelector('.ai-quick-btns');
      if (old) old.remove();

      var qb = document.createElement('div');
      qb.className = 'ai-quick-btns';
      qb.innerHTML = `
        <button class="ai-quick-btn" data-msg="How do I apply for the Freelance Guild?">How to Apply</button>
        <button class="ai-quick-btn" data-msg="What developer roles are currently open?">Open Roles</button>
        <button class="ai-quick-btn" data-msg="What are the commission payout percentages for projects?">Commission &amp; Payouts</button>
      `;
      box.appendChild(qb);
      box.scrollTop=box.scrollHeight;
    }

    async function handleInput(m){
      var trimmed = m.trim();
      if(!trimmed) return;
      add(escapeHtml(trimmed),'user');
      chatHistory.push({ role: 'user', content: trimmed });

      var qb=box.querySelector('.ai-quick-btns');
      if(qb) qb.remove();

      if (onboardingStep === 'ask_name') {
        leadData.name = trimmed;
        onboardingStep = 'ask_phone';
        setPlaceholder();
        typing();
        setTimeout(function(){
          rmTyping();
          add(`Nice to meet you, <strong>${escapeHtml(leadData.name)}</strong>! 🤝<br><br>What is your <strong>WhatsApp / Phone Number</strong>?`, 'bot');
        }, 600);
        return;
      }

      if (onboardingStep === 'ask_phone') {
        leadData.phone = trimmed;
        onboardingStep = 'ask_email';
        setPlaceholder();
        typing();
        setTimeout(function(){
          rmTyping();
          add(`Great! And what is your <strong>Email Address</strong>?`, 'bot');
        }, 600);
        return;
      }

      if (onboardingStep === 'ask_email') {
        leadData.email = trimmed;
        onboardingStep = 'ready';
        setPlaceholder();
        typing();

        localStorage.setItem('zamzy_chat_user', JSON.stringify(leadData));
        var fd = new FormData();
        fd.append('action', 'ai_chat_save_lead');
        fd.append('session_token', sessionToken);
        fd.append('name', leadData.name);
        fd.append('phone', leadData.phone);
        fd.append('email', leadData.email);
        fetch('api.php', { method: 'POST', body: fd }).catch(function(){});

        setTimeout(function(){
          rmTyping();
          add(`✨ Thank you, <strong>${escapeHtml(leadData.name)}</strong>! Your profile has been verified.<br><br>Ask me anything about our developer guild openings, commissions, or client projects!`, 'bot');
          showQuickButtons();
        }, 800);
        return;
      }

      typing();

      try {
        var fd = new FormData();
        fd.append('action', 'ai_chat');
        fd.append('session_token', sessionToken);
        fd.append('message', trimmed);
        fd.append('user_name', leadData.name);
        fd.append('user_phone', leadData.phone);
        fd.append('user_email', leadData.email);
        fd.append('history', JSON.stringify(chatHistory));

        var res = await fetch('api.php', { method: 'POST', body: fd });
        var data = await res.json();
        rmTyping();
        if(data && data.reply) {
          add(data.reply, 'bot');
          chatHistory.push({ role: 'assistant', content: data.reply });
        } else {
          add("Thanks for reaching out! Please fill the application form above or email hello@zamzy.in.", 'bot');
        }
      } catch(err) {
        rmTyping();
        add("I'm here to help! You can apply using the form above or email us at hello@zamzy.in for any questions.", 'bot');
      }
    }

    if(form){
      form.addEventListener('submit',function(e){
        e.preventDefault();
        var m=inp?inp.value.trim():'';
        if(!m) return;
        inp.value='';
        handleInput(m);
      });
    }
    if(box){
      box.addEventListener('click',function(e){
        var b=e.target.closest('.ai-quick-btn');
        if(b) handleInput(b.getAttribute('data-msg')||b.textContent);
      });
    }

    initChatView();
  })();
  </script>

  <!-- Fixed Mobile Quick-Action Dock -->
  <div class="mobile-action-dock" id="mobile-action-dock" role="navigation" aria-label="Quick Actions">
    <a href="./#services" class="dock-btn dock-btn--services" aria-label="Explore Services">
      <span class="dock-btn__icon">🛠</span>
      <span class="dock-btn__label">Services</span>
    </a>
    <a href="contact.php" class="dock-btn dock-btn--talk" aria-label="Start a Project / Let's Talk">
      <span class="dock-btn__icon">💬</span>
      <span class="dock-btn__label">Let's Talk</span>
    </a>
    <a href="#apply-guild" class="dock-btn dock-btn--careers active" aria-label="Explore Careers & Guild">
      <span class="dock-btn__icon">💼</span>
      <span class="dock-btn__label">Careers</span>
    </a>
  </div>

  <!-- ═══════════════════════════════════════════════
       SUPER THANK YOU POPUP MODAL (AUTO CLOSE)
  ═══════════════════════════════════════════════ -->
  <div class="thankyou-modal" id="thankyou-modal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="thankyou-backdrop" id="close-thankyou-backdrop"></div>
    <div class="thankyou-card">
      <div class="thankyou-icon-wrap">
        <div class="thankyou-icon">✓</div>
      </div>
      <span class="thankyou-badge">⚡ TRANSMISSION RECEIVED</span>
      <h2 class="thankyou-title">SUPER THANK YOU!</h2>
      <p class="thankyou-subtitle">Your Application / Brief is Successfully Logged</p>
      <p class="thankyou-msg" id="thankyou-msg">
        Thank you! Our Lead Architect has received your details and will connect with you via WhatsApp shortly.
      </p>
      <div class="thankyou-timer-wrap">
        <div class="thankyou-timer-bar" id="thankyou-timer-bar"></div>
      </div>
      <div class="thankyou-footer">
        <span class="thankyou-countdown">Closing automatically in <strong id="thankyou-countdown-num">5</strong>s</span>
        <button type="button" class="btn btn-outline" id="close-thankyou-btn" style="padding: 0.5rem 1.2rem; font-size: 0.76rem;">Close Now →</button>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="tooplate-vora-bold-script.js"></script>

</body>
</html>

