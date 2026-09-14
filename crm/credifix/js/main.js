// CREDIFIX Main Application Script
// Handlers: WhatsApp OTP Forms, EMI Calculator, Eligibility Calculator, AI Chatbot Widget

document.addEventListener('DOMContentLoaded', () => {
  initEMICalculator();
  initEligibilityCalculator();
  initWhatsAppOTPForms();
  initChatbot();
  
  // Navbar scroll visual effect
  const navbar = document.querySelector('.navbar-custom');
  if (navbar) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 20) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    });
  }
});

// ==========================================
// 1. WHATSAPP OTP VERIFICATION & LEADS SYSTEM
// ==========================================
let activeOTP = null;
let otpTimer = null;

function initWhatsAppOTPForms() {
  const forms = [
    { formId: 'loanApplyForm', step1Id: 'step1_hero', step2Id: 'step2_hero', phoneInputId: 'heroPhone', alertId: 'heroOtpAlert' },
    { formId: 'modalApplyForm', step1Id: 'step1_modal', step2Id: 'step2_modal', phoneInputId: 'modalPhone', alertId: 'modalOtpAlert' },
    { formId: 'contactUsForm', step1Id: 'step1_contact', step2Id: 'step2_contact', phoneInputId: 'contactPhone', alertId: 'contactOtpAlert' }
  ];

  forms.forEach(cfg => {
    const form = document.getElementById(cfg.formId);
    if (!form) return;

    const sendOtpBtn = form.querySelector('.btn-send-whatsapp-otp');
    const verifyOtpBtn = form.querySelector('.btn-verify-otp');
    const resendOtpBtn = form.querySelector('.btn-resend-otp');

    if (sendOtpBtn) {
      sendOtpBtn.addEventListener('click', (e) => {
        e.preventDefault();
        handleSendWhatsAppOTP(cfg, sendOtpBtn);
      });
    }

    if (verifyOtpBtn) {
      verifyOtpBtn.addEventListener('click', (e) => {
        e.preventDefault();
        handleVerifyOTP(cfg);
      });
    }

    if (resendOtpBtn) {
      resendOtpBtn.addEventListener('click', (e) => {
        e.preventDefault();
        handleSendWhatsAppOTP(cfg, resendOtpBtn);
      });
    }
  });
}

