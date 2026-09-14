// assets/js/app.js - Main Application Logic with WhatsApp Report Integration

const STAGES = [
    'Documents Pending',
    'Documents Collected',
    'Bank Transferred (Waiting for Login)',
    'Login with Documents',
    'Waiting for Sanction',
    'Sanction',
    'Completed',
    'On Hold',
    'Next Month Lead'
];

let currentClients = [];
let activeFilter = 'All';

document.addEventListener('DOMContentLoaded', () => {
    loadClients();
    setupEventListeners();
});

function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                loadClients();
            }, 300);
        });
    }

    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', (e) => {
            activeFilter = e.target.value;
            loadClients();
        });
    }

    const clientForm = document.getElementById('clientForm');
    if (clientForm) {
        clientForm.addEventListener('submit', handleFormSubmit);
    }

    const btnExport = document.getElementById('btnExport');
    if (btnExport) {
        btnExport.addEventListener('click', () => {
            ExcelHandler.exportToExcel(currentClients);
        });
    }

    const excelFileInput = document.getElementById('excelFileInput');
    if (excelFileInput) {
        excelFileInput.addEventListener('change', handleExcelImport);
    }
}

// Fetch clients & stats from backend API
async function loadClients() {
    const search = document.getElementById('searchInput')?.value.trim() || '';
    const status = activeFilter || 'All';

    try {
        const response = await fetch(`api.php?action=list&search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}`);
        const data = await response.json();

        if (data.success) {
            currentClients = data.clients;
            renderCounts(data.counts);
            renderTable(data.clients);
        } else {
            console.error('API Error:', data.error);
        }
    } catch (err) {
        console.error('Fetch Error:', err);
    }
}

// Toggle Table Visibility
function toggleTableVisibility() {
    const wrapper = document.getElementById('tableWrapper');
    const btn = document.getElementById('btnToggleTable');
    if (!wrapper || !btn) return;

    if (wrapper.classList.contains('hidden')) {
        wrapper.classList.remove('hidden');
        btn.innerHTML = '<i class="fas fa-eye-slash mr-1.5"></i> Hide Client List Table';
    } else {
        wrapper.classList.add('hidden');
        btn.innerHTML = '<i class="fas fa-eye mr-1.5"></i> Show Client List Table';
    }
}

