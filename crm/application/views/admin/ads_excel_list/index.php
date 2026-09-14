<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (!isset($is_ajax) || !$is_ajax) { init_head(); } ?>
<style>
/* Hide hero banner when loaded dynamically in the Leads all category page */
#excel-leads-content .ael-hero {
    display: none;
}
#excel-leads-content .content-inner {
    padding: 0 !important;
}
#excel-leads-content #wrapper {
    margin: 0 !important;
}
/* ─── Root Tokens ──────────────────────────────────────────────── */
:root {
    --ael-green-start : #107C41;
    --ael-green-end   : #1D6F42;
    --ael-teal-start  : #00b09b;
    --ael-teal-end    : #128C7E;
    --ael-blue-start  : #1a73e8;
    --ael-blue-end    : #0d47a1;
    --ael-orange-start: #ff6d00;
    --ael-orange-end  : #e65100;
    --ael-purple-start: #7b2ff7;
    --ael-purple-end  : #4c1d95;
    --ael-radius      : 14px;
    --ael-shadow      : 0 4px 24px rgba(0,0,0,.10);
}

/* ─── Page Header ──────────────────────────────────────────────── */
.ael-hero {
    background: linear-gradient(135deg, var(--ael-green-start) 0%, var(--ael-green-end) 100%);
    border-radius: var(--ael-radius);
    color: #fff;
    padding: 28px 32px;
    margin-bottom: 26px;
    box-shadow: 0 6px 32px rgba(16,124,65,.30);
    position: relative;
    overflow: hidden;
}
.ael-hero::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 200px; height: 200px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.ael-hero::after {
    content: '';
    position: absolute;
    bottom: -60px; left: 40%;
    width: 280px; height: 280px;
    background: rgba(255,255,255,.04);
    border-radius: 50%;
}
.ael-hero-title { margin: 0 0 6px; font-size: 26px; font-weight: 700; letter-spacing: -.3px; }
.ael-hero-sub   { margin: 0; opacity: .85; font-size: 13.5px; }
.ael-hero-actions { display:flex; gap:10px; flex-wrap:wrap; }

.ael-hero-actions .btn {
    font-weight: 600;
    border-radius: 8px;
    font-size: 13px;
    padding: 8px 18px;
    transition: transform .15s, box-shadow .15s;
}
.ael-hero-actions .btn:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(0,0,0,.25); }

/* ─── Stat Cards ───────────────────────────────────────────────── */
.ael-stat-card {
    border-radius: var(--ael-radius);
    padding: 22px 24px;
    color: #fff;
    text-align: center;
    box-shadow: var(--ael-shadow);
    margin-bottom: 22px;
    position: relative;
    overflow: hidden;
    transition: transform .2s, box-shadow .2s;
}
.ael-stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 32px rgba(0,0,0,.16); }
.ael-stat-card::after {
    content: '';
    position: absolute;
    top: -20px; right: -20px;
    width: 90px; height: 90px;
    background: rgba(255,255,255,.10);
    border-radius: 50%;
}
.ael-stat-num  { font-size: 38px; font-weight: 800; line-height: 1; margin-bottom: 4px; }
.ael-stat-lbl  { font-size: 11.5px; text-transform: uppercase; letter-spacing: 1.2px; opacity: .90; }
.ael-stat-icon { font-size: 18px; margin-bottom: 6px; opacity: .80; }
.ael-g  { background: linear-gradient(135deg, var(--ael-green-start),  var(--ael-green-end)); }
.ael-t  { background: linear-gradient(135deg, var(--ael-teal-start),   var(--ael-teal-end)); }
.ael-b  { background: linear-gradient(135deg, var(--ael-blue-start),   var(--ael-blue-end)); }
.ael-o  { background: linear-gradient(135deg, var(--ael-orange-start), var(--ael-orange-end)); }
.ael-p  { background: linear-gradient(135deg, var(--ael-purple-start), var(--ael-purple-end)); }

