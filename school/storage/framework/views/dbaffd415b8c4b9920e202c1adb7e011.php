<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $__env->make('layouts.gtag', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Get Free Demo - SMS School Management System</title>
    <meta name="description" content="Transform your school with SMS - the complete school management system. Manage admissions, attendance, fees, exams & more. Get a free demo today!">
    <link rel="shortcut icon" href="<?php echo e($systemSettings['favicon'] ?? url('assets/vertical-logo.svg')); ?>"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            min-height: 100vh;
            color: #fff;
            overflow-x: hidden;
        }

        /* Animated Background */
        .bg-shapes {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none; z-index: 0; overflow: hidden;
        }
        .bg-shapes .shape {
            position: absolute; border-radius: 50%;
            background: rgba(99, 102, 241, 0.08);
            animation: float 20s ease-in-out infinite;
        }
        .bg-shapes .shape:nth-child(1) { width: 400px; height: 400px; top: -100px; right: -100px; animation-delay: 0s; }
        .bg-shapes .shape:nth-child(2) { width: 300px; height: 300px; bottom: -80px; left: -80px; animation-delay: -5s; background: rgba(168, 85, 247, 0.08); }
        .bg-shapes .shape:nth-child(3) { width: 200px; height: 200px; top: 50%; left: 50%; animation-delay: -10s; background: rgba(59, 130, 246, 0.08); }
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            25% { transform: translateY(-20px) rotate(5deg); }
            50% { transform: translateY(10px) rotate(-3deg); }
            75% { transform: translateY(-15px) rotate(2deg); }
        }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; position: relative; z-index: 1; }

        /* Header */
        .header {
            padding: 20px 0;
            display: flex; align-items: center; justify-content: space-between;
        }
        .logo-area { display: flex; align-items: center; gap: 12px; }
        .logo-area img { height: 48px; border-radius: 10px; }
        .logo-area h2 { font-size: 1.4rem; font-weight: 700; }
        .header-badge {
            background: linear-gradient(135deg, #10b981, #059669);
            padding: 8px 20px; border-radius: 50px;
            font-size: 0.85rem; font-weight: 600;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            50% { box-shadow: 0 0 0 12px rgba(16, 185, 129, 0); }
        }

        /* Hero Section */
        .hero { padding: 40px 0 60px; display: flex; gap: 60px; align-items: center; }
        .hero-content { flex: 1; }
        .hero-content .tag {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.3);
            padding: 8px 18px; border-radius: 50px; font-size: 0.85rem;
            font-weight: 500; margin-bottom: 24px; color: #a5b4fc;
        }
        .hero-content h1 {
            font-size: 2.8rem; font-weight: 800; line-height: 1.15;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #fff 0%, #c7d2fe 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-content h1 span {
            background: linear-gradient(135deg, #818cf8, #a78bfa);
            -webkit-background-clip: text; background-clip: text;
        }
        .hero-content p {
            font-size: 1.1rem; color: #94a3b8; line-height: 1.7; margin-bottom: 32px;
        }

        /* Features List */
        .features-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 36px;
        }
        .feature-item {
            display: flex; align-items: center; gap: 12px;
            background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);
            padding: 14px 18px; border-radius: 12px;
            transition: all 0.3s ease;
        }
        .feature-item:hover {
            background: rgba(99, 102, 241, 0.1); border-color: rgba(99, 102, 241, 0.2);
            transform: translateY(-2px);
        }
        .feature-item .icon {
            width: 36px; height: 36px; border-radius: 8px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem; flex-shrink: 0;
        }
        .feature-item span { font-size: 0.9rem; font-weight: 500; color: #cbd5e1; }

        /* Trust badges */
        .trust-row {
            display: flex; align-items: center; gap: 30px; flex-wrap: wrap;
        }
        .trust-item {
            display: flex; align-items: center; gap: 8px;
            font-size: 0.85rem; color: #64748b;
        }
        .trust-item i { color: #10b981; }

        /* Form Card */
        .form-card {
            flex: 0 0 420px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px; padding: 36px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
        }
        .form-card h3 {
            font-size: 1.5rem; font-weight: 700; margin-bottom: 6px; text-align: center;
        }
        .form-card .subtitle {
            font-size: 0.9rem; color: #94a3b8; text-align: center; margin-bottom: 28px;
        }
        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block; font-size: 0.85rem; font-weight: 500;
            color: #cbd5e1; margin-bottom: 6px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%; padding: 14px 16px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px; font-size: 0.95rem;
            color: #fff; font-family: 'Inter', sans-serif;
            transition: all 0.3s ease; outline: none;
        }
        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #64748b;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
            background: rgba(255, 255, 255, 0.08);
        }
        .form-group select option { background: #1e1b4b; color: #fff; }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .form-row { display: flex; gap: 14px; }
        .form-row .form-group { flex: 1; }

        .submit-btn {
            width: 100%; padding: 16px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border: none; border-radius: 14px;
            font-size: 1.05rem; font-weight: 700;
            color: #fff; cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            margin-top: 8px;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
        }
        .submit-btn:active { transform: translateY(0); }
        .submit-btn:disabled {
            opacity: 0.7; cursor: not-allowed; transform: none !important;
        }
        .submit-btn .spinner {
            display: none; width: 20px; height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: #fff; border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .form-footer {
            text-align: center; margin-top: 16px;
            font-size: 0.8rem; color: #64748b;
        }
        .form-footer i { color: #10b981; margin-right: 4px; }

        /* Success State */
        .success-message {
            display: none; text-align: center; padding: 40px 20px;
        }
        .success-message .check-circle {
            width: 80px; height: 80px; border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px; font-size: 2rem;
            animation: scaleIn 0.5s ease;
        }
        @keyframes scaleIn {
            0% { transform: scale(0); } 50% { transform: scale(1.2); } 100% { transform: scale(1); }
        }
        .success-message h3 { font-size: 1.5rem; margin-bottom: 12px; }
        .success-message p { color: #94a3b8; line-height: 1.6; }

        /* Error */
        .error-text { color: #f87171; font-size: 0.8rem; margin-top: 4px; display: none; }
        .form-group.error input,
        .form-group.error select { border-color: #f87171; }
        .form-group.error .error-text { display: block; }

        /* Alert */
        .alert {
            padding: 14px 18px; border-radius: 12px; margin-bottom: 18px;
            font-size: 0.9rem; display: none;
        }
        .alert-error {
            background: rgba(248, 113, 113, 0.1); border: 1px solid rgba(248, 113, 113, 0.2);
            color: #fca5a5;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .hero { flex-direction: column; gap: 40px; padding: 20px 0 40px; }
            .form-card { flex: none; width: 100%; max-width: 480px; margin: 0 auto; }
            .hero-content h1 { font-size: 2rem; }
            .features-grid { grid-template-columns: 1fr; }
            .header { flex-direction: column; gap: 12px; text-align: center; }
        }
        @media (max-width: 480px) {
            .form-card { padding: 24px 20px; }
            .form-row { flex-direction: column; gap: 0; }
            .hero-content h1 { font-size: 1.7rem; }
        }
    </style>
</head>
<body>

<div class="bg-shapes">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
</div>

<div class="container">
    <!-- Header -->
    <header class="header">
        <div class="logo-area">
            <img src="<?php echo e($systemSettings['horizontal_logo'] ?? asset('assets/home_page/images/logo.png')); ?>" alt="SMS Logo">
            <h2>SMS</h2>
        </div>
        <div class="header-badge">
            <i class="fas fa-gift"></i>&nbsp; Get Free Demo
        </div>
    </header>

    <!-- Hero -->
    <section class="hero">
        <div class="hero-content">
            <div class="tag">
                <i class="fas fa-bolt"></i>
                #1 School Management System
            </div>
            <h1>Transform Your School with <span>Smart Digital Management</span></h1>
            <p>Streamline admissions, attendance, fees, exams, and communication — all in one powerful platform. Trusted by schools across India.</p>

            <div class="features-grid">
                <div class="feature-item">
                    <div class="icon"><i class="fas fa-user-graduate"></i></div>
                    <span>Student & Admission Management</span>
                </div>
                <div class="feature-item">
                    <div class="icon"><i class="fas fa-clipboard-check"></i></div>
                    <span>Attendance Tracking</span>
                </div>
                <div class="feature-item">
                    <div class="icon"><i class="fas fa-indian-rupee-sign"></i></div>
                    <span>Online Fee Collection</span>
                </div>
                <div class="feature-item">
                    <div class="icon"><i class="fas fa-file-alt"></i></div>
                    <span>Exam & Report Cards</span>
                </div>
                <div class="feature-item">
                    <div class="icon"><i class="fab fa-whatsapp"></i></div>
                    <span>WhatsApp Notifications</span>
                </div>
                <div class="feature-item">
                    <div class="icon"><i class="fas fa-mobile-alt"></i></div>
                    <span>Parent & Teacher Apps</span>
                </div>
            </div>

            <div class="trust-row">
                <div class="trust-item"><i class="fas fa-check-circle"></i> Free Setup</div>
                <div class="trust-item"><i class="fas fa-check-circle"></i> No Hidden Charges</div>
                <div class="trust-item"><i class="fas fa-check-circle"></i> 24/7 Support</div>
            </div>
        </div>

        <!-- Lead Form -->
        <div class="form-card">
            <div id="formContent">
                <h3>📩 Request Free Demo</h3>
                <p class="subtitle">Fill the form and our team will contact you within 24 hours</p>

                <div class="alert alert-error" id="alertError"></div>

                <form id="leadForm" method="POST" action="<?php echo e(url('/lead-form')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" placeholder="Your name" required>
                            <div class="error-text">Please enter your name</div>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" placeholder="98XXXXXXXX" required>
                            <div class="error-text">Enter a valid 10-digit number</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="your@email.com">
                    </div>

                    <div class="form-group">
                        <label for="school_name">School / Institution Name *</label>
                        <input type="text" id="school_name" name="school_name" placeholder="Enter school name" required>
                        <div class="error-text">Please enter your school name</div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City *</label>
                            <input type="text" id="city" name="city" placeholder="Your city" required>
                            <div class="error-text">Please enter your city</div>
                        </div>
                        <div class="form-group">
                            <label for="students_count">No. of Students</label>
                            <select id="students_count" name="students_count">
                                <option value="">Select range</option>
                                <option value="1-100">1 - 100</option>
                                <option value="100-300">100 - 300</option>
                                <option value="300-500">300 - 500</option>
                                <option value="500-1000">500 - 1000</option>
                                <option value="1000+">1000+</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="message">Message (Optional)</label>
                        <textarea id="message" name="message" placeholder="Any specific requirements?"></textarea>
                    </div>

                    <button type="submit" class="submit-btn" id="submitBtn">
                        <span class="btn-text">Get Free Demo →</span>
                        <div class="spinner" id="spinner"></div>
                    </button>
                </form>

                <div class="form-footer">
                    <i class="fas fa-lock"></i> Your information is 100% secure and will not be shared
                </div>
            </div>

            <div class="success-message" id="successMessage">
                <div class="check-circle">
                    <i class="fas fa-check"></i>
                </div>
                <h3>Thank You! 🎉</h3>
                <p>Your demo request has been received successfully. Our team will contact you within 24 hours.</p>
                <p style="margin-top: 16px; color: #6366f1; font-weight: 600;">
                    <i class="fas fa-phone"></i>&nbsp; For immediate assistance, call us now
                </p>
            </div>
        </div>
    </section>
</div>

<script>
document.getElementById('leadForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Validate
    let valid = true;
    const name = document.getElementById('name');
    const phone = document.getElementById('phone');
    const school = document.getElementById('school_name');
    const city = document.getElementById('city');

    [name, phone, school, city].forEach(el => el.parentElement.classList.remove('error'));

    if (!name.value.trim()) { name.parentElement.classList.add('error'); valid = false; }
    if (!phone.value.trim() || !/^[6-9]\d{9}$/.test(phone.value.trim())) { phone.parentElement.classList.add('error'); valid = false; }
    if (!school.value.trim()) { school.parentElement.classList.add('error'); valid = false; }
    if (!city.value.trim()) { city.parentElement.classList.add('error'); valid = false; }

    if (!valid) return;

    const btn = document.getElementById('submitBtn');
    const spinner = document.getElementById('spinner');
    const btnText = btn.querySelector('.btn-text');

    btn.disabled = true;
    spinner.style.display = 'block';
    btnText.textContent = 'Submitting...';

    const formData = new FormData(this);

    fetch('<?php echo e(url("/lead-form")); ?>', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('formContent').querySelector('form').style.display = 'none';
            document.getElementById('formContent').querySelector('h3').style.display = 'none';
            document.getElementById('formContent').querySelector('.subtitle').style.display = 'none';
            document.getElementById('formContent').querySelector('.form-footer').style.display = 'none';
            document.getElementById('successMessage').style.display = 'block';

            // Google Ads conversion tracking
            if (typeof gtag === 'function') {
                gtag('event', 'conversion', {
                    'send_to': 'AW-18267214365/lead_form_submit',
                    'event_callback': function() { console.log('Conversion tracked'); }
                });
            }
        } else {
            const alert = document.getElementById('alertError');
            alert.textContent = data.message || 'Something went wrong. Please try again.';
            alert.style.display = 'block';
            btn.disabled = false;
            spinner.style.display = 'none';
            btnText.textContent = 'Get Free Demo →';
        }
    })
    .catch(err => {
        const alert = document.getElementById('alertError');
        alert.textContent = 'Network error. Please try again.';
        alert.style.display = 'block';
        btn.disabled = false;
        spinner.style.display = 'none';
        btnText.textContent = 'Get Free Demo →';
    });
});
</script>

</body>
</html>
<?php /**PATH /home/shacartc/school.tehub.in/resources/views/lead-form.blade.php ENDPATH**/ ?>