// Render Header Counter Cards & KPI Metrics
function renderCounts(counts) {
    const kpiTotal = document.getElementById('kpiTotal');
    const kpiStage1 = document.getElementById('kpiStage1');
    const kpiBankProc = document.getElementById('kpiBankProc');
    const kpiSanctionWait = document.getElementById('kpiSanctionWait');
    const kpiSanctioned = document.getElementById('kpiSanctioned');

    if (kpiTotal) kpiTotal.innerText = counts.Total || 0;
    if (kpiStage1) kpiStage1.innerText = counts['Documents Pending'] || 0;
    if (kpiBankProc) {
        const bankCount = (counts['Bank Transferred (Waiting for Login)'] || 0) + (counts['Login with Documents'] || 0);
        kpiBankProc.innerText = bankCount;
    }
    if (kpiSanctionWait) {
        kpiSanctionWait.innerText = counts['Waiting for Sanction'] || 0;
    }
    if (kpiSanctioned) {
        const sanctionCount = (counts['Sanction'] || 0) + (counts['Completed'] || 0);
        kpiSanctioned.innerText = sanctionCount;
    }

    const container = document.getElementById('countsContainer');
    if (!container) return;

    let html = '';

    STAGES.forEach((stage, idx) => {
        const count = counts[stage] || 0;
        const isActive = activeFilter === stage;
        const badgeClass = `badge-stage-${idx + 1}`;

        html += `
            <div onclick="filterByCard('${stage}')" class="glass-panel p-5 rounded-2xl cursor-pointer transition ${isActive ? 'ring-2 ring-emerald-400 border-emerald-400' : 'hover:border-emerald-500/50'}">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-extrabold px-2.5 py-0.5 rounded-full ${badgeClass}">Stage ${idx + 1}</span>
                    <span class="text-xs text-emerald-500/70 font-mono">#${idx + 1}</span>
                </div>
                <div class="text-xs font-black text-slate-200 truncate mt-3" title="${stage}">${stage}</div>
                <div class="text-3xl font-black text-white mt-1">${count}</div>
                <div class="text-[10px] text-emerald-400 font-bold mt-2 flex items-center gap-1">
                    <span>Click to view cases</span> &rarr;
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function filterByCard(stage) {
    activeFilter = stage;
    const filterSelect = document.getElementById('statusFilter');
    if (filterSelect) {
        filterSelect.value = stage;
    }
    
    // Auto expand table when stage card is clicked
    const wrapper = document.getElementById('tableWrapper');
    const btn = document.getElementById('btnToggleTable');
    if (wrapper && wrapper.classList.contains('hidden')) {
        wrapper.classList.remove('hidden');
        if (btn) btn.innerHTML = '<i class="fas fa-eye-slash mr-1.5"></i> Hide Client List Table';
    }

    loadClients();
}

// Render Clients Table
function renderTable(clients) {
    const tbody = document.getElementById('clientsTableBody');
    if (!tbody) return;

    if (!clients || clients.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                    <i class="fas fa-folder-open text-4xl text-slate-300 mb-3 block"></i>
                    <p class="text-base font-bold text-slate-700">No client records found.</p>
                    <p class="text-xs text-slate-400 mt-1">Click "+ Onboard Client" or import Excel files.</p>
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    clients.forEach((client, idx) => {
        const stageIndex = STAGES.indexOf(client.status);
        const currentStageNum = stageIndex >= 0 ? stageIndex + 1 : 1;
        const badgeClass = `badge-stage-${currentStageNum}`;
        const formatAmount = client.amount ? '₹' + Number(client.amount).toLocaleString('en-IN') : '-';

        // Build 8-stage quick buttons
        let statusButtonsHtml = '';
        STAGES.forEach((stage, sIdx) => {
            const isCurrent = stage === client.status;
            const btnClass = isCurrent 
                ? `btn-stage-${sIdx + 1} ring-2 ring-offset-1 ring-slate-800 font-extrabold scale-105 shadow-md`
                : `bg-slate-100 text-slate-700 hover:bg-slate-200 opacity-80 hover:opacity-100 font-semibold`;

            const shortLabel = getStageShortLabel(stage);

            statusButtonsHtml += `
                <button type="button" 
                    onclick="promptUpdateStatus(${client.id}, '${stage}', '${client.status}')" 
                    class="text-[11px] px-2.5 py-1 rounded-lg transition-all whitespace-nowrap ${btnClass}"
                    title="Change stage to: ${stage}">
                    ${isCurrent ? '<i class="fas fa-check-circle mr-1 text-xs"></i>' : ''}${shortLabel}
                </button>
            `;
        });

        // Build progress bar
        let progressDotsHtml = '';
        STAGES.forEach((stage, sIdx) => {
            const dotStep = sIdx + 1;
            let dotBg = 'bg-slate-200 text-slate-500';
            if (dotStep < currentStageNum) {
                dotBg = 'bg-emerald-500 text-white';
            } else if (dotStep === currentStageNum) {
                dotBg = 'bg-sky-600 text-white active font-bold';
            }

            progressDotsHtml += `
                <div class="stepper-dot ${dotBg}" title="${dotStep}. ${stage}">
                    ${dotStep < currentStageNum ? '✓' : dotStep}
                </div>
                ${sIdx < STAGES.length - 1 ? `<div class="h-1 flex-1 ${sIdx + 1 < currentStageNum ? 'bg-emerald-500' : 'bg-slate-200'}"></div>` : ''}
            `;
        });

        html += `
            <tr class="table-row-hover border-b border-slate-800/80">
                <td class="px-4 py-4 text-xs font-black text-emerald-400">
                    #${String(client.id).padStart(4, '0')}
                </td>
                <td class="px-4 py-4">
                    <div class="font-extrabold text-white text-base tracking-wide">${escapeHtml(client.name)}</div>
                    <div class="text-xs text-slate-300 flex items-center gap-2 mt-1">
                        ${client.phone ? `<span><i class="fas fa-phone-alt text-emerald-400/80 mr-1"></i>${escapeHtml(client.phone)}</span>` : ''}
                        <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 px-2 py-0.5 rounded text-[10px] font-bold">${escapeHtml(client.lead_source || 'Marketing')}</span>
                    </div>
                </td>
                <td class="px-4 py-4">
                    <div class="text-xs font-bold text-slate-200">${escapeHtml(client.bank_name || '-')}</div>
                    <div class="text-xs text-emerald-400 font-black mt-0.5">${formatAmount}</div>
                </td>
                <td class="px-4 py-4 max-w-xs">
                    <div class="text-xs font-semibold text-slate-300 line-clamp-2" title="${escapeHtml(client.additional_details || '')}">
                        ${escapeHtml(client.additional_details || 'No details specified')}
                    </div>
                </td>
                <td class="px-4 py-4">
                    <div class="mb-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black ${badgeClass}">
                            <span class="w-2 h-2 rounded-full bg-current"></span>
                            ${client.status}
                        </span>
                        <span class="text-xs text-emerald-400/90 font-bold ml-1">(${currentStageNum}/9)</span>
                    </div>
                    <div class="flex items-center max-w-[200px] mt-1">
                        ${progressDotsHtml}
                    </div>
                </td>
                <td class="px-4 py-4">
                    <div class="flex flex-wrap gap-1.5 max-w-md">
                        ${statusButtonsHtml}
                    </div>
                </td>
                <td class="px-4 py-4 text-right whitespace-nowrap">
                    <button onclick="openEditModal(${client.id})" class="text-emerald-400 hover:text-emerald-300 p-1.5 rounded-lg hover:bg-emerald-500/20 transition" title="Edit Details">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="viewHistory(${client.id})" class="text-emerald-400 hover:text-emerald-300 p-1.5 rounded-lg hover:bg-emerald-500/20 transition" title="View Audit History">
                        <i class="fas fa-history"></i>
                    </button>
                    <button onclick="deleteClient(${client.id}, '${escapeJsString(client.name)}')" class="text-red-400 hover:text-red-300 p-1.5 rounded-lg hover:bg-red-500/20 transition" title="Delete Client">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

// Short labels for stage buttons
function getStageShortLabel(stage) {
    switch (stage) {
        case 'Documents Pending': return 'Docs Pending';
        case 'Documents Collected': return 'Docs Collected';
        case 'Bank Transferred (Waiting for Login)': return 'Bank Transferred';
        case 'Login with Documents': return 'Login Docs';
        case 'Waiting for Sanction': return 'Sanction Wait';
        case 'Sanction': return 'Sanction';
        case 'Completed': return 'Completed';
        case 'On Hold': return 'On Hold';
        case 'Next Month Lead': return 'Next Month';
        default: return stage;
    }
}

// Prompt status update
async function promptUpdateStatus(clientId, newStatus, currentStatus) {
    if (newStatus === currentStatus) return;

    const notes = prompt(`Advance client status from "${currentStatus}" to "${newStatus}"?\n\nOptional remark note:`, `Advanced to ${newStatus}`);
    if (notes === null) return;

    try {
        const response = await fetch('api.php?action=update_status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                client_id: clientId,
                status: newStatus,
                notes: notes
            })
        });
        const res = await response.json();

        if (res.success) {
            showNotification(res.message, 'success');
            loadClients();
        } else {
            alert('Error: ' + res.error);
        }
    } catch (err) {
        console.error('Update status error:', err);
    }
}