/* ─── Toolbar ──────────────────────────────────────────────────── */
.ael-toolbar {
    background: #fff;
    border: 1px solid #e8edf3;
    border-radius: var(--ael-radius);
    padding: 14px 20px;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
}
.ael-toolbar .ael-search-wrap {
    flex: 1;
    min-width: 220px;
    position: relative;
}
.ael-toolbar .ael-search-wrap .fa {
    position: absolute;
    left: 12px; top: 50%;
    transform: translateY(-50%);
    color: #8ea0b8;
}
.ael-toolbar .ael-search-wrap input {
    padding-left: 34px;
    border-radius: 8px;
    border: 1px solid #d8e2ee;
    height: 36px;
    font-size: 13px;
    width: 100%;
    transition: border-color .2s, box-shadow .2s;
}
.ael-toolbar .ael-search-wrap input:focus {
    border-color: var(--ael-green-start);
    box-shadow: 0 0 0 3px rgba(16,124,65,.12);
    outline: none;
}
.ael-toolbar .ael-meta {
    font-size: 13px;
    color: #8395a7;
    white-space: nowrap;
    margin-left: auto;
}
.ael-toolbar .ael-meta strong { color: var(--ael-green-start); }

/* ─── Table Panel ──────────────────────────────────────────────── */
.ael-panel {
    background: #fff;
    border-radius: var(--ael-radius);
    box-shadow: var(--ael-shadow);
    border: 1px solid #e8edf3;
    overflow: hidden;
}
.ael-panel .ael-panel-header {
    padding: 14px 20px;
    border-bottom: 1px solid #eef2f7;
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fafbfc;
}
.ael-panel .ael-panel-header h5 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2d3748;
}
.ael-panel .ael-panel-header .badge-count {
    background: var(--ael-green-start);
    color: #fff;
    border-radius: 20px;
    padding: 2px 10px;
    font-size: 11px;
    font-weight: 700;
}

