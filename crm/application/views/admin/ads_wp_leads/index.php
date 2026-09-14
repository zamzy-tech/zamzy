<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
.awp-header {
    background: linear-gradient(135deg, #128C7E 0%, #075E54 100%);
    border-radius: 12px;
    color: #fff;
    padding: 28px 32px;
    margin-bottom: 24px;
    box-shadow: 0 4px 20px rgba(7,94,84,0.35);
}
.awp-header h2 { margin: 0 0 5px; font-size: 24px; font-weight: 700; }
.awp-header p  { margin: 0; opacity: .85; font-size: 14px; }
.awp-stat {
    border-radius: 10px;
    padding: 20px 22px;
    color: #fff;
    text-align: center;
    font-weight: 600;
    box-shadow: 0 3px 14px rgba(0,0,0,.12);
    margin-bottom: 20px;
}
.awp-stat .num { font-size: 34px; font-weight: 700; line-height: 1; margin-bottom: 4px; }
.awp-stat .lbl { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; opacity: .85; }
.awp-green  { background: linear-gradient(135deg,#128C7E,#075E54); }
.awp-teal   { background: linear-gradient(135deg,#25D366,#128C7E); }
.awp-blue   { background: linear-gradient(135deg,#1a73e8,#0d47a1); }
.awp-orange { background: linear-gradient(135deg,#ff6d00,#e65100); }
.awp-filter-bar {
    background: #f8f9fa;
    border: 1px solid #e3e8ef;
    border-radius: 10px;
    padding: 14px 18px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}
.awp-filter-bar label { font-weight: 600; font-size: 13px; margin-bottom: 0; white-space: nowrap; }
.awp-filter-bar .form-control, .awp-filter-bar .form-select {
    font-size: 13px; height: 34px; padding: 4px 10px; border-radius: 6px;
}
.awp-table th  { background: #f4f6fa; font-weight: 600; font-size: 13px; }
.awp-table td  { font-size: 13px; vertical-align: middle !important; }
.badge-service { background: #e8f5e9; color: #2e7d32; border-radius: 20px; padding: 3px 10px; font-size: 12px; font-weight: 600; }
.awp-view-btn  { font-size: 12px; padding: 4px 10px; border-radius: 5px; }
</style>

<div id="wrapper">
    <div class="content-inner">
        <div class="container-fluid">

            <!-- Header -->
            <div class="awp-header">
                <h2><i class="fa-brands fa-whatsapp" style="margin-right:10px;"></i>Ads WhatsApp Leads</h2>
                <p>Leads captured from the CREDIFIX website via WhatsApp OTP-verified form submissions.</p>
            </div>

            <!-- Stats Row -->
            <div class="row">
                <div class="col-sm-3">
                    <div class="awp-stat awp-green">
                        <div class="num"><?= count($leads); ?></div>
                        <div class="lbl">Total Leads</div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="awp-stat awp-teal">
                        <div class="num"><?= $today_count; ?></div>
                        <div class="lbl">Today</div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="awp-stat awp-blue">
                        <div class="num"><?= $week_count; ?></div>
                        <div class="lbl">This Week</div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <?php
                    $converted = 0;
                    foreach ($leads as $l) {
                        if ($l['status_name'] && strtolower($l['status_name']) == 'converted') $converted++;
                    }
                    ?>
                    <div class="awp-stat awp-orange">
                        <div class="num"><?= $converted; ?></div>
                        <div class="lbl">Converted</div>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="awp-filter-bar">
                <label><i class="fa fa-filter"></i> Filter:</label>
                <div>
                    <label style="font-size:12px;margin-bottom:2px;">Date</label>
                    <input type="date" id="filterDate" class="form-control"
                        value="<?= e($filter_date); ?>" style="width:150px;" />
                </div>
                <?php if (!empty($batches)): ?>
                <div>
                    <label style="font-size:12px;margin-bottom:2px;">Batch / Section</label>
                    <select id="filterBatch" class="form-control" style="width:180px;">
                        <option value="">All Batches</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?= e($b['batch_name']); ?>"
                                <?= $filter_batch == $b['batch_name'] ? 'selected' : ''; ?>>
                                <?= e($b['batch_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div style="flex:1;min-width:180px;">
                    <label style="font-size:12px;margin-bottom:2px;">Search</label>
                    <input type="text" id="filterSearch" class="form-control"
                        placeholder="Name / Phone..." value="<?= e($filter_search); ?>" />
                </div>
                <div style="align-self:flex-end;">
                    <button class="btn btn-primary btn-sm" onclick="applyFilters()">
                        <i class="fa fa-search"></i> Apply
                    </button>
                    <a href="<?= admin_url('ads_wp_leads'); ?>" class="btn btn-default btn-sm">
                        <i class="fa fa-times"></i> Clear
                    </a>
                </div>
                <div style="align-self:flex-end; margin-left:auto;">
                    <span class="text-muted" style="font-size:13px;">
                        Showing <strong><?= count($leads); ?></strong> leads
                    </span>
                </div>
            </div>

            <!-- Table -->
            <div class="panel_s">
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered awp-table" id="awpLeadsTable">
                            <thead>
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th>Name</th>
                                    <th>WhatsApp Number</th>
                                    <th>Service Required</th>
                                    <th>Loan Amount</th>
                                    <th>Batch / Section</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th style="width:80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($leads)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted" style="padding:40px;">
                                        <i class="fa-brands fa-whatsapp" style="font-size:40px;color:#25D366;display:block;margin-bottom:10px;"></i>
                                        No leads captured yet.<br>
                                        <small>Leads will appear here when visitors submit the CREDIFIX form.</small>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php $i = 1; foreach ($leads as $lead): ?>
                                <tr data-id="<?= $lead['id']; ?>"
                                    data-name="<?= e(strtolower($lead['name'])); ?>"
                                    data-status="<?= e(strtolower($lead['status_name'] ?? '')); ?>">
                                    <td class="text-muted"><?= $i++; ?></td>
                                    <td>
                                        <strong>
                                            <a href="<?= admin_url('leads/index/' . $lead['id']); ?>"
                                               onclick="init_lead(<?= $lead['id']; ?>); return false;">
                                                <?= e($lead['name']); ?>
                                            </a>
                                        </strong>
                                    </td>
                                    <td>
                                        <?php if ($lead['phonenumber']): ?>
                                        <a href="https://wa.me/91<?= e(preg_replace('/[^0-9]/', '', $lead['phonenumber'])); ?>"
                                           target="_blank" class="text-success" title="Open WhatsApp">
                                            <i class="fa-brands fa-whatsapp"></i>
                                            <?= e($lead['phonenumber']); ?>
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($lead['company']): ?>
                                        <span class="badge-service"><?= e($lead['company']); ?></span>
                                        <?php else: ?>
                                        <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($lead['lead_value'] && $lead['lead_value'] > 0): ?>
                                        <strong>₹<?= number_format($lead['lead_value']); ?></strong>
                                        <?php else: ?>
                                        <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($lead['batch_name'])): ?>
                                        <span class="label label-default"><?= e($lead['batch_name']); ?></span>
                                        <?php else: ?>
                                        <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($lead['status_name']): ?>
                                        <span class="label label-info"><?= e($lead['status_name']); ?></span>
                                        <?php else: ?>
                                        <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span data-toggle="tooltip"
                                              title="<?= e(_dt($lead['dateadded'])); ?>">
                                            <?= e(time_ago($lead['dateadded'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= admin_url('leads/index/' . $lead['id']); ?>"
                                           onclick="init_lead(<?= $lead['id']; ?>); return false;"
                                           class="btn btn-info btn-xs awp-view-btn" title="View Lead">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="#" onclick="deleteLead(<?= $lead['id']; ?>); return false;"
                                           class="btn btn-danger btn-xs awp-view-btn" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function applyFilters() {
    var params = new URLSearchParams();
    var d = document.getElementById('filterDate').value;
    var b = document.getElementById('filterBatch') ? document.getElementById('filterBatch').value : '';
    var s = document.getElementById('filterSearch').value;
    if (d) params.set('filter_date', d);
    if (b) params.set('filter_batch', b);
    if (s) params.set('search', s);
    window.location = '<?= admin_url('ads_wp_leads'); ?>?' + params.toString();
}

function deleteLead(id) {
    if (!confirm('Are you sure you want to delete this lead? This action cannot be undone.')) return;
    $.ajax({
        url: admin_url + 'ads_wp_leads/delete_lead/' + id,
        type: 'GET',
        success: function(res) {
            var data = typeof res === 'string' ? JSON.parse(res) : res;
            if (data.success) {
                alert_float('success', 'Lead deleted successfully.');
                $('tr[data-id="' + id + '"]').fadeOut();
                setTimeout(function() { location.reload(); }, 1000);
            } else {
                alert_float('danger', 'Failed to delete lead.');
            }
        },
        error: function() { alert_float('danger', 'Error occurred.'); }
    });
}

// Enable Enter key on search field
document.getElementById('filterSearch').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') applyFilters();
});

// DataTable init for export/pagination
$(function() {
    if ($.fn.DataTable) {
        $('#awpLeadsTable').DataTable({
            paging: true,
            pageLength: 50,
            searching: false,
            ordering: true,
            language: { emptyTable: 'No leads found.' }
        });
    }
});
</script>
<?php init_tail(); ?>
