<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
                            <h4 class="tw-my-0 tw-font-bold tw-text-lg tw-text-neutral-700">
                                <i class="fa fa-history text-muted"></i> WhatsApp Cold Messaging logs
                            </h4>
                            <a href="<?= admin_url('cold_wp'); ?>" class="btn btn-primary btn-sm">
                                <i class="fa fa-whatsapp"></i> New Campaign Portal
                            </a>
                        </div>
                        <hr class="hr-panel-separator" />

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dt-table" data-order-col="4" data-order-type="desc">
                                <thead>
                                    <tr>
                                        <th>Lead Name</th>
                                        <th>Phone Number</th>
                                        <th width="40%">Message Content</th>
                                        <th>Attached Media</th>
                                        <th>Sent By</th>
                                        <th>Sent At</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $log) { ?>
                                        <tr>
                                            <td class="bold">
                                                <?php if ($log['lead_id']) { ?>
                                                    <a href="#" onclick="init_lead(<?= $log['lead_id']; ?>); return false;"><?= e($log['lead_name'] ?: 'Unknown'); ?></a>
                                                <?php } else { ?>
                                                    <?= e($log['lead_name'] ?: 'Unknown'); ?>
                                                <?php } ?>
                                            </td>
                                            <td><?= e($log['phone_number']); ?></td>
                                            <td>
                                                <div style="max-height: 80px; overflow-y: auto; font-size: 13px; line-height: 1.4;">
                                                    <?= nl2br(e($log['message_text'])); ?>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($log['image_path']) { ?>
                                                    <a href="<?= base_url($log['image_path']); ?>" target="_blank">
                                                        <img src="<?= base_url($log['image_path']); ?>" style="max-height: 40px; max-width: 60px; object-fit: cover; border-radius: 2px; border: 1px solid #ddd;" />
                                                    </a>
                                                <?php } else { ?>
                                                    <span class="text-muted">None</span>
                                                <?php } ?>
                                            </td>
                                            <td><?= e($log['sent_by']); ?></td>
                                            <td><?= e($log['sent_at']); ?></td>
                                            <td class="text-center">
                                                <span class="label label-success"><?= e($log['status']); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?php if (staff_can('delete', 'cold_wp_messages')) { ?>
                                                    <a href="<?= admin_url('cold_wp/delete_log/' . $log['id']); ?>" class="btn btn-danger btn-xs delete-log-btn" onclick="return confirm('Are you sure you want to delete this log?');">
                                                        <i class="fa fa-remove"></i>
                                                    </a>
                                                <?php } else { ?>
                                                    <span class="text-muted">-</span>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
$(function() {
    // Initialize standard DataTable for logs page
    if ($('.dt-table').length) {
        initDataTable('.dt-table', undefined, undefined, undefined, 'undefined', [5, 'desc']);
    }
});
</script>
</body>
</html>
