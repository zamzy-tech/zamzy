<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Loan Solutions | Apply Online in Minutes</title>
    <meta name="description" content="Get the best loan rates for home loans, personal loans, business loans. Quick approval, minimal documentation. Apply now.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy:   #0a0f2e;
            --navy2:  #111740;
            --gold:   #f0a500;
            --gold2:  #ffc542;
            --white:  #ffffff;
            --gray50: #f8f9fc;
            --gray100:#f0f2f8;
            --gray300:#c8cfe0;
            --gray500:#8892a4;
            --gray700:#4a5568;
            --accent: #1a6ef5;
            --green:  #00c471;
            --radius: 14px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--gray50);
            color: var(--navy);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── NAVBAR ─── */
        .navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            background: rgba(10,15,46,0.96);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            padding: 0 32px;
            height: 66px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .navbar .logo {
            display: flex; align-items: center; gap: 10px;
            font-size: 20px; font-weight: 800; color: var(--white);
            letter-spacing: -0.5px;
        }
        .navbar .logo .badge-dot {
            width: 10px; height: 10px;
            background: var(--gold);
            border-radius: 50%;
            box-shadow: 0 0 10px var(--gold);
        }
        .navbar .tag {
            background: rgba(240,165,0,0.15);
            color: var(--gold2);
            border: 1px solid rgba(240,165,0,0.3);
            font-size: 11.5px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            letter-spacing: 0.5px;
        }

        /* ─── HERO ─── */
        .hero {
            background: linear-gradient(135deg, var(--navy) 0%, #0d1545 40%, #122060 100%);
            padding: 130px 32px 80px;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(ellipse 80% 60% at 70% 50%, rgba(26,110,245,0.18) 0%, transparent 70%),
                        radial-gradient(ellipse 50% 40% at 20% 80%, rgba(240,165,0,0.10) 0%, transparent 70%);
            pointer-events: none;
        }
        .hero-grid {
            max-width: 1160px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 64px;
            align-items: center;
            position: relative; z-index: 1;
        }
        .hero-left { color: var(--white); }
        .hero-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(240,165,0,0.12);
            border: 1px solid rgba(240,165,0,0.28);
            color: var(--gold2);
            font-size: 12px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 20px;
            margin-bottom: 24px;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }
        .hero-chip span { width: 6px; height: 6px; background: var(--gold2); border-radius: 50%; animation: blink 1.6s infinite; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.3} }
        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(36px, 4.5vw, 56px);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 22px;
            letter-spacing: -1px;
        }
        .hero h1 em { font-style: normal; color: var(--gold2); }
        .hero-sub {
            font-size: 17px;
            color: rgba(255,255,255,0.65);
            max-width: 440px;
            margin-bottom: 40px;
            font-weight: 400;
            line-height: 1.7;
        }
        .hero-badges {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }
        .hero-badge {
            display: flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 13px;
            color: rgba(255,255,255,0.82);
            font-weight: 500;
        }
        .hero-badge .icon { font-size: 18px; }

        /* ─── FORM CARD ─── */
        .form-card {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 32px 80px rgba(0,0,0,0.35);
            overflow: hidden;
        }
        .form-card-header {
            background: linear-gradient(135deg, var(--gold) 0%, #e8930a 100%);
            padding: 22px 28px;
        }
        .form-card-header h3 {
            color: var(--navy);
            font-size: 17px;
            font-weight: 800;
            margin-bottom: 2px;
        }
        .form-card-header p { color: rgba(10,15,46,0.65); font-size: 13px; }
        .form-card-body { padding: 28px; }

        /* Loan Type Selector */
        .loan-types {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
            margin-bottom: 22px;
        }
        .lt-btn {
            border: 1.5px solid var(--gray300);
            border-radius: 10px;
            padding: 10px 6px;
            text-align: center;
            cursor: pointer;
            transition: all 0.18s ease;
            font-size: 11.5px;
            font-weight: 600;
            color: var(--gray700);
            background: var(--gray50);
            user-select: none;
        }
        .lt-btn .em { font-size: 20px; display: block; margin-bottom: 4px; }
        .lt-btn:hover { border-color: var(--accent); color: var(--accent); background: #eef3ff; transform: translateY(-1px); }
        .lt-btn.active { border-color: var(--gold); color: var(--navy); background: linear-gradient(135deg,#fff8e6,#fff3cc); box-shadow: 0 3px 10px rgba(240,165,0,0.2); }

        .fl { display: flex; gap: 12px; }
        .fl .fg { flex: 1; }
        .fg { margin-bottom: 16px; }
        .fg label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: var(--gray700);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .fg label .req { color: #e53e3e; margin-left: 2px; }
        .fg input, .fg select, .fg textarea {
            width: 100%;
            border: 1.5px solid var(--gray300);
            border-radius: 9px;
            padding: 11px 14px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: var(--navy);
            background: var(--gray50);
            outline: none;
            transition: all 0.18s;
        }
        .fg input:focus, .fg select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(26,110,245,0.1); background: #fff; }
        .fg .prefix-wrap { position: relative; }
        .fg .prefix-wrap .prefix {
            position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
            font-size: 14px; color: var(--gray500); font-weight: 600; pointer-events: none;
        }
        .fg .prefix-wrap input { padding-left: 30px; }

        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--navy) 0%, #1a2060 100%);
            color: var(--white);
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 4px;
        }
        .submit-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(10,15,46,0.35); }
        .submit-btn:active { transform: translateY(0); }
        .form-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 14px;
            font-size: 11.5px;
            color: var(--gray500);
        }
        .form-footer span { color: var(--green); font-weight: 600; }

        /* ─── FEATURES STRIP ─── */
        .features {
            background: var(--white);
            border-top: 1px solid var(--gray100);
            border-bottom: 1px solid var(--gray100);
        }
        .features-inner {
            max-width: 1160px;
            margin: 0 auto;
            padding: 52px 32px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 32px;
        }
        .feat {
            text-align: center;
        }
        .feat .feat-icon {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, #eef3ff, #dde7ff);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 26px;
            margin: 0 auto 14px;
        }
        .feat h4 { font-size: 15px; font-weight: 700; color: var(--navy); margin-bottom: 6px; }
        .feat p { font-size: 13px; color: var(--gray500); line-height: 1.5; }

        /* ─── HOW IT WORKS ─── */
        .steps-section {
            background: var(--gray50);
            padding: 72px 32px;
        }
        .section-title {
            text-align: center;
            margin-bottom: 48px;
        }
        .section-title .eyebrow {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--accent);
            margin-bottom: 10px;
        }
        .section-title h2 {
            font-family: 'Playfair Display', serif;
            font-size: 34px;
            font-weight: 800;
            color: var(--navy);
        }
        .steps {
            max-width: 860px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            position: relative;
        }
        .steps::before {
            content: '';
            position: absolute;
            top: 28px; left: calc(100% / 6); right: calc(100% / 6);
            height: 2px;
            background: linear-gradient(90deg, var(--gold), var(--accent));
            z-index: 0;
        }
        .step {
            text-align: center;
            position: relative; z-index: 1;
        }
        .step-num {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, var(--gold), #e8930a);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 800; color: var(--navy);
            margin: 0 auto 16px;
            box-shadow: 0 6px 20px rgba(240,165,0,0.35);
        }
        .step h4 { font-size: 15px; font-weight: 700; color: var(--navy); margin-bottom: 7px; }
        .step p  { font-size: 13px; color: var(--gray500); }

        /* ─── SUCCESS ─── */
        .success-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--navy) 0%, #0d1545 100%);
            padding: 40px 20px;
        }
        .success-card {
            background: var(--white);
            border-radius: 24px;
            padding: 56px 48px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 32px 80px rgba(0,0,0,0.3);
        }
        .success-card .check {
            width: 80px; height: 80px;
            background: linear-gradient(135deg, var(--green), #009051);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 36px;
            margin: 0 auto 24px;
            box-shadow: 0 10px 30px rgba(0,196,113,0.35);
            animation: popIn 0.4s cubic-bezier(0.34,1.56,0.64,1) both;
        }
        @keyframes popIn { from { transform: scale(0); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .success-card h2 { font-size: 26px; font-weight: 800; color: var(--navy); margin-bottom: 12px; }
        .success-card p  { font-size: 15px; color: var(--gray500); line-height: 1.7; }
        .success-card .ref {
            display: inline-block;
            background: var(--gray100);
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 13px;
            color: var(--navy);
            font-weight: 600;
            margin-top: 20px;
        }

        /* ─── FOOTER ─── */
        .footer {
            background: var(--navy);
            color: rgba(255,255,255,0.5);
            text-align: center;
            font-size: 12.5px;
            padding: 24px 32px;
        }
        .footer a { color: var(--gold2); text-decoration: none; }

        /* ─── ERROR ─── */
        .err-bar {
            background: #fff3f3; border-left: 4px solid #e53e3e;
            border-radius: 8px; padding: 12px 16px;
            font-size: 13.5px; color: #c62828;
            margin-bottom: 20px;
            display: flex; align-items: center; gap: 8px;
        }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 900px) {
            .hero-grid { grid-template-columns: 1fr; gap: 40px; }
            .form-card { max-width: 100%; }
            .features-inner { grid-template-columns: repeat(2,1fr); }
            .steps { grid-template-columns: 1fr; }
            .steps::before { display: none; }
        }
        @media (max-width: 540px) {
            .hero { padding: 100px 20px 60px; }
            .form-card-body { padding: 20px; }
            .loan-types { grid-template-columns: repeat(3,1fr); }
            .fl { flex-direction: column; gap: 0; }
            .features-inner { grid-template-columns: 1fr; gap: 24px; }
        }
    </style>
</head>
<body>
<?php if ($success): ?>
<!-- ─── SUCCESS STATE ─── -->
<div class="success-page">
    <div class="success-card">
        <div class="check">✓</div>
        <h2>Application Submitted!</h2>
        <p>Thank you! Your loan enquiry has been received. Our financial advisor will contact you within <strong>24 hours</strong> to guide you through the best options.</p>
        <div class="ref">📋 Reference: LN-<?= strtoupper(substr(md5(time()), 0, 8)); ?></div>
        <p style="margin-top:20px; font-size:13px; color:#8892a4;">Keep your phone nearby. We'll call shortly.</p>
    </div>
</div>
<?php else: ?>

<!-- ─── NAVBAR ─── -->
<nav class="navbar">
    <div class="logo">
        <?php if (!empty($crm_logo)): ?>
            <img src="<?= e($crm_logo); ?>" alt="<?= e($crm_name); ?>" style="max-height:36px; max-width:160px; object-fit:contain;">
        <?php else: ?>
            <div class="badge-dot"></div>
            <span><?= e($crm_name ?: 'FinServPro'); ?></span>
        <?php endif; ?>
    </div>
    <div class="tag">🔒 SSL Secured</div>
</nav>

<!-- ─── HERO ─── -->
<section class="hero">
    <div class="hero-grid">
        <div class="hero-left">
            <div class="hero-chip"><span></span> Trusted Loan Experts</div>
            <h1>Smart Loans,<br><em>Faster Approvals.</em><br>Better Rates.</h1>
            <p class="hero-sub">Get personalized loan solutions with competitive interest rates, minimal documentation, and quick disbursals. Apply in minutes — our experts do the rest.</p>
            <div class="hero-badges">
                <div class="hero-badge"><span class="icon">⚡</span> 48-Hour Approval</div>
                <div class="hero-badge"><span class="icon">📄</span> Minimal Docs</div>
                <div class="hero-badge"><span class="icon">🏦</span> 15+ Partner Banks</div>
                <div class="hero-badge"><span class="icon">🔒</span> Data Secured</div>
            </div>
            <?php if (!empty($crm_phone) || !empty($crm_email)): ?>
            <div style="margin-top:28px; padding-top:24px; border-top:1px solid rgba(255,255,255,0.1);">
                <p style="font-size:12px; text-transform:uppercase; letter-spacing:1px; color:rgba(255,255,255,0.4); margin-bottom:10px; font-weight:600;">Contact Us Directly</p>
                <?php if (!empty($crm_phone)): ?>
                <div class="hero-badge" style="margin-bottom:8px;"><span class="icon">📞</span> <?= e($crm_phone); ?></div>
                <?php endif; ?>
                <?php if (!empty($crm_email)): ?>
                <div class="hero-badge"><span class="icon">✉️</span> <?= e($crm_email); ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- FORM CARD -->
        <div class="form-card">
            <div class="form-card-header">
                <h3>Apply for a Loan</h3>
                <p>Fill in the form below — takes under 2 minutes</p>
            </div>
            <div class="form-card-body">
                <?php if ($error): ?>
                <div class="err-bar">⚠️ <?= e($error); ?></div>
                <?php endif; ?>

                <form action="" method="POST" id="loanForm">
                    <input type="hidden" name="description" id="selected_loan_type" value="">

                    <!-- Loan Type -->
                    <div style="margin-bottom:18px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:var(--gray700); margin-bottom:8px; text-transform:uppercase; letter-spacing:0.5px;">Select Loan Type</label>
                        <div class="loan-types">
                            <div class="lt-btn" data-value="Home Loan" onclick="selectLoan(this)">
                                <span class="em">🏠</span>Home Loan
                            </div>
                            <div class="lt-btn" data-value="Personal Loan" onclick="selectLoan(this)">
                                <span class="em">👤</span>Personal
                            </div>
                            <div class="lt-btn" data-value="Business Loan" onclick="selectLoan(this)">
                                <span class="em">🏢</span>Business
                            </div>
                            <div class="lt-btn" data-value="Car Loan" onclick="selectLoan(this)">
                                <span class="em">🚗</span>Car Loan
                            </div>
                            <div class="lt-btn" data-value="Education Loan" onclick="selectLoan(this)">
                                <span class="em">🎓</span>Education
                            </div>
                            <div class="lt-btn" data-value="Gold Loan" onclick="selectLoan(this)">
                                <span class="em">💰</span>Gold Loan
                            </div>
                        </div>
                    </div>

                    <div class="fl">
                        <div class="fg">
                            <label>Full Name <span class="req">*</span></label>
                            <input type="text" name="name" id="nameField" placeholder="John Smith" required autocomplete="name">
                        </div>
                    </div>
                    <div class="fl">
                        <div class="fg">
                            <label>Phone Number <span class="req">*</span></label>
                            <input type="tel" name="phonenumber" id="phoneField" placeholder="+91 98765 43210" required maxlength="15" autocomplete="tel">
                        </div>
                        <div class="fg">
                            <label>Email Address</label>
                            <input type="email" name="email" placeholder="you@email.com" autocomplete="email">
                        </div>
                    </div>
                    <div class="fg">
                        <label>Loan Amount Required</label>
                        <div class="prefix-wrap">
                            <span class="prefix">₹</span>
                            <input type="number" name="lead_value" placeholder="500,000" min="0" step="10000">
                        </div>
                    </div>

                    <button type="submit" class="submit-btn" onclick="return checkForm()">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        Get My Loan Offer
                    </button>

                    <div class="form-footer">
                        🔒 <span>Your data is 100% private</span> &amp; never sold to third parties
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- ─── FEATURES STRIP ─── -->
<section class="features">
    <div class="features-inner">
        <div class="feat">
            <div class="feat-icon">⚡</div>
            <h4>Instant Pre-Approval</h4>
            <p>Know your eligibility in minutes with our smart loan assessment engine.</p>
        </div>
        <div class="feat">
            <div class="feat-icon">💸</div>
            <h4>Best Interest Rates</h4>
            <p>Compare rates from 15+ leading banks and NBFCs to get the lowest EMI.</p>
        </div>
        <div class="feat">
            <div class="feat-icon">📱</div>
            <h4>100% Digital Process</h4>
            <p>No branch visits. Upload documents online and track your application live.</p>
        </div>
        <div class="feat">
            <div class="feat-icon">🛡️</div>
            <h4>Secure & Confidential</h4>
            <p>Bank-grade 256-bit SSL encryption protects all your personal information.</p>
        </div>
    </div>
</section>

<!-- ─── HOW IT WORKS ─── -->
<section class="steps-section">
    <div class="section-title">
        <div class="eyebrow">Simple Process</div>
        <h2>How It Works</h2>
    </div>
    <div class="steps">
        <div class="step">
            <div class="step-num">1</div>
            <h4>Submit Enquiry</h4>
            <p>Fill the form with your basic details and loan requirements.</p>
        </div>
        <div class="step">
            <div class="step-num">2</div>
            <h4>Expert Consultation</h4>
            <p>Our advisor calls you within 24 hours to discuss the best offers.</p>
        </div>
        <div class="step">
            <div class="step-num">3</div>
            <h4>Get Disbursed</h4>
            <p>Complete verification online and receive funds directly in your account.</p>
        </div>
    </div>
</section>

<!-- ─── FOOTER ─── -->
<footer class="footer">
    &copy; <?= date('Y'); ?> <?= e($crm_name ?: 'FinServPro'); ?> &mdash; All Rights Reserved
    <?php if (!empty($crm_email)): ?> &nbsp;|&nbsp; <a href="mailto:<?= e($crm_email); ?>"><?= e($crm_email); ?></a><?php endif; ?>
    <?php if (!empty($crm_phone)): ?> &nbsp;|&nbsp; <?= e($crm_phone); ?><?php endif; ?>
    <?php if (!empty($crm_website)): ?> &nbsp;|&nbsp; <a href="<?= e($crm_website); ?>" target="_blank"><?= e($crm_website); ?></a><?php endif; ?>
</footer>

<script>
function selectLoan(el) {
    document.querySelectorAll('.lt-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('selected_loan_type').value = el.dataset.value;
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