// WhatsApp Daily Report Handlers
async function openWhatsAppModal() {
    const modal = document.getElementById('whatsappModal');
    const previewArea = document.getElementById('whatsappReportPreview');

    try {
        const res = await fetch('whatsapp_report.php');
        const data = await res.json();

        if (data.success && data.report_text) {
            previewArea.value = data.report_text;
        } else {
            previewArea.value = 'Failed to generate report text.';
        }
    } catch (err) {
        console.error('Fetch whatsapp report error:', err);
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeWhatsAppModal() {
    const modal = document.getElementById('whatsappModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function copyWhatsAppText() {
    const previewArea = document.getElementById('whatsappReportPreview');
    if (!previewArea || !previewArea.value) return;

    navigator.clipboard.writeText(previewArea.value).then(() => {
        showNotification('WhatsApp report copied to clipboard!', 'success');
    }).catch(err => {
        alert('Copy failed: ' + err.message);
    });
}

async function sendWhatsAppReport() {
    const phoneInput = document.getElementById('waPhone');
    const phone = phoneInput ? phoneInput.value.trim() : '';

    if (!phone) {
        alert('Please enter recipient WhatsApp phone number with country code (e.g. 919876543210).');
        return;
    }

    try {
        const res = await fetch('whatsapp_report.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                phone: phone
            })
        });
        const data = await res.json();

        if (data.success) {
            showNotification('WhatsApp Report Dispatched via tehub.in API!', 'success');
            closeWhatsAppModal();
        } else {
            alert('WhatsApp Error: ' + (data.error || (data.api_response ? JSON.stringify(data.api_response) : 'Failed to send message')));
        }
    } catch (err) {
        console.error('WhatsApp send error:', err);
        alert('Network Error sending WhatsApp report: ' + err.message);
    }
}

