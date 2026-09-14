<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
.dm-header-card {
    background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
    border-radius: 12px;
    color: #fff;
    padding: 28px 32px;
    margin-bottom: 28px;
    box-shadow: 0 4px 20px rgba(26,115,232,0.3);
}
.dm-header-card h2 { margin: 0 0 6px; font-size: 24px; font-weight: 700; }
.dm-header-card p  { margin: 0; opacity: 0.85; font-size: 14px; }
.dm-stat-box {
    border-radius: 10px;
    padding: 20px 22px;
    color: #fff;
    text-align: center;
    font-weight: 600;
    box-shadow: 0 3px 14px rgba(0,0,0,.12);
    margin-bottom: 20px;
}
.dm-stat-box .num  { font-size: 34px; font-weight: 700; line-height: 1; margin-bottom: 4px; }
.dm-stat-box .lbl  { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; opacity: .85; }
.dm-stat-blue   { background: linear-gradient(135deg,#1a73e8,#0d47a1); }
.dm-stat-green  { background: linear-gradient(135deg,#00c853,#1b5e20); }
.dm-stat-orange { background: linear-gradient(135deg,#ff6d00,#e65100); }
.dm-stat-purple { background: linear-gradient(135deg,#7c4dff,#311b92); }
.dm-url-box {
    background: #f8f9fa;
    border: 1px dashed #1a73e8;
    border-radius: 8px;
    padding: 14px 18px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.dm-url-box span  { font-size: 13px; color: #333; flex: 1; word-break: break-all; font-family: monospace; }
.dm-leads-table th { background: #f4f6fa; font-weight: 600; font-size: 13px; }
.dm-leads-table td { font-size: 13px; vertical-align: middle !important; }
.add-lead-btn {
    background: linear-gradient(135deg, #00c853, #1b5e20);
    border: none;
    color: #fff;
    font-weight: 600;
    padding: 9px 20px;
    border-radius: 6px;
    cursor: pointer;
    transition: opacity .2s;
}
.add-lead-btn:hover { opacity: .88; }
</style>

<div id="wrapper">
    <?php init_tail(); ?>

    <div class="content-inner">
        <div class="container-fluid">

            <!-- Header Card -->
            <div class="dm-header-card">
                <h2><i class="fa fa-dollar" style="margin-right:10px;"></i>Loans ADS Lead</h2>
                <p>Manage leads captured through Loan Ads digital marketing campaigns.</p>
            </div>

            <!-- Stats Row -->
            <div class="row">
                <div class="col-sm-3">
                    <div class="dm-stat-box dm-stat-blue">
                        <div class="num" id="stat_total"><?= count($leads); ?></div>
                        <div class="lbl">Total Leads</div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <?php
                    $new_count = 0;
                    $first_status = count($statuses) > 0 ? $statuses[0]['name'] : '';
                    foreach ($leads as $l) { if ($l['status_name'] == $first_status) $new_count++; }
                    ?>
                    <div class="dm-stat-box dm-stat-green">
                        <div class="num"><?= $new_count; ?></div>
                        <div class="lbl">New / <?= e($first_status); ?></div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <?php
                    $this_month = 0;
                    $month_start = date('Y-m-01');
                    foreach ($leads as $l) { if (strtotime($l['dateadded']) >= strtotime($month_start)) $this_month++; }
                    ?>
                    <div class="dm-stat-box dm-stat-orange">
                        <div class="num"><?= $this_month; ?></div>
                        <div class="lbl">This Month</div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <?php
                    $total_val = array_sum(array_column($leads, 'lead_value'));
                    ?>
                    <div class="dm-stat-box dm-stat-purple">
                        <div class="num">₹<?= number_format($total_val, 0); ?></div>
                        <div class="lbl">Total Lead Value</div>
                    </div>
                </div>
            </div>

            <!-- Public Form URL -->
            <div class="panel panel-default">
                <div class="panel-body">
                    <h5 style="font-weight:700; margin-bottom:14px;"><i class="fa fa-link" style="color:#1a73e8;"></i> Public Lead Capture URL</h5>
                    <div class="dm-url-box">
                        <i class="fa fa-globe" style="color:#1a73e8; font-size:18px;"></i>
                        <span id="loans_public_url"><?= e($public_url); ?></span>
                        <button type="button" class="btn btn-default btn-xs" onclick="copyUrl()"><i class="fa fa-copy"></i> Copy</button>
                        <a href="<?= e($public_url); ?>" target="_blank" class="btn btn-primary btn-xs"><i class="fa fa-external-link"></i> Open Form</a>
                    </div>
                    <small class="text-muted"><i class="fa fa-info-circle"></i> Share this URL in your ad campaigns. Leads submitted through this form will appear in this table automatically.</small>
                </div>
            </div>

            <!-- Manual Add Lead + Leads Table -->
            <div class="panel panel-default">
                <div class="panel-heading" style="display:flex; align-items:center; justify-content:space-between;">
                    <h3 class="panel-title"><i class="fa fa-users"></i> Leads — Loans ADS (<?= count($leads); ?>)</h3>
                    <button class="add-lead-btn" data-toggle="modal" data-target="#addLeadModal">
                        <i class="fa fa-plus"></i> Add Lead Manually
                    </button>
                </div>
                <div class="panel-body">
                    <?php if (count($leads) === 0): ?>
                        <div class="alert alert-info text-center">
                            <i class="fa fa-info-circle"></i> No Loans ADS leads found yet. Share the public form URL to start collecting leads!
                        </div>
                    <?php else: ?>
                        <div style="margin-bottom: 12px; display:flex; gap:10px; flex-wrap:wrap;">
                            <select id="filterStatus" class="form-control" style="max-width:200px;">
                                <option value="">All Statuses</option>
                                <?php foreach ($statuses as $s): ?>
                                    <option value="<?= e($s['name']); ?>"><?= e($s['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" id="filterSearch" class="form-control" placeholder="Search by name / phone..." style="max-width:250px;">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered dm-leads-table" id="loansLeadsTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Lead Value</th>
                                        <th>Status</th>
                                        <th>Date Added</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; foreach ($leads as $lead): ?>
                                    <tr data-status="<?= e($lead['status_name']); ?>" data-name="<?= e(strtolower($lead['name'] . ' ' . $lead['phonenumber'])); ?>">
                                        <td class="text-muted"><?= $i++; ?></td>
                                        <td class="bold">
                                            <a href="<?= admin_url('leads#leadid=' . $lead['id']); ?>" target="_blank"><?= e($lead['name']); ?></a>
                                        </td>
                                        <td><?= e($lead['phonenumber']); ?></td>
                                        <td><?= e($lead['email']); ?></td>
                                        <td>
                                            <?php if ($lead['lead_value'] > 0): ?>
                                                <span class="label label-success">₹<?= number_format($lead['lead_value'], 0); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="label label-info"><?= e($lead['status_name']); ?></span>
                                        </td>
                                        <td>
                                            <span data-toggle="tooltip" title="<?= e(_dt($lead['dateadded'])); ?>">
                                                <?= time_ago($lead['dateadded']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= admin_url('leads#leadid=' . $lead['id']); ?>" class="btn btn-primary btn-xs" target="_blank">
                                                <i class="fa fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Add Lead Modal -->
<div class="modal fade" id="addLeadModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#1a73e8,#0d47a1); color:#fff; border-radius:4px 4px 0 0;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Add Loans ADS Lead</h4>
            </div>
            <form id="addLeadForm">
                <div class="modal-body">
                    <input type="hidden" name="source_id" value="<?= $source_id; ?>">
                    <div class="form-group">
                        <label class="control-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Ramesh Kumar" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phonenumber" class="form-control" placeholder="e.g. 9876543210" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. ramesh@example.com">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Loan Type</label>
                                <select name="description" class="form-control">
                                    <option value="">Select Loan Type</option>
                                    <option value="Home Loan">Home Loan</option>
                                    <option value="Personal Loan">Personal Loan</option>
                                    <option value="Business Loan">Business Loan</option>
                                    <option value="Car Loan">Car Loan</option>
                                    <option value="Education Loan">Education Loan</option>
                                    <option value="Gold Loan">Gold Loan</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Loan Amount (₹)</label>
                                <input type="number" name="lead_value" class="form-control" placeholder="e.g. 500000" min="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Add Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function copyUrl() {
    var url = document.getElementById('loans_public_url').innerText;
    navigator.clipboard.writeText(url).then(function() {
        alert_float('success', 'URL copied to clipboard!');
    });
}

// Filter table
$('#filterStatus, #filterSearch').on('change keyup', function() {
    var status = $('#filterStatus').val().toLowerCase();
    var search = $('#filterSearch').val().toLowerCase();
    $('#loansLeadsTable tbody tr').each(function() {
        var rowStatus = $(this).data('status') ? $(this).data('status').toLowerCase() : '';
        var rowName   = $(this).data('name') ? $(this).data('name').toLowerCase() : '';
        var show = (!status || rowStatus === status) && (!search || rowName.indexOf(search) !== -1);
        $(this).toggle(show);
    });
});

// Add lead AJAX
$('#addLeadForm').on('submit', function(e) {
    e.preventDefault();
    var form = $(this);
    $.ajax({
        url: admin_url + 'digital_marketing/add_lead',
        type: 'POST',
        data: form.serialize(),
        success: function(res) {
            var data = typeof res === 'string' ? JSON.parse(res) : res;
            if (data.success) {
                alert_float('success', 'Lead added successfully!');
                $('#addLeadModal').modal('hide');
                setTimeout(function() { location.reload(); }, 1200);
            } else {
                alert_float('danger', data.message || 'Failed to add lead.');
            }
        },
        error: function() {
            alert_float('danger', 'An error occurred. Please try again.');
        }
    });
});
</script>