/* ─── Table Styles ─────────────────────────────────────────────── */
.ael-table { margin: 0 !important; }
.ael-table thead th {
    background: #f0f6f2 !important;
    font-weight: 700;
    font-size: 11.5px;
    color: var(--ael-green-start);
    white-space: nowrap;
    text-transform: uppercase;
    letter-spacing: .5px;
    border-bottom: 2px solid #d6eadc !important;
    padding: 10px 14px !important;
    vertical-align: middle !important;
}
.ael-table tbody tr { transition: background .12s; }
.ael-table tbody tr:hover { background: #f7fdf9 !important; }
.ael-table td {
    font-size: 12.5px;
    vertical-align: middle !important;
    padding: 9px 14px !important;
    max-width: 240px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #3a4a5c;
}
.ael-table td:first-child { color: #aab5c2; font-weight: 600; font-size: 11.5px; }

/* ─── Cell Badges ──────────────────────────────────────────────── */
.badge-platform {
    display: inline-flex; align-items: center; gap: 4px;
    border-radius: 5px; padding: 3px 8px;
    font-weight: 700; font-size: 11px; letter-spacing: .4px;
}
.badge-platform.fb  { background: #e8f0fe; color: #1a73e8; }
.badge-platform.ig  { background: #fce4ec; color: #c62828; }
.badge-platform.gg  { background: #e8f5e9; color: #2e7d32; }
.badge-platform.def { background: #f3e5f5; color: #6a1b9a; }

.badge-status {
    display: inline-block;
    border-radius: 20px; padding: 3px 10px;
    font-weight: 600; font-size: 11px;
}
.badge-status.created   { background: #e3f2fd; color: #1565c0; }
.badge-status.converted { background: #e8f5e9; color: #2e7d32; }
.badge-status.junk      { background: #fce4ec; color: #b71c1c; }
.badge-status.default   { background: #f5f5f5; color: #616161; }

.badge-organic {
    display: inline-block;
    border-radius: 4px; padding: 2px 7px;
    font-size: 11px; font-weight: 600;
}
.badge-organic.yes { background: #e8f5e9; color: #2e7d32; }
.badge-organic.no  { background: #fafafa; color: #9e9e9e; border: 1px solid #e0e0e0; }

.phone-link { color: #25D366 !important; font-weight: 600; }
.phone-link:hover { color: #128C7E !important; }
.phone-link i { margin-right: 3px; }

/* ─── Empty State ──────────────────────────────────────────────── */
.ael-empty {
    padding: 60px 20px;
    text-align: center;
    color: #8395a7;
}
.ael-empty i { font-size: 48px; display: block; margin-bottom: 16px; color: #d1dae6; }
.ael-empty h5 { font-size: 18px; font-weight: 700; color: #4a5568; margin-bottom: 6px; }

/* ─── DataTables Overrides ─────────────────────────────────────── */
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_length { display: none; }
.dataTables_wrapper .dataTables_paginate { padding: 12px 16px; }
.dataTables_wrapper .dataTables_info    { padding: 12px 16px; font-size: 12.5px; color: #8395a7; }
.paginate_button { border-radius: 6px !important; }
.paginate_button.current { background: var(--ael-green-start) !important; border-color: var(--ael-green-start) !important; color: #fff !important; }
</style>

<div id="wrapper">
    <div class="content-inner">
        <div class="container-fluid">

            <!-- ── Hero Header ─────────────────────────────────── -->
            <div class="ael-hero" style="padding: 16px 24px; margin-bottom: 16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; position:relative; z-index:1;">
                    <div>
                        <h2 class="ael-hero-title" style="margin:0; font-size:22px;">
                            <i class="fa-solid fa-file-excel" style="margin-right:10px;"></i>Ads Excel List
                        </h2>
                    </div>
                    <div class="ael-hero-actions">
                        <button type="button" class="btn btn-primary btn-sm import-all-excel-leads" style="background-color: #1a73e8; border-color: #1a73e8; color: #fff;">
                            <i class="fa fa-upload"></i>&nbsp; Import All Leads
                        </button>
                        <a href="<?= admin_url('settings?group=general'); ?>" class="btn btn-default btn-sm">
                            <i class="fa fa-cog"></i>&nbsp; Configure Settings
                        </a>
                        <a href="<?= e($sheet_url); ?>" target="_blank" class="btn btn-warning btn-sm">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>&nbsp; Open Google Sheet
                        </a>
                    </div>
                </div>
            </div>

            <!-- ── Toolbar ─────────────────────────────────────── -->
            <div class="ael-toolbar" style="margin-bottom: 16px;">
                <div class="ael-search-wrap">
                    <i class="fa fa-search"></i>
                    <input type="text" id="aelSearchInput" placeholder="Search name, phone, email..." />
                </div>
            </div>

            <!-- ── Table Panel ─────────────────────────────────── -->
            <div class="ael-panel">
                <div class="ael-panel-header">
                    <i class="fa-solid fa-table-list" style="color:var(--ael-green-start);"></i>
                    <h5>Lead Records</h5>
                </div>

                 <?php if (empty($rows)): ?>
                <div class="ael-empty">
                    <i class="fa-solid fa-file-excel"></i>
                    <h5>No Leads Found</h5>
                    <?php if (!empty($fetch_error)): ?>
                        <div class="alert alert-danger" style="display:inline-block; max-width:600px; margin-top:10px; font-weight:600; text-align:left;">
                            <i class="fa fa-exclamation-triangle" style="font-size:16px; display:inline; margin-right:8px; color:#a94442;"></i>
                            <?= e($fetch_error); ?>
                        </div>
                    <?php else: ?>
                        <p>No leads could be fetched from the Google Sheet.<br>
                        <small>Check your Google Sheet link in <a href="<?= admin_url('settings?group=general'); ?>">General Settings</a>.</small></p>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered ael-table" id="aelTable">
                        <thead>
                            <tr>
                                <th style="width:38px;">#</th>
                                <?php foreach ($headers as $h): ?>
                                    <?php
                                    $cleanTitle = ucwords(str_replace(
                                        ['_', '?', '.', '4.', '5.', '6.'],
                                        [' ', '',  '',  '',   '',   ''],
                                        $h
                                    ));
                                    ?>
                                    <th><?= e(trim($cleanTitle)); ?></th>
                                <?php endforeach; ?>
                                <?php if (is_admin()): ?>
                                    <th style="min-width: 90px; text-align: center;">Clicked</th>
                                <?php endif; ?>
                                <th style="min-width: 120px; text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $idx = 1; foreach ($rows as $row): ?>
                            <tr>
                                <td><?= $idx++; ?></td>
                                <?php foreach ($headers as $h): ?>
                                    <?php
                                    $val    = isset($row[$h]) ? $row[$h] : '';
                                    $hLower = strtolower($h);
                                    ?>
                                    <td title="<?= e($val); ?>">
                                        <?php if (strpos($hLower, 'phone') !== false && !empty($val)): ?>
                                            <?php $cleanPhone = preg_replace('/[^0-9]/', '', $val); ?>
                                            <a href="https://wa.me/<?= e($cleanPhone); ?>" target="_blank" class="phone-link">
                                                <i class="fa-brands fa-whatsapp"></i><?= e($val); ?>
                                            </a>

                                        <?php elseif (strpos($hLower, 'email') !== false && !empty($val)): ?>
                                            <a href="mailto:<?= e($val); ?>" style="color:#1a73e8;"><?= e($val); ?></a>

                                        <?php elseif ($hLower === 'platform' && !empty($val)): ?>
                                            <?php
                                            $pUp  = strtoupper(trim($val));
                                            $pClass = ($pUp === 'FB' || $pUp === 'FACEBOOK') ? 'fb'
                                                    : (($pUp === 'IG' || $pUp === 'INSTAGRAM') ? 'ig'
                                                    : (($pUp === 'GG' || $pUp === 'GOOGLE') ? 'gg' : 'def'));
                                            $icon = ($pClass === 'fb') ? 'fa-brands fa-facebook-f'
                                                  : (($pClass === 'ig') ? 'fa-brands fa-instagram'
                                                  : (($pClass === 'gg') ? 'fa-brands fa-google' : 'fa-solid fa-ad'));
                                            ?>
                                            <span class="badge-platform <?= $pClass; ?>">
                                                <i class="<?= $icon; ?>"></i> <?= e($pUp); ?>
                                            </span>

                                        <?php elseif ($hLower === 'lead_status' && !empty($val)): ?>
                                            <?php
                                            $sLower = strtolower(trim($val));
                                            $sClass = ($sLower === 'created')   ? 'created'
                                                    : (($sLower === 'converted') ? 'converted'
                                                    : (($sLower === 'junk')      ? 'junk' : 'default'));
                                            ?>
                                            <span class="badge-status <?= $sClass; ?>"><?= e(strtoupper($val)); ?></span>

                                        <?php elseif ($hLower === 'is_organic' || $hLower === 'organic'): ?>
                                            <?php $isYes = in_array(strtolower($val), ['true', '1', 'yes']); ?>
                                            <span class="badge-organic <?= $isYes ? 'yes' : 'no'; ?>">
                                                <?= $isYes ? 'Organic' : 'Paid'; ?>
                                            </span>

                                        <?php elseif (strpos($hLower, 'form') !== false && !empty($val)): ?>
                                            <span style="color:#6c757d; font-size:11.5px;"><i class="fa fa-file-alt me-1"></i><?= e($val); ?></span>

                                        <?php else: ?>
                                            <?= e($val); ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                                
                                <?php 
                                $dbLead = isset($row['db_lead']) ? $row['db_lead'] : null;
                                ?>
                                <?php if (is_admin()): ?>
                                    <td class="text-center">
                                        <?php if ($dbLead): ?>
                                            <?php
                                            $light1_color = ($dbLead['click_1'] == 1) ? '#25d366' : '#bbb';
                                            $light2_color = ($dbLead['click_2'] == 1) ? '#25d366' : '#bbb';
                                            
                                            $light1_time = '';
                                            if ($dbLead['click_1'] == 1 && !empty($dbLead['click_1_time'])) {
                                                $light1_time = ' data-toggle="tooltip" data-title="' . e(_dt($dbLead['click_1_time'])) . '"';
                                            }
                                            
                                            $light2_time = '';
                                            if ($dbLead['click_2'] == 1 && !empty($dbLead['click_2_time'])) {
                                                $light2_time = ' data-toggle="tooltip" data-title="' . e(_dt($dbLead['click_2_time'])) . '"';
                                            }
                                            ?>
                                            <div style="display:inline-flex; align-items:center; gap:10px;">
                                                <div <?= $light1_time; ?>><span style="display:inline-block; width:10px; height:10px; border-radius:50%; background-color:<?= $light1_color; ?>;"></span><span style="font-size:10px; margin-left:2px; font-weight:bold;">1</span></div>
                                                <div <?= $light2_time; ?>><span style="display:inline-block; width:10px; height:10px; border-radius:50%; background-color:<?= $light2_color; ?>;"></span><span style="font-size:10px; margin-left:2px; font-weight:bold;">2</span></div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <?php if ($dbLead): ?>
                                        <button type="button" class="btn btn-info btn-xs" style="display:block; width:100%; margin-bottom:4px; font-weight:bold; font-size:11px; padding:3px 6px;" onclick="initLeadLoanDetails(<?= $dbLead['id']; ?>); return false;">
                                            <i class="fa fa-edit"></i> Details
                                        </button>
                                        <?php
                                        $wasWpSent = total_rows('cold_wp_messages', ['lead_id' => $dbLead['id']]) > 0;
                                        if ($wasWpSent):
                                        ?>
                                            <button type="button" class="btn btn-default btn-xs send-single-wp" style="display:block; width:100%; background-color:#dcdcdc; color:#777; font-weight:bold; font-size:11px; padding:3px 6px;" title="sended" data-id="<?= $dbLead['id']; ?>" data-name="<?= e($dbLead['name']); ?>" data-phone="<?= e($dbLead['phonenumber']); ?>">
                                                <i class="fa-solid fa-rotate"></i> Re-send
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-success btn-xs send-single-wp" style="display:block; width:100%; background-color:#25d366; border-color:#25d366; color:#fff; font-weight:bold; font-size:11px; padding:3px 6px;" data-id="<?= $dbLead['id']; ?>" data-name="<?= e($dbLead['name']); ?>" data-phone="<?= e($dbLead['phonenumber']); ?>">
                                                <i class="fa-brands fa-whatsapp"></i> WhatsApp
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php 
                                        $fullName = isset($row['full_name']) ? $row['full_name'] : (isset($row['name']) ? $row['name'] : 'Customer'); 
                                        $phoneVal = '';
                                        foreach ($row as $k => $v) {
                                            if (strpos(strtolower($k), 'phone') !== false) {
                                                $phoneVal = $v;
                                                break;
                                            }
                                        }
                                        $cleanPhoneVal = preg_replace('/[^0-9]/', '', $phoneVal);
                                        ?>
                                        <?php if (!empty($cleanPhoneVal)): ?>
                                            <a href="https://wa.me/<?= e($cleanPhoneVal); ?>" target="_blank" class="btn btn-success btn-xs" style="display:block; width:100%; background-color:#25d366; border-color:#25d366; color:#fff; text-align:center; margin-bottom:4px; font-weight:bold; font-size:11px; padding:3px 6px;">
                                                <i class="fa-brands fa-whatsapp"></i> WhatsApp
                                            </a>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-warning btn-xs import-excel-lead" style="display:block; width:100%; font-weight:bold; font-size:11px; padding:3px 6px; background-color:#ff9f43; border-color:#ff9f43; color:#fff;" 
                                                data-name="<?= e($fullName); ?>" 
                                                data-phone="<?= e($phoneVal); ?>" 
                                                data-email="<?= e(isset($row['email']) ? $row['email'] : ''); ?>"
                                                data-extra="<?= e(json_encode($row)); ?>">
                                            <i class="fa fa-plus"></i> Import to CRM
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div><!-- /.ael-panel -->

        </div>
    </div>
</div>

<script>
function initExcelTable() {
    // Prevent double initialization
    if ($.fn.DataTable.isDataTable('#aelTable')) {
        return;
    }

    var table = $('#aelTable').DataTable({
        "pageLength": 25,
        "language": {
            "emptyTable": "No leads found"
        },
        "dom": "rtip", // Custom search/info/pagination layout
        "ordering": true,
        "info": true,
        "paging": true,
        "columnDefs": [
            { "orderable": false, "targets": 0 } // index column not orderable
        ]
    });

    // Handle custom search input search
    var searchInput = document.getElementById('aelSearchInput');
    if (searchInput) {
        // Remove previous listeners if any
        var newSearchInput = searchInput.cloneNode(true);
        searchInput.parentNode.replaceChild(newSearchInput, searchInput);
        
        newSearchInput.addEventListener('keyup', function () {
            table.search(this.value).draw();
        });
    }

    // Set cursor pointers for cell tooltips
    document.querySelectorAll('.ael-table td').forEach(function (td) {
        if (td.scrollWidth > td.clientWidth) {
            td.style.cursor = 'pointer';
        }
    });
}

// Support AJAX container loading and normal document load
if (window.jQuery && typeof($.fn.DataTable) !== 'undefined') {
    initExcelTable();
} else {
    document.addEventListener('DOMContentLoaded', initExcelTable);
}

// AJAX Lead Import Action
$(document).off('click', '.import-excel-lead').on('click', '.import-excel-lead', function(e) {
    e.preventDefault();
    var btn = $(this);
    var originalText = btn.html();
    
    btn.prop('disabled', true).html('<i class="fa-solid fa-circle-notch fa-spin"></i> Importing...');
    
    var postData = {
        name: btn.data('name'),
        phone: btn.data('phone'),
        email: btn.data('email')
    };
    
    var extra = btn.data('extra');
    if (extra) {
        $.extend(postData, extra);
    }
    
    if (typeof(csrfData) !== 'undefined') {
        postData[csrfData.token_name] = csrfData.hash;
    }

    $.post(admin_url + 'ads_excel_list/import_lead_ajax', postData, function(response) {
        var res = JSON.parse(response);
        if (res.success) {
            alert_float('success', res.message);
            
            // Reload the category dynamically
            if (typeof(handleLeadCategoryChange) === 'function') {
                handleLeadCategoryChange();
            } else {
                window.location.reload();
            }
        } else {
            alert_float('danger', res.message);
            btn.prop('disabled', false).html(originalText);
        }
    }).fail(function() {
        alert_float('danger', 'Failed to import lead.');
        btn.prop('disabled', false).html(originalText);
    });
});
// AJAX Bulk Lead Import Action
$(document).off('click', '.import-all-excel-leads').on('click', '.import-all-excel-leads', function(e) {
    e.preventDefault();
    var btn = $(this);
    var originalText = btn.html();
    
    if (!confirm('Are you sure you want to import all new leads from the Google Sheet? Duplicates will be skipped.')) {
        return;
    }
    
    btn.prop('disabled', true).html('<i class="fa-solid fa-circle-notch fa-spin"></i> Importing Leads...');
    
    var postData = {};
    if (typeof(csrfData) !== 'undefined') {
        postData[csrfData.token_name] = csrfData.hash;
    }
    
    $.post(admin_url + 'ads_excel_list/import_all_leads_ajax', postData, function(response) {
        var res = JSON.parse(response);
        if (res.success) {
            alert_float('success', res.message);
            setTimeout(function() {
                window.location.reload();
            }, 1500);
        } else {
            alert_float('danger', res.message);
            btn.prop('disabled', false).html(originalText);
        }
    }).fail(function() {
        alert_float('danger', 'Failed to import bulk leads.');
        btn.prop('disabled', false).html(originalText);
    });
});
</script>
<?php if (!isset($is_ajax) || !$is_ajax) { init_tail(); } ?>
