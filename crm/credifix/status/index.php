<?php
// index.php - Credfix.in Black & Glassmorphic Emerald Green Operations Dashboard
require_once __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credfix.in - Glassmorphic Counter Dashboard</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- FontAwesome 6 Icons CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SheetJS CDN for Excel Export/Import -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    
    <!-- Custom Glassmorphic Black & Green CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="min-h-screen text-slate-100">

    <!-- Glassmorphic Navigation Header -->
    <header class="glass-panel-header text-white sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col md:flex-row items-center justify-between gap-4">
            
            <!-- Brand & Title -->
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-2xl glass-btn-primary flex items-center justify-center text-white font-extrabold text-2xl tracking-wider shadow-lg shadow-emerald-500/30">
                    <i class="fas fa-shield-alt text-white"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-2xl font-black tracking-tight text-white">CREDFIX<span class="text-emerald-400">.IN</span></span>
                        <span class="bg-emerald-500/20 text-emerald-300 text-[11px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider border border-emerald-500/40">Glass Counter Dashboard</span>
                    </div>
                    <p class="text-xs text-emerald-300/80 font-medium mt-0.5">Marketing Lead Generation & Stage Execution Pipeline</p>
                </div>
            </div>

            <!-- Glassmorphic Toolbar Buttons -->
            <div class="flex items-center space-x-2.5 w-full md:w-auto justify-end flex-wrap gap-y-2">
                
                <!-- Send WhatsApp Report Button -->
                <button onclick="openWhatsAppModal()" class="glass-btn px-4 py-2.5 text-xs font-black rounded-xl">
                    <i class="fab fa-whatsapp mr-2 text-base text-emerald-400"></i> WhatsApp Report
                </button>

                <button onclick="document.getElementById('excelFileInput').click()" class="glass-btn px-3.5 py-2.5 text-xs font-bold rounded-xl">
                    <i class="fas fa-file-import mr-2 text-emerald-400"></i> Import Excel
                </button>
                <input type="file" id="excelFileInput" accept=".xlsx, .xls, .csv" class="hidden">

                <button id="btnExport" class="glass-btn px-3.5 py-2.5 text-xs font-bold rounded-xl">
                    <i class="fas fa-file-excel mr-2 text-emerald-400"></i> Export Excel
                </button>

                <button onclick="openClientModal()" class="glass-btn-primary px-4.5 py-2.5 text-xs font-black rounded-xl">
                    <i class="fas fa-plus-circle mr-2 text-sm"></i> Onboard Client
                </button>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- Executive KPI Hero Cards (5 Glass Cards Grid) -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            
            <!-- Total Onboarded -->
            <div class="glass-panel p-5 rounded-2xl">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-black text-emerald-400/90 uppercase tracking-wider">Total Onboarded</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold border border-emerald-500/40">
                        <i class="fas fa-users text-sm"></i>
                    </div>
                </div>
                <div id="kpiTotal" class="text-3xl font-black text-white mt-2.5">0</div>
                <div class="text-[11px] text-emerald-400 font-bold mt-1">Total Marketing Leads</div>
            </div>

            <!-- Stage 1 Docs Pending Card -->
            <div onclick="filterByCard('Documents Pending')" class="glass-panel p-5 rounded-2xl cursor-pointer hover:border-amber-400/60 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-black text-amber-400 uppercase tracking-wider">Docs Pending</span>
                    <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold border border-amber-500/40">
                        <i class="fas fa-bullhorn text-sm"></i>
                    </div>
                </div>
                <div id="kpiStage1" class="text-3xl font-black text-amber-400 mt-2.5">0</div>
                <div class="text-[11px] text-amber-400/90 font-semibold mt-1">Docs Not Collected Yet</div>
            </div>

            <!-- Bank Processing -->
            <div onclick="filterByCard('Bank Transferred (Waiting for Login)')" class="glass-panel p-5 rounded-2xl cursor-pointer hover:border-sky-400/60 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-black text-sky-400 uppercase tracking-wider">In Bank Processing</span>
                    <div class="w-9 h-9 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center font-bold border border-sky-500/40">
                        <i class="fas fa-university text-sm"></i>
                    </div>
                </div>
                <div id="kpiBankProc" class="text-3xl font-black text-sky-400 mt-2.5">0</div>
                <div class="text-[11px] text-sky-400/90 font-semibold mt-1">Bank Transfer & Login</div>
            </div>

            <!-- Waiting for Sanction Card -->
            <div onclick="filterByCard('Waiting for Sanction')" class="glass-panel p-5 rounded-2xl cursor-pointer hover:border-yellow-400/60 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-black text-yellow-400 uppercase tracking-wider">Waiting for Sanction</span>
                    <div class="w-9 h-9 rounded-xl bg-yellow-500/20 text-yellow-400 flex items-center justify-center font-bold border border-yellow-500/40">
                        <i class="fas fa-hourglass-half text-sm"></i>
                    </div>
                </div>
                <div id="kpiSanctionWait" class="text-3xl font-black text-yellow-400 mt-2.5">0</div>
                <div class="text-[11px] text-yellow-400/90 font-semibold mt-1">Under Approval Review</div>
            </div>

            <!-- Sanctioned & Done -->
            <div onclick="filterByCard('Completed')" class="glass-panel p-5 rounded-2xl cursor-pointer hover:border-emerald-400/60 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-black text-emerald-400 uppercase tracking-wider">Sanctioned & Done</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold border border-emerald-500/40">
                        <i class="fas fa-award text-sm"></i>
                    </div>
                </div>
                <div id="kpiSanctioned" class="text-3xl font-black text-emerald-400 mt-2.5">0</div>
                <div class="text-[11px] text-emerald-400/90 font-semibold mt-1">Sanction & Completed</div>
            </div>
        </section>

        <!-- Glassmorphic Stage Counter Grid -->
        <section>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-black text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-layer-group"></i> 8-Stage Execution Counter Command Center
                </h2>
                <span class="text-xs text-slate-400 font-medium">Click any card to filter</span>
            </div>

            <div id="countsContainer" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Dynamically populated 8 glass stage cards by app.js -->
            </div>
        </section>

        <!-- Collapsible Client Management Table -->
        <section class="glass-panel rounded-2xl overflow-hidden shadow-xl">
            
            <div class="p-4 sm:p-5 flex items-center justify-between border-b border-emerald-500/20 bg-slate-950/80">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-list-check text-emerald-400 text-lg"></i>
                    <div>
                        <h3 class="text-sm font-black text-white">Client Management Table</h3>
                        <p class="text-xs text-slate-400">View or update individual client stage details</p>
                    </div>
                </div>

                <button onclick="toggleTableVisibility()" id="btnToggleTable" class="glass-btn px-4 py-2 text-xs font-black rounded-xl">
                    <i class="fas fa-eye mr-1.5"></i> Show Client List Table
                </button>
            </div>

            <!-- Table Container -->
            <div id="tableWrapper" class="hidden">
                <!-- Filters -->
                <div class="p-4 bg-slate-950/60 border-b border-emerald-500/20 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="relative w-full sm:w-80">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-emerald-500/70">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" id="searchInput" placeholder="Search by name, bank, ID..." class="w-full pl-9 pr-4 py-2 bg-slate-900 text-xs font-semibold rounded-xl border border-emerald-500/30 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="flex items-center space-x-2">
                        <label for="statusFilter" class="text-xs font-bold text-emerald-400">Stage Filter:</label>
                        <select id="statusFilter" class="bg-slate-900 border border-emerald-500/30 text-xs font-bold rounded-xl px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="All">All Workflow Stages</option>
                            <?php foreach ($STAGES as $idx => $stage): ?>
                                <option value="<?= htmlspecialchars($stage) ?>">Stage <?= $idx + 1 ?>: <?= htmlspecialchars($stage) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-950/90 border-b border-emerald-500/20 text-[11px] font-black text-emerald-400 uppercase tracking-wider">
                                <th class="px-4 py-4">Ref ID</th>
                                <th class="px-4 py-4">Client Name</th>
                                <th class="px-4 py-4">Bank & Amount</th>
                                <th class="px-4 py-4">Details / Reference</th>
                                <th class="px-4 py-4">Current Stage</th>
                                <th class="px-4 py-4">Click Stage Button to Update Status</th>
                                <th class="px-4 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="clientsTableBody" class="divide-y divide-slate-800/80 bg-slate-950/40 text-white">
                            <!-- Rendered by app.js -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <!-- Modal: WhatsApp Daily Report -->
    <div id="whatsappModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden items-center justify-center p-4">
        <div class="glass-panel rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-emerald-500/40">
            
            <div class="px-6 py-4 bg-slate-950 text-white flex items-center justify-between border-b border-emerald-500/30">
                <div class="flex items-center space-x-2.5">
                    <i class="fab fa-whatsapp text-emerald-400 text-xl"></i>
                    <div>
                        <h3 class="text-sm font-black text-white">Send WhatsApp Daily Report</h3>
                        <p class="text-[11px] text-emerald-300/90 font-medium">Powered by 2fa.tehub.in WhatsApp API</p>
                    </div>
                </div>
                <button onclick="closeWhatsAppModal()" class="text-slate-400 hover:text-white p-1">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-emerald-300 mb-1">Generated WhatsApp Message Preview</label>
                    <textarea id="whatsappReportPreview" rows="8" readonly class="w-full text-xs font-mono bg-slate-950/90 p-3 rounded-xl border border-emerald-500/30 text-emerald-300 focus:outline-none"></textarea>
                </div>

                <div class="bg-slate-950/80 p-4 rounded-xl border border-emerald-500/30 space-y-3">
                    <div class="text-xs font-black text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-mobile-alt"></i> Recipient Mobile Configuration
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 mb-1">WhatsApp Mobile Number (With Country Code)</label>
                        <input type="text" id="waPhone" placeholder="e.g. 919876543210" value="91" class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-emerald-500/40 bg-slate-900 text-white focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-emerald-500/20">
                    <button type="button" onclick="copyWhatsAppText()" class="glass-btn px-3.5 py-2 text-xs font-bold rounded-xl">
                        <i class="fas fa-copy mr-1.5"></i> Copy Text
                    </button>

                    <div class="flex items-center space-x-2">
                        <button type="button" onclick="closeWhatsAppModal()" class="px-4 py-2 text-xs font-bold text-slate-400 hover:bg-slate-800 rounded-xl transition">Cancel</button>
                        <button type="button" onclick="sendWhatsAppReport()" class="glass-btn-primary px-4 py-2.5 text-xs font-black rounded-xl">
                            <i class="fab fa-whatsapp mr-1.5"></i> Send WhatsApp Report Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Add / Edit Client -->
    <div id="clientModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden items-center justify-center p-4">
        <div class="glass-panel rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-emerald-500/40">
            
            <div class="px-6 py-4 bg-slate-950 text-white flex items-center justify-between border-b border-emerald-500/30">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-user-plus text-emerald-400"></i>
                    <h3 id="modalTitle" class="text-sm font-black text-white">Onboard Interested Marketing Client</h3>
                </div>
                <button onclick="closeClientModal()" class="text-slate-400 hover:text-white p-1">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="clientForm" class="p-6 space-y-4">
                <input type="hidden" id="clientId">

                <div>
                    <label class="block text-xs font-bold text-emerald-300 mb-1">Interested Client Name <span class="text-red-400">*</span></label>
                    <input type="text" id="inputName" required placeholder="e.g. Prabhakar Reddy sir" class="w-full text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-emerald-500/40 bg-slate-950 text-white focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-emerald-300 mb-1">Phone Number</label>
                        <input type="text" id="inputPhone" placeholder="+91 9876543210" class="w-full text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-emerald-500/40 bg-slate-950 text-white focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-emerald-300 mb-1">Lead Source</label>
                        <select id="inputLeadSource" class="w-full text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-emerald-500/40 bg-slate-950 text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="Marketing Campaign">Marketing Campaign</option>
                            <option value="Direct Referral">Direct Referral</option>
                            <option value="Social Media Ads">Social Media Ads</option>
                            <option value="Telecalling">Telecalling</option>
                            <option value="Walk-in Client">Walk-in Client</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-emerald-300 mb-1">Bank Name</label>
                        <input type="text" id="inputBank" placeholder="e.g. HDFC / ICICI / SBI" class="w-full text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-emerald-500/40 bg-slate-950 text-white focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-emerald-300 mb-1">Loan Amount Required (₹)</label>
                        <input type="number" id="inputAmount" placeholder="500000" class="w-full text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-emerald-500/40 bg-slate-950 text-white focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div id="statusSelectContainer">
                    <label class="block text-xs font-bold text-emerald-300 mb-1">Initial Stage (Starts at Stage 1)</label>
                    <select id="inputInitialStatus" class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-emerald-500/40 bg-slate-950 text-white focus:ring-2 focus:ring-emerald-500">
                        <?php foreach ($STAGES as $idx => $stage): ?>
                            <option value="<?= htmlspecialchars($stage) ?>">Stage <?= $idx + 1 ?>: <?= htmlspecialchars($stage) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-emerald-300 mb-1">Additional Details / Reference</label>
                    <textarea id="inputDetails" rows="3" placeholder="Enter reference details, city/location, loan type notes..." class="w-full text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-emerald-500/40 bg-slate-950 text-white focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="pt-3 border-t border-emerald-500/20 flex items-center justify-end space-x-3">
                    <button type="button" onclick="closeClientModal()" class="px-4 py-2 text-xs font-bold text-slate-400 hover:bg-slate-800 rounded-xl transition">Cancel</button>
                    <button type="submit" class="glass-btn-primary px-4 py-2 text-xs font-black rounded-xl">Onboard Client</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: View Status Log History -->
    <div id="historyModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden items-center justify-center p-4">
        <div class="glass-panel rounded-2xl max-w-md w-full overflow-hidden shadow-2xl border border-emerald-500/40">
            
            <div class="px-6 py-4 bg-slate-950 text-white flex items-center justify-between border-b border-emerald-500/30">
                <div>
                    <h3 class="text-sm font-black text-white flex items-center gap-2">
                        <i class="fas fa-history text-emerald-400"></i> Credfix Status Audit History
                    </h3>
                    <p id="historyClientName" class="text-xs text-emerald-400 font-bold mt-0.5"></p>
                </div>
                <button onclick="closeHistoryModal()" class="text-slate-400 hover:text-white p-1">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div id="historyBody" class="p-6 max-h-96 overflow-y-auto custom-scrollbar">
                <!-- Dynamic status log entries -->
            </div>

            <div class="px-6 py-3 bg-slate-950 border-t border-emerald-500/20 text-right">
                <button onclick="closeHistoryModal()" class="glass-btn px-4 py-1.5 text-xs font-bold rounded-xl">Close</button>
            </div>
        </div>
    </div>

    <!-- App Scripts -->
    <script src="assets/js/excel.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