async function handleSendWhatsAppOTP(cfg, triggerBtn) {
  const phoneInput = document.getElementById(cfg.phoneInputId);
  const alertBox = document.getElementById(cfg.alertId);
  if (!phoneInput) return;

  const phoneVal = phoneInput.value.trim();

  if (!/^[6-9]\d{9}$/.test(phoneVal)) {
    if (alertBox) {
      alertBox.className = 'alert alert-danger mt-3 py-2 small';
      alertBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Please enter a valid 10-digit Indian WhatsApp mobile number.';
      alertBox.classList.remove('d-none');
    }
    return;
  }

  // Generate 4-digit OTP
  activeOTP = Math.floor(1000 + Math.random() * 9000).toString();

  const origText = triggerBtn.innerHTML;
  triggerBtn.disabled = true;
  triggerBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Dispatching OTP...';

  try {
    // We fetch from the relative root send_otp.php. Adjust path if inside folders
    const prefix = window.location.pathname.includes('/services/') || 
                   window.location.pathname.includes('/calculator/') || 
                   window.location.pathname.includes('/blog/') || 
                   window.location.pathname.includes('/legal/') ? '../' : './';
                   
    const endpoint = `${prefix}send_otp.php?phone=${encodeURIComponent(phoneVal)}&otp=${encodeURIComponent(activeOTP)}`;
    const response = await fetch(endpoint, { method: 'GET' });
    const res = await response.json();

    triggerBtn.disabled = false;
    triggerBtn.innerHTML = origText;

    if (res.success) {
      const step1 = document.getElementById(cfg.step1Id);
      const step2 = document.getElementById(cfg.step2Id);

      if (step1) step1.classList.add('d-none');
      if (step2) step2.classList.remove('d-none');

      const displayPhone = document.querySelector(`#${cfg.step2Id} .whatsapp-number-display`);
      if (displayPhone) displayPhone.textContent = '+91 ' + phoneVal;

      if (alertBox) {
        alertBox.className = 'alert alert-info mt-3 py-2 small';
        alertBox.innerHTML = `<i class="bi bi-whatsapp text-success me-1 fs-6"></i> <strong>OTP Dispatched!</strong> Sent to WhatsApp +91 ${phoneVal}. Please check your phone.`;
        alertBox.classList.remove('d-none');
      }

      startOTPCountdown(cfg);
    } else {
      if (alertBox) {
        alertBox.className = 'alert alert-warning mt-3 py-2 small';
        alertBox.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> WhatsApp Gateway: ${res.error || 'Failed to dispatch OTP.'}`;
        alertBox.classList.remove('d-none');
      }
    }
  } catch (err) {
    triggerBtn.disabled = false;
    triggerBtn.innerHTML = origText;
    console.error('WhatsApp Gateway Error:', err);
    if (alertBox) {
      alertBox.className = 'alert alert-danger mt-3 py-2 small';
      alertBox.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> Network connection issue. Please check your internet connection.';
      alertBox.classList.remove('d-none');
    }
  }
}

async function handleVerifyOTP(cfg) {
  const alertBox = document.getElementById(cfg.alertId);
  const form = document.getElementById(cfg.formId);
  const singleOtpInput = document.querySelector(`#${cfg.step2Id} input[name="otp_code"]`);
  
  let enteredOTP = singleOtpInput ? singleOtpInput.value.trim() : '';

  if (enteredOTP === activeOTP) {
    // Extract Form Fields
    const nameInput = form.querySelector('input[type="text"]:not([name="otp_code"])');
    const phoneInput = document.getElementById(cfg.phoneInputId);
    const serviceSelect = form.querySelector('select');
    const amountInput = form.querySelector('input[type="number"]');

    const customerName = nameInput && nameInput.value.trim() ? nameInput.value.trim() : 'Valued Customer';
    const customerPhone = phoneInput ? phoneInput.value.trim() : '';
    const selectedService = serviceSelect ? serviceSelect.options[serviceSelect.selectedIndex].text : 'Financial Inquiry';
    const loanAmount = amountInput && amountInput.value.trim() ? amountInput.value.trim() : 'As per requirement';

    if (alertBox) {
      alertBox.className = 'alert alert-info mt-3 py-2 small';
      alertBox.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Submitting verified lead details...';
      alertBox.classList.remove('d-none');
    }

    try {
      const prefix = window.location.pathname.includes('/services/') || 
                     window.location.pathname.includes('/calculator/') || 
                     window.location.pathname.includes('/blog/') || 
                     window.location.pathname.includes('/legal/') ? '../' : './';
                     
      const leadUrl = `${prefix}send_otp.php?action=submit_lead&name=${encodeURIComponent(customerName)}&phone=${encodeURIComponent(customerPhone)}&service=${encodeURIComponent(selectedService)}&amount=${encodeURIComponent(loanAmount)}`;
      await fetch(leadUrl, { method: 'GET' });

      if (alertBox) {
        alertBox.className = 'alert alert-success mt-3 py-3';
        alertBox.innerHTML = `
          <div class="d-flex align-items-start gap-2">
            <i class="bi bi-check-circle-fill text-success fs-4 mt-1"></i>
            <div>
              <strong class="text-dark">Lead Submitted Successfully!</strong><br>
              <span class="small text-muted">Dear ${customerName}, your OTP is verified and details have been dispatched to our Executive Desk. Our officer will call you back within 30 minutes.</span>
            </div>
          </div>
        `;
      }
    } catch (err) {
      console.error('Lead dispatch error:', err);
    }

    const step2 = document.getElementById(cfg.step2Id);
    if (step2) {
      const inputs = step2.querySelectorAll('input, button');
      inputs.forEach(i => i.disabled = true);
    }
  } else {
    if (alertBox) {
      alertBox.className = 'alert alert-danger mt-3 py-2 small';
      alertBox.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> Invalid verification code. Please check your WhatsApp messages and try again.';
      alertBox.classList.remove('d-none');
    }
  }
}

function startOTPCountdown(cfg) {
  const step2 = document.getElementById(cfg.step2Id);
  if (!step2) return;

  const resendBtn = step2.querySelector('.btn-resend-otp');
  const timerSpan = step2.querySelector('.otp-timer-count');
  if (!resendBtn || !timerSpan) return;

  resendBtn.disabled = true;
  let seconds = 30;
  timerSpan.textContent = `(${seconds}s)`;

  if (otpTimer) clearInterval(otpTimer);

  otpTimer = setInterval(() => {
    seconds--;
    if (seconds > 0) {
      timerSpan.textContent = `(${seconds}s)`;
    } else {
      clearInterval(otpTimer);
      timerSpan.textContent = '';
      resendBtn.disabled = false;
    }
  }, 1000);
}

// ==========================================
// 2. LOAN EMI CALCULATOR
// ==========================================
function initEMICalculator() {
  const amountInput = document.getElementById('calcAmountInput');
  const rateInput = document.getElementById('calcRateInput');
  const tenureInput = document.getElementById('calcTenureInput');

  if (!amountInput) return;

  function updateEMIValues() {
    let p = parseFloat(amountInput.value) || 0;
    let r = parseFloat(rateInput ? rateInput.value : 9.5) || 0;
    let n = parseFloat(tenureInput ? tenureInput.value : 5) || 0;

    const amountDisplay = document.getElementById('calcAmountVal');
    const rateDisplay = document.getElementById('calcRateVal');
    const tenureDisplay = document.getElementById('calcTenureVal');

    if (amountDisplay) amountDisplay.textContent = formatINR(p);
    if (rateDisplay) rateDisplay.textContent = r > 0 ? (r.toFixed(1) + '% Per Annum') : '0%';
    if (tenureDisplay) tenureDisplay.textContent = n > 0 ? (n + (n === 1 ? ' Year' : ' Years') + ` (${n * 12} Months)`) : '0 Years';

    const emiDisplay = document.getElementById('resEMI');
    const interestDisplay = document.getElementById('resInterest');
    const totalDisplay = document.getElementById('resTotal');

    if (p <= 0 || r <= 0 || n <= 0) {
      if (emiDisplay) emiDisplay.textContent = '₹0';
      if (interestDisplay) interestDisplay.textContent = '₹0';
      if (totalDisplay) totalDisplay.textContent = '₹0';
      return;
    }

    let monthlyRate = (r / 12) / 100;
    let months = n * 12;

    let emi = (p * monthlyRate * Math.pow(1 + monthlyRate, months)) / (Math.pow(1 + monthlyRate, months) - 1);
    let totalPayment = emi * months;
    let totalInterest = totalPayment - p;

    if (emiDisplay) emiDisplay.textContent = '₹' + formatNumber(Math.round(emi));
    if (interestDisplay) interestDisplay.textContent = '₹' + formatNumber(Math.round(totalInterest));
    if (totalDisplay) totalDisplay.textContent = '₹' + formatNumber(Math.round(totalPayment));
  }

  amountInput.addEventListener('input', updateEMIValues);
  amountInput.addEventListener('change', updateEMIValues);

  if (rateInput) {
    rateInput.addEventListener('input', updateEMIValues);
    rateInput.addEventListener('change', updateEMIValues);
  }

  if (tenureInput) {
    tenureInput.addEventListener('input', updateEMIValues);
    tenureInput.addEventListener('change', updateEMIValues);
  }

  window.setCalcAmount = function(amt) {
    amountInput.value = amt;
    updateEMIValues();
  };

  window.setCalcRate = function(rate) {
    if (rateInput) {
      rateInput.value = rate;
      updateEMIValues();
    }
  };

  window.setCalcTenure = function(years) {
    if (tenureInput) {
      tenureInput.value = years;
      updateEMIValues();
    }
  };

  updateEMIValues();
}

// ==========================================
// 3. LOAN ELIGIBILITY CALCULATOR (NEW)
// ==========================================
function initEligibilityCalculator() {
  const incomeInput = document.getElementById('elIncome');
  const emiInput = document.getElementById('elEMI');
  const rateInput = document.getElementById('elRate');
  const tenureInput = document.getElementById('elTenure');

  if (!incomeInput) return;

  function calculateEligibility() {
    let income = parseFloat(incomeInput.value) || 0;
    let existingEmi = parseFloat(emiInput.value) || 0;
    let interestRate = parseFloat(rateInput.value) || 9.5;
    let tenureYears = parseFloat(tenureInput.value) || 20;

    const incomeDisplay = document.getElementById('elIncomeVal');
    const emiDisplay = document.getElementById('elEMIVal');
    const rateDisplay = document.getElementById('elRateVal');
    const tenureDisplay = document.getElementById('elTenureVal');

    if (incomeDisplay) incomeDisplay.textContent = '₹' + formatNumber(income);
    if (emiDisplay) emiDisplay.textContent = '₹' + formatNumber(existingEmi);
    if (rateDisplay) rateDisplay.textContent = interestRate.toFixed(1) + '%';
    if (tenureDisplay) tenureDisplay.textContent = tenureYears + ' Years';

    const resEligibleAmount = document.getElementById('resEligibleAmount');
    const resEligibleEMI = document.getElementById('resEligibleEMI');
    const resFOIRInfo = document.getElementById('resFOIRInfo');

    // Assume 50% FOIR (Fixed Obligation to Income Ratio) for standard lending
    let foir = 0.50; 
    let maxAllowedEMI = income * foir;
    let disposableEMI = maxAllowedEMI - existingEmi;

    if (disposableEMI <= 0) {
      if (resEligibleAmount) resEligibleAmount.textContent = '₹0 (Not Eligible)';
      if (resEligibleEMI) resEligibleEMI.textContent = '₹0';
      if (resFOIRInfo) resFOIRInfo.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill"></i> Your existing debt obligations exceed 50% of your gross monthly income.</span>';
      return;
    }

    let monthlyRate = (interestRate / 12) / 100;
    let months = tenureYears * 12;

    // Calculate maximum loan based on disposable EMI: 
    // Loan = EMI / [ (r * (1+r)^n) / ((1+r)^n - 1) ]
    let factor = (monthlyRate * Math.pow(1 + monthlyRate, months)) / (Math.pow(1 + monthlyRate, months) - 1);
    let maxLoan = disposableEMI / factor;

    if (resEligibleAmount) {
      resEligibleAmount.textContent = formatINR(Math.round(maxLoan));
    }
    if (resEligibleEMI) {
      resEligibleEMI.textContent = '₹' + formatNumber(Math.round(disposableEMI)) + ' / Month';
    }
    if (resFOIRInfo) {
      resFOIRInfo.innerHTML = `<span class="text-success"><i class="bi bi-check-circle-fill"></i> Eligible for a fresh EMI up to <strong>₹${formatNumber(Math.round(disposableEMI))}</strong> (Based on a 50% debt-to-income cap).</span>`;
    }
  }

  incomeInput.addEventListener('input', calculateEligibility);
  incomeInput.addEventListener('change', calculateEligibility);
  
  if (emiInput) {
    emiInput.addEventListener('input', calculateEligibility);
    emiInput.addEventListener('change', calculateEligibility);
  }
  
  if (rateInput) {
    rateInput.addEventListener('input', calculateEligibility);
    rateInput.addEventListener('change', calculateEligibility);
  }
  
  if (tenureInput) {
    tenureInput.addEventListener('input', calculateEligibility);
    tenureInput.addEventListener('change', calculateEligibility);
  }

  window.setElIncome = function(amt) {
    incomeInput.value = amt;
    calculateEligibility();
  };

  calculateEligibility();
}

// ==========================================
// 4. AI CHATBOT WIDGET ACTIONS
// ==========================================
let chatHistoryLogs = [];

function initChatbot() {
  const win = document.getElementById('chatbotWindow');
  const chatInput = document.getElementById('chatbotInput');
  const chatSendBtn = document.getElementById('chatbotSendBtn');
  const chatCloseBtn = document.getElementById('chatbotCloseBtn');
  const floatingBtn = document.getElementById('floatingChatBtn');
  const exportBtn = document.getElementById('exportChatBtn');

  if (!win) return;

  if (floatingBtn) {
    floatingBtn.addEventListener('click', (e) => {
      e.preventDefault();
      toggleChatbot();
    });
  }

  if (chatCloseBtn) {
    chatCloseBtn.addEventListener('click', closeChatbot);
  }

  if (chatSendBtn) {
    chatSendBtn.addEventListener('click', userSendChatMessage);
  }

  if (chatInput) {
    chatInput.addEventListener('keypress', handleChatKeyPress);
  }

  if (exportBtn) {
    exportBtn.addEventListener('click', sendChatToWhatsApp);
  }

  // Auto Greeting
  setTimeout(() => {
    if (chatHistoryLogs.length === 0) {
      openChatbot();
    }
  }, 1500);
}

function openChatbot() {
  const win = document.getElementById('chatbotWindow');
  if (win) {
    win.style.display = 'flex';
    win.classList.add('active');
    
    // Add initial bot greeting if empty
    const body = document.getElementById('chatbotBody');
    if (body && body.children.length === 0) {
      addBotMessage("👋 Hello! Welcome to CREDIFIX Loan Advisory.<br><br>I can assist you with:<br>• <strong>Home Loans</strong> & Mortgage (LAP)<br>• <strong>Business Loans</strong> (MSME)<br>• <strong>Private Funding</strong> (Low CIBIL)<br>• GST & Income Tax Filing", [
        "Check Loan Eligibility",
        "Private Funding (Low CIBIL)",
        "Speak to CEO Sai Krishna J"
      ]);
    }
  }
}

function closeChatbot() {
  const win = document.getElementById('chatbotWindow');
  if (win) {
    win.style.display = 'none';
    win.classList.remove('active');
  }
}

function toggleChatbot() {
  const win = document.getElementById('chatbotWindow');
  if (win) {
    if (win.style.display === 'flex' || win.classList.contains('active')) {
      closeChatbot();
    } else {
      openChatbot();
    }
  }
}

function formatFormattedText(text) {
  return text.split('**').map((item, idx) => {
    return idx % 2 === 1 ? '<strong>' + item + '</strong>' : item;
  }).join('');
}

function addBotMessage(text, options) {
  const body = document.getElementById('chatbotBody');
  if (!body) return;

  chatHistoryLogs.push({ sender: 'bot', text: text });

  const bubble = document.createElement('div');
  bubble.className = 'chat-bubble chat-bubble-bot';
  bubble.innerHTML = formatFormattedText(text);

  if (options && options.length > 0) {
    const optsDiv = document.createElement('div');
    optsDiv.className = 'chat-options-container';
    options.forEach(opt => {
      const pill = document.createElement('div');
      pill.className = 'chat-option-pill';
      pill.textContent = opt;
      pill.onclick = () => { selectChatOption(opt); };
      optsDiv.appendChild(pill);
    });
    bubble.appendChild(optsDiv);
  }

  body.appendChild(bubble);
  body.scrollTop = body.scrollHeight;
}

function addUserMessage(text) {
  const body = document.getElementById('chatbotBody');
  if (!body) return;

  chatHistoryLogs.push({ sender: 'user', text: text });

  const bubble = document.createElement('div');
  bubble.className = 'chat-bubble chat-bubble-user';
  bubble.textContent = text;

  body.appendChild(bubble);
  body.scrollTop = body.scrollHeight;
}

function handleChatKeyPress(e) {
  if (e.key === 'Enter' || e.keyCode === 13) {
    userSendChatMessage();
  }
}

function userSendChatMessage() {
  const input = document.getElementById('chatbotInput');
  if (!input) return;
  const msg = input.value.trim();
  if (!msg) return;

  input.value = '';
  addUserMessage(msg);
  processAIChatResponse(msg);
}

function selectChatOption(optionText) {
  addUserMessage(optionText);
  processAIChatResponse(optionText);
}

function processAIChatResponse(msg) {
  const lower = msg.toLowerCase();
  
  const prefix = window.location.pathname.includes('/services/') || 
                 window.location.pathname.includes('/calculator/') || 
                 window.location.pathname.includes('/blog/') || 
                 window.location.pathname.includes('/legal/') ? '../' : './';

  setTimeout(() => {
    if (lower.indexOf('private') !== -1 || lower.indexOf('low cibil') !== -1 || lower.indexOf('cibil') !== -1) {
      addBotMessage(`⚡ <strong>Private Funding & Low CIBIL Specialization</strong>:<br>We offer private capital and advisory to structure loans even with bank rejections or Low CIBIL score.`, [
        "Go to Private Funding Page",
        "Speak to CEO Sai Krishna J",
        "Calculate EMI"
      ]);
    } else if (lower.indexOf('private funding page') !== -1) {
      window.location.href = `${prefix}services/private-funding-low-cibil`;
    } else if (lower.indexOf('check loan eligibility') !== -1 || lower.indexOf('eligibility') !== -1) {
      addBotMessage(`📊 <strong>Eligibility Calculator</strong>:<br>You can check your borrowing limit directly on our Eligibility tool!`, [
        "Go to Eligibility Calculator",
        "EMI Calculator"
      ]);
    } else if (lower.indexOf('go to eligibility calculator') !== -1) {
      window.location.href = `${prefix}calculator/eligibility`;
    } else if (lower.indexOf('emi calculator') !== -1) {
      window.location.href = `${prefix}calculator/emi`;
    } else if (lower.indexOf('car') !== -1 || lower.indexOf('auto') !== -1 || lower.indexOf('vehicle') !== -1) {
      addBotMessage("🚗 <strong>Car Loans & Auto Finance</strong>:<br>Instant auto finance for new and pre-owned luxury or commercial vehicles.", [
        "Go to Car Loans Page",
        "Speak to CEO on WhatsApp"
      ]);
    } else if (lower.indexOf('go to car loans page') !== -1) {
      window.location.href = `${prefix}services/car-loans`;
    } else if (lower.indexOf('home') !== -1 || lower.indexOf('property') !== -1 || lower.indexOf('villa') !== -1) {
      addBotMessage("🏡 <strong>Home Loans</strong>:<br>Customized home loans for plots, luxury villas, apartments, & self-construction.", [
        "Go to Home Loans Page",
        "Eligibility Check"
      ]);
    } else if (lower.indexOf('go to home loans page') !== -1) {
      window.location.href = `${prefix}services/home-loans`;
    } else if (lower.indexOf('business') !== -1 || lower.indexOf('msme') !== -1 || lower.indexOf('working capital') !== -1) {
      addBotMessage("💼 <strong>Business & MSME Loans</strong>:<br>Collateral-free working capital, machinery, & expansion loans up to ₹500 Cr.", [
        "Go to Business Loans Page",
        "Tax & ITR Filing Support"
      ]);
    } else if (lower.indexOf('go to business loans page') !== -1) {
      window.location.href = `${prefix}services/business-msme-loans`;
    } else if (lower.indexOf('sai krishna') !== -1 || lower.indexOf('ceo') !== -1 || lower.indexOf('avp') !== -1 || lower.indexOf('speak to') !== -1) {
      addBotMessage("👨‍💼 <strong>Executive Leadership Desk</strong>:<br>CEO & AVP South India: <strong>Sai Krishna J</strong>.<br>Direct WhatsApp desk: +91 90592 97755.", [
        "📲 Speak on WhatsApp",
        "Office Location Address"
      ]);
    } else if (lower.indexOf('speak on whatsapp') !== -1 || lower.indexOf('whatsapp') !== -1) {
      window.open("https://wa.me/919059297755?text=Hello%20Credifix%2C%20I%20would%20like%20to%20enquire%20about%20a%20loan.", "_blank");
    } else if (lower.indexOf('location') !== -1 || lower.indexOf('address') !== -1 || lower.indexOf('office') !== -1 || lower.indexOf('madhapur') !== -1) {
      addBotMessage("📍 <strong>Madhapur Head Office</strong>:<br>4th Floor, Plot No. 50, Sai Dham, Cyberhills Colony, Madhapur, Hyderabad - 500081.", [
        "Get Direct Call",
        "Export Chat Transcript"
      ]);
    } else {
      addBotMessage("Thank you for reaching out! Our expert advisor team is ready to structure your loan. Tap below to export this conversation directly to our executive desk on WhatsApp.", [
        "📲 Export Chat Transcript to WhatsApp",
        "Speak to CEO Sai Krishna J"
      ]);
    }
  }, 400);
}

function sendChatToWhatsApp() {
  if (chatHistoryLogs.length === 0) {
    alert("Please type a message first before exporting chat history.");
    return;
  }

  const lines = ["*CREDIFIX AI CHATBOT TRANSCRIPT*", "-----------------------------------"];
  
  chatHistoryLogs.forEach(item => {
    const role = item.sender === 'user' ? '👤 User' : '🤖 Assistant';
    const cleanText = item.text.replace(/<[^>]*>?/gm, '');
    lines.push(role + ": " + cleanText);
  });

  lines.push("-----------------------------------");
  lines.push("📍 *Source*: Website Live Chatbot");
  lines.push("⏰ *Time*: " + new Date().toLocaleString('en-IN'));

  const summary = lines.join(String.fromCharCode(10));
  const waURL = "https://wa.me/919059297755?text=" + encodeURIComponent(summary);
  window.open(waURL, '_blank');
}

// Global Formats Helpers
function formatINR(num) {
  if (num >= 10000000) {
    return '₹' + (num / 10000000).toFixed(2) + ' Cr';
  } else if (num >= 100000) {
    return '₹' + (num / 100000).toFixed(1) + ' Lakh';
  } else {
    return '₹' + num.toLocaleString('en-IN');
  }
}

function formatNumber(num) {
  return num.toLocaleString('en-IN');
}
