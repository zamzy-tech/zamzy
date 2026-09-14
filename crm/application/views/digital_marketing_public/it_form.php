<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Solutions & Digital Services | Get a Free Quote</title>
    <meta name="description" content="World-class IT solutions — websites, mobile apps, custom software, ERP/CRM, cloud & digital marketing. Get a free project quote today.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --dark:    #060b18;
            --dark2:   #0c1527;
            --dark3:   #111f38;
            --blue:    #0070f3;
            --cyan:    #00d4ff;
            --purple:  #7c3aed;
            --green:   #10b981;
            --white:   #ffffff;
            --gray50:  #f0f4ff;
            --gray100: #e2e8f7;
            --gray400: #94a3b8;
            --gray600: #475569;
            --radius:  14px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark);
            color: var(--white);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── ANIMATED BG ─── */
        .bg-orbs {
            position: fixed;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
            z-index: 0;
        }
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.18;
            animation: orbFloat 12s ease-in-out infinite;
        }
        .orb1 { width: 600px; height: 600px; background: var(--blue); top: -150px; right: -100px; animation-delay: 0s; }
        .orb2 { width: 500px; height: 500px; background: var(--purple); bottom: 10%; left: -100px; animation-delay: -4s; }
        .orb3 { width: 400px; height: 400px; background: var(--cyan); top: 40%; left: 40%; animation-delay: -8s; }
        @keyframes orbFloat {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-40px) scale(1.05); }
        }

        /* ─── NAVBAR ─── */
        .navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            background: rgba(6,11,24,0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            padding: 0 40px;
            height: 66px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .navbar .logo {
            display: flex; align-items: center; gap: 10px;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 20px; font-weight: 700; color: var(--white);
        }
        .logo-icon {
            width: 34px; height: 34px;
            background: linear-gradient(135deg, var(--blue), var(--cyan));
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
        }
        .nav-pill {
            background: rgba(0,212,255,0.1);
            border: 1px solid rgba(0,212,255,0.25);
            color: var(--cyan);
            font-size: 12px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 20px;
            letter-spacing: 0.5px;
        }

        /* ─── HERO ─── */
        .hero {
            position: relative; z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 100px 40px 60px;
        }
        .hero-inner {
            max-width: 1160px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 440px;
            gap: 80px;
            align-items: center;
            width: 100%;
        }

        /* Left: Copy */
        .hero-copy { }
        .tag-row {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 28px;
        }
        .tag-pill {
            background: rgba(0,112,243,0.15);
            border: 1px solid rgba(0,112,243,0.35);
            color: #60a5fa;
            font-size: 12px;
            font-weight: 600;
            padding: 5px 14px;
            border-radius: 20px;
            letter-spacing: 0.5px;
        }
        .tag-dot { width: 6px; height: 6px; background: var(--green); border-radius: 50%; animation: pulse 1.6s ease-in-out infinite; }
        @keyframes pulse { 0%,100%{box-shadow:0 0 0 0 rgba(16,185,129,0.5)} 50%{box-shadow:0 0 0 7px rgba(16,185,129,0)} }
        .live-text { font-size: 12px; color: var(--green); font-weight: 600; }

        .hero-copy h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(38px, 4.8vw, 62px);
            font-weight: 700;
            line-height: 1.08;
            margin-bottom: 24px;
            letter-spacing: -1.5px;
        }
        .hero-copy h1 .highlight {
            background: linear-gradient(90deg, var(--blue), var(--cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-copy .sub {
            font-size: 17px;
            color: var(--gray400);
            max-width: 460px;
            margin-bottom: 40px;
            line-height: 1.75;
        }

        /* Service Chips */
        .service-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 40px;
        }
        .sc {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.75);
            font-size: 13px;
            font-weight: 500;
            padding: 7px 14px;
            border-radius: 8px;
            display: flex; align-items: center; gap: 6px;
            transition: all 0.18s;
        }
        .sc:hover { background: rgba(0,112,243,0.15); border-color: rgba(0,112,243,0.4); color: #fff; }

        /* Stats row */
        .stats-row {
            display: flex;
            gap: 32px;
            border-top: 1px solid rgba(255,255,255,0.08);
            padding-top: 32px;
        }
        .stat-item { }
        .stat-item .num {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 28px; font-weight: 700;
            background: linear-gradient(90deg, var(--white), var(--cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .stat-item .lbl { font-size: 13px; color: var(--gray400); margin-top: 2px; }

        /* ─── FORM CARD ─── */
        .form-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 24px;
            padding: 36px 32px;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow: 0 32px 80px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.06);
            position: relative;
            overflow: hidden;
        }
        .form-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--blue), var(--cyan), var(--purple));
        }

        .form-card .card-head { margin-bottom: 26px; }
        .form-card .card-head h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 20px; font-weight: 700;
            color: var(--white);
            margin-bottom: 4px;
        }
        .form-card .card-head p { font-size: 13.5px; color: var(--gray400); }

        /* Service Grid */
        .svc-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 22px;
        }
        .svc-btn {
            border: 1.5px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 11px 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.18s;
            font-size: 12px;
            font-weight: 600;
            color: var(--gray400);
            background: rgba(255,255,255,0.03);
            user-select: none;
        }
        .svc-btn .em { font-size: 18px; display: block; margin-bottom: 4px; }
        .svc-btn:hover { border-color: rgba(0,212,255,0.5); color: var(--cyan); background: rgba(0,212,255,0.06); }
        .svc-btn.active {
            border-color: var(--blue);
            color: #60a5fa;
            background: rgba(0,112,243,0.12);
            box-shadow: 0 0 16px rgba(0,112,243,0.2);
        }

        .fg { margin-bottom: 16px; }
        .fg label {
            display: block;
            font-size: 11.5px;
            font-weight: 600;
            color: var(--gray400);
            margin-bottom: 7px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .fg label .req { color: #f87171; }
        .fg input, .fg select, .fg textarea {
            width: 100%;
            background: rgba(255,255,255,0.06);
            border: 1.5px solid rgba(255,255,255,0.1);
            border-radius: 9px;
            padding: 11px 14px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: var(--white);
            outline: none;
            transition: all 0.18s;
        }
        .fg input::placeholder, .fg textarea::placeholder { color: rgba(255,255,255,0.3); }
        .fg input:focus, .fg select:focus, .fg textarea:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(0,112,243,0.15);
            background: rgba(0,112,243,0.07);
        }
        .fg select option { background: var(--dark2); color: var(--white); }
        .fl { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        .budget-row { position: relative; }
        .budget-row .pfx { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--gray400); font-weight: 600; font-size: 14px; pointer-events: none; }
        .budget-row input { padding-left: 30px; }

        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--blue) 0%, #0051c3 100%);
            color: var(--white);
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Space Grotesk', sans-serif;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 6px 20px rgba(0,112,243,0.4);
            letter-spacing: 0.2px;
            margin-top: 6px;
        }
        .submit-btn:hover { transform: translateY(-1px); box-shadow: 0 10px 28px rgba(0,112,243,0.5); }
        .submit-btn:active { transform: translateY(0); }
        .trust-line {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            margin-top: 14px;
            font-size: 11.5px;
            color: var(--gray400);
        }
        .trust-line .ok { color: var(--green); }

        /* ─── FEATURES ─── */
        .features {
            position: relative; z-index: 1;
            background: rgba(255,255,255,0.02);
            border-top: 1px solid rgba(255,255,255,0.06);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            padding: 64px 40px;
        }
        .features-grid {
            max-width: 1160px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 32px;
        }
        .feat-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 28px 24px;
            transition: border-color 0.2s, transform 0.2s;
        }
        .feat-card:hover { border-color: rgba(0,112,243,0.35); transform: translateY(-3px); }
        .feat-icon { font-size: 28px; margin-bottom: 14px; display: block; }
        .feat-card h4 { font-family: 'Space Grotesk', sans-serif; font-size: 15px; font-weight: 700; color: var(--white); margin-bottom: 8px; }
        .feat-card p  { font-size: 13px; color: var(--gray400); line-height: 1.55; }

        /* ─── PROCESS ─── */
        .process {
            position: relative; z-index: 1;
            padding: 72px 40px;
        }
        .process-inner { max-width: 1000px; margin: 0 auto; }
        .section-head {
            text-align: center;
            margin-bottom: 52px;
        }
        .eyebrow {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--cyan);
            margin-bottom: 10px;
        }
        .section-head h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 34px;
            font-weight: 700;
            color: var(--white);
            letter-spacing: -0.5px;
        }
        .steps-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }
        .step-item {
            text-align: center;
            position: relative;
        }
        .step-item::after {
            content: '→';
            position: absolute;
            top: 20px; right: -12px;
            font-size: 20px;
            color: rgba(255,255,255,0.2);
        }
        .step-item:last-child::after { display: none; }
        .step-circle {
            width: 52px; height: 52px;
            background: linear-gradient(135deg, rgba(0,112,243,0.3), rgba(0,212,255,0.3));
            border: 1.5px solid rgba(0,212,255,0.4);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
            font-weight: 800;
            color: var(--cyan);
            font-family: 'Space Grotesk', sans-serif;
            margin: 0 auto 16px;
        }
        .step-item h4 { font-size: 14px; font-weight: 700; color: var(--white); margin-bottom: 6px; }
        .step-item p  { font-size: 12.5px; color: var(--gray400); }

        /* ─── SUCCESS ─── */
        .success-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--dark);
            padding: 40px 20px;
            position: relative; z-index: 1;
        }
        .success-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 24px;
            padding: 56px 48px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            backdrop-filter: blur(20px);
        }
        .success-card .check {
            width: 80px; height: 80px;
            background: linear-gradient(135deg, var(--green), #059669);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 36px;
            margin: 0 auto 24px;
            box-shadow: 0 10px 30px rgba(16,185,129,0.4);
            animation: popIn 0.5s cubic-bezier(0.34,1.56,0.64,1) both;
        }
        @keyframes popIn { from { transform: scale(0); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .success-card h2 { font-family: 'Space Grotesk', sans-serif; font-size: 26px; font-weight: 700; color: var(--white); margin-bottom: 12px; }
        .success-card p  { font-size: 15px; color: var(--gray400); line-height: 1.7; }
        .success-card .ref {
            display: inline-block;
            background: rgba(0,112,243,0.15);
            border: 1px solid rgba(0,112,243,0.3);
            border-radius: 8px;
            padding: 10px 22px;
            font-size: 13px;
            color: #60a5fa;
            font-weight: 600;
            margin-top: 20px;
            font-family: 'Space Grotesk', sans-serif;
        }

        /* ─── FOOTER ─── */
        .footer {
            position: relative; z-index: 1;
            background: rgba(255,255,255,0.02);
            border-top: 1px solid rgba(255,255,255,0.06);
            color: var(--gray400);
            text-align: center;
            font-size: 12.5px;
            padding: 24px 40px;
        }
        .footer a { color: var(--cyan); text-decoration: none; }

        /* ─── ERROR ─── */
        .err-bar {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13.5px;
            color: #f87171;
            margin-bottom: 20px;
            display: flex; align-items: center; gap: 8px;
        }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 960px) {
            .hero-inner { grid-template-columns: 1fr; gap: 48px; }
            .features-grid { grid-template-columns: repeat(2,1fr); }
            .steps-row { grid-template-columns: repeat(2,1fr); }
            .step-item::after { display: none; }
            .stats-row { gap: 20px; }
        }
        @media (max-width: 540px) {
            .hero { padding: 100px 20px 60px; }
            .navbar { padding: 0 20px; }
            .features, .process { padding: 48px 20px; }
            .form-card { padding: 24px 18px; }
            .svc-grid { grid-template-columns: 1fr 1fr; }
            .fl { grid-template-columns: 1fr; }
            .features-grid { grid-template-columns: 1fr; }
            .steps-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- Ambient Orbs -->
<div class="bg-orbs">
    <div class="orb orb1"></div>
    <div class="orb orb2"></div>
    <div class="orb orb3"></div>
</div>

<?php if ($success): ?>
<!-- ─── SUCCESS STATE ─── -->
<div class="success-page">
    <div class="success-card">
        <div class="check">✓</div>
        <h2>Request Received!</h2>
        <p>Thank you! Your IT project enquiry has been submitted. Our solutions team will contact you within <strong style="color:var(--white);">24 hours</strong> with a customized proposal.</p>
        <div class="ref">🎯 Ref: IT-<?= strtoupper(substr(md5(time()), 0, 8)); ?></div>
        <p style="margin-top:20px; font-size:13px;">Check your email for a confirmation.</p>
    </div>
</div>

<?php else: ?>

<!-- ─── NAVBAR ─── -->
<nav class="navbar">
    <div class="logo">
        <?php if (!empty($crm_logo)): ?>
            <img src="<?= e($crm_logo); ?>" alt="<?= e($crm_name); ?>" style="max-height:36px; max-width:160px; object-fit:contain; filter:brightness(0) invert(1);">
        <?php elseif (!empty($crm_logo_dark)): ?>
            <img src="<?= e($crm_logo_dark); ?>" alt="<?= e($crm_name); ?>" style="max-height:36px; max-width:160px; object-fit:contain;">
        <?php else: ?>
            <div class="logo-icon">⚡</div>
            <span><?= e($crm_name ?: 'TEHubTech'); ?></span>
        <?php endif; ?>
    </div>
    <div class="nav-pill">✦ Free Consultation</div>
</nav>

<!-- ─── HERO ─── -->
<section class="hero">
    <div class="hero-inner">
        <!-- Copy -->
        <div class="hero-copy">
            <div class="tag-row">
                <div class="tag-pill">IT Solutions</div>
                <div class="tag-dot"></div>
                <span class="live-text">Accepting New Projects</span>
            </div>

            <h1>
                Build the Future<br>
                with <span class="highlight">World-Class</span><br>
                IT Solutions
            </h1>

            <p class="sub">
                From cutting-edge websites to enterprise software — we design, build, and scale digital products that put your business ahead of the competition. Globally trusted, locally available.
            </p>

            <div class="service-chips">
                <div class="sc">🌐 Web Development</div>
                <div class="sc">📱 Mobile Apps</div>
                <div class="sc">⚙️ Custom Software</div>
                <div class="sc">📊 ERP & CRM</div>
                <div class="sc">☁️ Cloud Solutions</div>
                <div class="sc">📣 Digital Marketing</div>
            </div>

            <div class="stats-row">
                <div class="stat-item">
                    <div class="num">500+</div>
                    <div class="lbl">Projects Delivered</div>
                </div>
                <div class="stat-item">
                    <div class="num">98%</div>
                    <div class="lbl">Client Satisfaction</div>
                </div>
                <div class="stat-item">
                    <div class="num">8+</div>
                    <div class="lbl">Years Experience</div>
                </div>
            </div>
            <?php if (!empty($crm_phone) || !empty($crm_email)): ?>
            <div style="margin-top:28px; padding-top:24px; border-top:1px solid rgba(255,255,255,0.08);">
                <p style="font-size:11px; text-transform:uppercase; letter-spacing:1.5px; color:var(--gray400); margin-bottom:10px; font-weight:600;">Get in Touch</p>
                <div style="display:flex; flex-wrap:wrap; gap:10px;">
                    <?php if (!empty($crm_phone)): ?>
                    <a href="tel:<?= e($crm_phone); ?>" class="sc" style="text-decoration:none;">📞 <?= e($crm_phone); ?></a>
                    <?php endif; ?>
                    <?php if (!empty($crm_email)): ?>
                    <a href="mailto:<?= e($crm_email); ?>" class="sc" style="text-decoration:none;">✉️ <?= e($crm_email); ?></a>
                    <?php endif; ?>
                    <?php if (!empty($crm_website)): ?>
                    <a href="<?= e($crm_website); ?>" target="_blank" class="sc" style="text-decoration:none;">🌐 <?= e($crm_website); ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Form Card -->
        <div class="form-card">
            <div class="card-head">
                <h3>Get a Free Quote</h3>
                <p>Tell us about your project — we'll respond within 24 hours.</p>
            </div>

            <?php if ($error): ?>
            <div class="err-bar">⚠️ <?= e($error); ?></div>
            <?php endif; ?>

            <form action="" method="POST" id="itForm">
                <input type="hidden" name="description" id="selected_service" value="">

                <!-- Service Selection -->
                <div class="fg">
                    <label>Service Required</label>
                    <div class="svc-grid">
                        <div class="svc-btn" data-value="Website Development" onclick="selectSvc(this)">
                            <span class="em">🌐</span>Website Dev
                        </div>
                        <div class="svc-btn" data-value="Mobile App Development" onclick="selectSvc(this)">
                            <span class="em">📱</span>Mobile App
                        </div>
                        <div class="svc-btn" data-value="Custom Software" onclick="selectSvc(this)">
                            <span class="em">⚙️</span>Custom Software
                        </div>
                        <div class="svc-btn" data-value="ERP / CRM Solution" onclick="selectSvc(this)">
                            <span class="em">📊</span>ERP / CRM
                        </div>
                        <div class="svc-btn" data-value="Digital Marketing" onclick="selectSvc(this)">
                            <span class="em">📣</span>Digital Mktg
                        </div>
                        <div class="svc-btn" data-value="Cloud & Hosting" onclick="selectSvc(this)">
                            <span class="em">☁️</span>Cloud & Host
                        </div>
                    </div>
                </div>

                <div class="fl">
                    <div class="fg">
                        <label>Full Name <span class="req">*</span></label>
                        <input type="text" name="name" id="nameField" placeholder="John Smith" required autocomplete="name">
                    </div>
                    <div class="fg">
                        <label>Company Name</label>
                        <input type="text" name="company" placeholder="Your Company Ltd">
                    </div>
                </div>
                <div class="fl">
                    <div class="fg">
                        <label>Phone <span class="req">*</span></label>
                        <input type="tel" name="phonenumber" id="phoneField" placeholder="+91 98765 43210" required maxlength="15" autocomplete="tel">
                    </div>
                    <div class="fg">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="you@company.com" autocomplete="email">
                    </div>
                </div>
                <div class="fg">
                    <label>Project Budget (₹)</label>
                    <div class="budget-row">
                        <span class="pfx">₹</span>
                        <input type="number" name="lead_value" placeholder="e.g. 50,000" min="0" step="5000">
                    </div>
                </div>

                <button type="submit" class="submit-btn" onclick="return checkForm()">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                    Get My Free Quote
                </button>

                <div class="trust-line">
                    <span class="ok">✓</span> No spam &nbsp;·&nbsp; <span class="ok">✓</span> No commitment &nbsp;·&nbsp; <span class="ok">✓</span> 100% free
                </div>
            </form>
        </div>
    </div>
</section>

<!-- ─── FEATURES ─── -->
<section class="features">
    <div class="features-grid">
        <div class="feat-card">
            <span class="feat-icon">🚀</span>
            <h4>Fast Delivery</h4>
            <p>Agile methodology ensures rapid delivery without compromising quality.</p>
        </div>
        <div class="feat-card">
            <span class="feat-icon">🏆</span>
            <h4>Award-Winning Design</h4>
            <p>Stunning, conversion-optimized interfaces built by expert UI/UX designers.</p>
        </div>
        <div class="feat-card">
            <span class="feat-icon">🔒</span>
            <h4>Enterprise Security</h4>
            <p>Bank-grade security standards built into every layer of your application.</p>
        </div>
        <div class="feat-card">
            <span class="feat-icon">🌍</span>
            <h4>Global Standards</h4>
            <p>Code and infrastructure that meets international compliance & scalability standards.</p>
        </div>
    </div>
</section>

<!-- ─── PROCESS ─── -->
<section class="process">
    <div class="process-inner">
        <div class="section-head">
            <div class="eyebrow">Our Process</div>
            <h2>From Idea to Launch in 4 Steps</h2>
        </div>
        <div class="steps-row">
            <div class="step-item">
                <div class="step-circle">01</div>
                <h4>Discovery Call</h4>
                <p>We understand your goals, audience, and technical needs.</p>
            </div>
            <div class="step-item">
                <div class="step-circle">02</div>
                <h4>Custom Proposal</h4>
                <p>Receive a detailed scope, timeline, and cost estimate.</p>
            </div>
            <div class="step-item">
                <div class="step-circle">03</div>
                <h4>Design & Build</h4>
                <p>Our team executes with weekly progress updates to you.</p>
            </div>
            <div class="step-item">
                <div class="step-circle">04</div>
                <h4>Launch & Support</h4>
                <p>We deploy, train your team, and provide ongoing support.</p>
            </div>
        </div>
    </div>
</section>

<!-- ─── FOOTER ─── -->
<footer class="footer">
    &copy; <?= date('Y'); ?> <?= e($crm_name ?: 'TEHubTech Solutions'); ?> &mdash; All Rights Reserved
    <?php if (!empty($crm_email)): ?> &nbsp;|&nbsp; <a href="mailto:<?= e($crm_email); ?>"><?= e($crm_email); ?></a><?php endif; ?>
    <?php if (!empty($crm_phone)): ?> &nbsp;|&nbsp; <?= e($crm_phone); ?><?php endif; ?>
    <?php if (!empty($crm_website)): ?> &nbsp;|&nbsp; <a href="<?= e($crm_website); ?>" target="_blank"><?= e($crm_website); ?></a><?php endif; ?>
</footer>

<script>
function selectSvc(el) {
    document.querySelectorAll('.svc-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('selected_service').value = el.dataset.value;
}
function checkForm() {
    var n = document.getElementById('nameField').value.trim();
    var p = document.getElementById('phoneField').value.trim();
    if (!n) { alert('Please enter your full name.'); return false; }
    if (!p || p.replace(/\D/g,'').length < 10) { alert('Please enter a valid phone number.'); return false; }
    return true;
}
</script>

<?php endif; ?>
</body>
</html>