// Open modal for Adding or Editing Client
function openClientModal(client = null) {
    const modal = document.getElementById('clientModal');
    const modalTitle = document.getElementById('modalTitle');
    const form = document.getElementById('clientForm');

    form.reset();

    if (client) {
        modalTitle.innerText = 'Edit Client Details';
        document.getElementById('clientId').value = client.id;
        document.getElementById('inputName').value = client.name || '';
        document.getElementById('inputPhone').value = client.phone || '';
        document.getElementById('inputEmail').value = client.email || '';
        document.getElementById('inputBank').value = client.bank_name || '';
        document.getElementById('inputAmount').value = client.amount || '';
        if (document.getElementById('inputLeadSource')) {
            document.getElementById('inputLeadSource').value = client.lead_source || 'Marketing Campaign';
        }
        document.getElementById('inputDetails').value = client.additional_details || '';
        document.getElementById('statusSelectContainer').classList.add('hidden');
    } else {
        modalTitle.innerText = 'Onboard Interested Marketing Client';
        document.getElementById('clientId').value = '';
        if (document.getElementById('inputLeadSource')) {
            document.getElementById('inputLeadSource').value = 'Marketing Campaign';
        }
        document.getElementById('statusSelectContainer').classList.remove('hidden');
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeClientModal() {
    const modal = document.getElementById('clientModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function openEditModal(id) {
    const client = currentClients.find(c => c.id === id);
    if (client) {
        openClientModal(client);
    }
}

// Form Submit Handler
async function handleFormSubmit(e) {
    e.preventDefault();

    const id = document.getElementById('clientId').value;
    const action = id ? 'update_client' : 'create';

    const payload = {
        id: id ? parseInt(id) : undefined,
        name: document.getElementById('inputName').value.trim(),
        phone: document.getElementById('inputPhone').value.trim(),
        email: document.getElementById('inputEmail').value.trim(),
        bank_name: document.getElementById('inputBank').value.trim(),
        amount: parseFloat(document.getElementById('inputAmount').value) || 0,
        lead_source: document.getElementById('inputLeadSource')?.value || 'Marketing Campaign',
        additional_details: document.getElementById('inputDetails').value.trim(),
        status: document.getElementById('inputInitialStatus')?.value || 'Documents Collect'
    };

    try {
        const response = await fetch(`api.php?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const res = await response.json();

        if (res.success) {
            closeClientModal();
            showNotification(res.message, 'success');
            loadClients();
        } else {
            alert('Error: ' + res.error);
        }
    } catch (err) {
        console.error('Save error:', err);
    }
}

// Delete Client
async function deleteClient(id, name) {
    if (!confirm(`Are you sure you want to delete client "${name}"? This action cannot be undone.`)) {
        return;
    }

    try {
        const response = await fetch('api.php?action=delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        const res = await response.json();

        if (res.success) {
            showNotification(res.message, 'success');
            loadClients();
        } else {
            alert('Error: ' + res.error);
        }
    } catch (err) {
        console.error('Delete error:', err);
    }
}

// View Status History Modal
async function viewHistory(clientId) {
    try {
        const response = await fetch(`api.php?action=get_history&client_id=${clientId}`);
        const res = await response.json();

        if (res.success) {
            const clientNameElem = document.getElementById('historyClientName');
            const historyBody = document.getElementById('historyBody');

            clientNameElem.innerText = res.client ? res.client.name : 'Client History';

            if (!res.history || res.history.length === 0) {
                historyBody.innerHTML = '<p class="text-xs text-slate-500 text-center py-4">No history records found.</p>';
            } else {
                let html = '<div class="space-y-4 relative border-l-2 border-sky-200 ml-3 pl-4 py-2">';
                res.history.forEach(log => {
                    html += `
                        <div class="relative">
                            <span class="absolute -left-[23px] top-1.5 w-3 h-3 rounded-full bg-sky-600 border-2 border-white"></span>
                            <div class="text-[10px] text-slate-400 font-mono">${log.created_at}</div>
                            <div class="text-xs font-bold text-slate-800 mt-0.5">
                                ${log.previous_status ? `<span class="text-slate-500 font-normal">${log.previous_status}</span> &rarr; ` : ''}
                                <span class="text-sky-700 font-extrabold">${log.new_status}</span>
                            </div>
                            ${log.notes ? `<div class="text-xs text-slate-600 bg-slate-50 p-2 rounded-lg border border-slate-200 mt-1">${escapeHtml(log.notes)}</div>` : ''}
                        </div>
                    `;
                });
                html += '</div>';
                historyBody.innerHTML = html;
            }

            const modal = document.getElementById('historyModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        } else {
            alert('Error: ' + res.error);
        }
    } catch (err) {
        console.error('History fetch error:', err);
    }
}

function closeHistoryModal() {
    const modal = document.getElementById('historyModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Excel Import Handler
async function handleExcelImport(e) {
    const file = e.target.files[0];
    if (!file) return;

    try {
        const rows = await ExcelHandler.parseExcelFile(file);
        if (rows.length === 0) {
            alert('The selected Excel file is empty.');
            return;
        }

        if (confirm(`Import ${rows.length} client records from Excel file "${file.name}"?`)) {
            const response = await fetch('api.php?action=bulk_import', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ rows: rows })
            });
            const res = await response.json();

            if (res.success) {
                showNotification(res.message, 'success');
                loadClients();
            } else {
                alert('Import Failed: ' + res.error);
            }
        }
    } catch (err) {
        alert('Error reading Excel file: ' + err.message);
    } finally {
        e.target.value = '';
    }
}

// Toast helper
function showNotification(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-5 right-5 z-50 px-4 py-3 rounded-xl shadow-xl text-white font-bold text-xs transition-all duration-300 ${type === 'success' ? 'bg-emerald-600' : 'bg-red-600'}`;
    toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2 text-sm"></i>${escapeHtml(message)}`;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Helpers
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function escapeJsString(str) {
    if (!str) return '';
    return String(str).replace(/'/g, "\\'").replace(/"/g, '\\"');
}
