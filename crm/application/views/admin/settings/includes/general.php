<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
	<div class="col-md-12">
		<?php $company_logo_light = get_option('company_logo'); ?>
		<?php $company_logo_dark  = get_option('company_logo_dark'); ?>
		<?php if ($company_logo_light != '') { ?>
		<div class="row">
			<div class="col-md-9">
				<img src="<?= base_url('uploads/company/' . $company_logo_light); ?>"
					class="img img-responsive">
			</div>
			<?php if (staff_can('delete', 'settings')) { ?>
			<div class="col-md-3 text-right">
				<a href="<?= admin_url('settings/remove_company_logo'); ?>"
					data-toggle="tooltip"
					title="<?= _l('settings_general_company_remove_logo_tooltip'); ?>"
					class="_delete text-danger"><i class="fa fa-remove"></i></a>
			</div>
			<?php } ?>
		</div>
		<div class="clearfix"></div>
		<?php } else { ?>
		<div class="form-group">
			<label for="company_logo"
				class="control-label"><?= _l('company_logo_light'); ?></label>
			<input type="file" name="company_logo" class="form-control" value="" data-toggle="tooltip"
				title="<?= _l('settings_general_company_logo_tooltip'); ?>">
		</div>
		<?php } ?>
		<hr />
		<?php if ($company_logo_dark != '') { ?>
		<div class="row">
			<div class="col-md-9">
				<img src="<?= base_url('uploads/company/' . $company_logo_dark); ?>"
					class="img img-responsive">
			</div>
			<?php if (staff_can('delete', 'settings')) { ?>
			<div class="col-md-3 text-right">
				<a href="<?= admin_url('settings/remove_company_logo/dark'); ?>"
					data-toggle="tooltip"
					title="<?= _l('settings_general_company_remove_logo_tooltip'); ?>"
					class="_delete text-danger"><i class="fa fa-remove"></i></a>
			</div>
			<?php } ?>
		</div>
		<div class="clearfix"></div>
		<?php } else { ?>
		<div class="form-group">
			<label for="company_logo_dark"
				class="control-label"><?= _l('company_logo_dark'); ?></label>
			<input type="file" name="company_logo_dark" class="form-control" value="" data-toggle="tooltip"
				title="<?= _l('settings_general_company_logo_tooltip'); ?>">
		</div>
		<?php } ?>
		<hr />
		<?php $favicon = get_option('favicon'); ?>
		<?php if ($favicon != '') { ?>
		<div class="form-group favicon">
			<div class="row">
				<div class="col-md-9">
					<img src="<?= base_url('uploads/company/' . $favicon); ?>"
						class="img img-responsive">
				</div>
				<?php if (staff_can('delete', 'settings')) { ?>
				<div class="col-md-3 text-right">
					<a href="<?= admin_url('settings/remove_fv'); ?>"
						class="_delete text-danger"><i class="fa fa-remove"></i></a>
				</div>
				<?php } ?>
			</div>
			<div class="clearfix"></div>
		</div>
		<?php } else { ?>
		<div class="form-group favicon_upload">
			<label for="favicon"
				class="control-label"><?= _l('settings_general_favicon'); ?></label>
			<input type="file" name="favicon" class="form-control">
		</div>
		<?php } ?>
		<hr />
		<?php $attrs = (get_option('companyname') != '' ? [] : ['autofocus' => true]); ?>
		<?= render_input('settings[companyname]', 'settings_general_company_name', get_option('companyname'), 'text', $attrs); ?>
		<hr />
		<?= render_input('settings[main_domain]', 'settings_general_company_main_domain', get_option('main_domain')); ?>
		<hr />
		<?php render_yes_no_option('rtl_support_admin', 'settings_rtl_support_admin'); ?>
		<hr />
		<?php render_yes_no_option('rtl_support_client', 'settings_rtl_support_client'); ?>
		<hr />
		<?= render_input('settings[allowed_files]', 'settings_allowed_upload_file_types', get_option('allowed_files')); ?>
		<hr />
		<h4 class="bold tw-mt-4 tw-mb-3"><i class="fa-solid fa-file-excel text-success me-2"></i> Ads Excel Settings</h4>
		<?= render_input('settings[excel_sheet_url]', 'Excel Google Sheet Link / URL', get_option('excel_sheet_url', 'https://docs.google.com/spreadsheets/d/17hEUmsz8Q8Q32KDKO7qi0uTdhAXIDz7vRvPmkMS7Yv8/edit?usp=sharing')); ?>
		<?= render_input('settings[excel_lead_count]', 'Excel Lead Import/Sync Count (Give -1 to import all)', get_option('excel_lead_count', '30'), 'number'); ?>
		<?= render_input('settings[excel_lead_show_count]', 'Excel Lead Show Count', get_option('excel_lead_show_count', '30'), 'number'); ?>
		<?= render_input('settings[whatsapp_api_key]', 'WhatsApp API Key', get_option('whatsapp_api_key', 'b0b306dc4bf090c19f85c584906a967c')); ?>
		<hr />
		<?php render_yes_no_option('auto_import_to_lead', 'Auto Import to Lead'); ?>
	</div>
</div>