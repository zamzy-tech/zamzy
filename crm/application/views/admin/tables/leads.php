<?php

defined('BASEPATH') or exit('No direct script access allowed');

$this->ci->load->model('gdpr_model');
$this->ci->load->model('leads_model');
$this->ci->load->model('staff_model');
$statuses = $this->ci->leads_model->get_status();

if (is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1') {
    $consent_purposes = $this->ci->gdpr_model->get_consent_purposes();
}

$rules = [
    App_table_filter::new('name', 'TextRule')->label(_l('leads_dt_name')),
    App_table_filter::new('phonenumber', 'TextRule')->label(_l('leads_dt_phonenumber')),
    App_table_filter::new('company', 'TextRule')->label(_l('lead_company')),
    App_table_filter::new('loan_type', 'SelectRule')->label('Loan Type')->options(function () {
        return [
            ['value' => 'Personal', 'label' => 'Personal'],
            ['value' => 'Business', 'label' => 'Business']
        ];
    }),
    App_table_filter::new('email', 'TextRule')->label(_l('leads_dt_email')),
    App_table_filter::new('title', 'TextRule')->label(_l('lead_title')),
    App_table_filter::new('address', 'TextRule')->label(_l('lead_address')),
    App_table_filter::new('website', 'TextRule')->label(_l('lead_website')),
    App_table_filter::new('description', 'TextRule')->label('Notes / Description'),
    App_table_filter::new('has_notes', 'SelectRule')->label('Notes Filter')->options(function () {
        return [
            ['value' => 'has_notes', 'label' => 'With Notes Only'],
            ['value' => 'no_notes', 'label' => 'Without Notes']
        ];
    })->raw(function ($value) {
        if ($value === 'has_notes') {
            return db_prefix() . 'leads.description IS NOT NULL AND TRIM(' . db_prefix() . 'leads.description) != ""';
        } elseif ($value === 'no_notes') {
            return '(' . db_prefix() . 'leads.description IS NULL OR TRIM(' . db_prefix() . 'leads.description) = "")';
        }
        return '1=1';
    }),
    App_table_filter::new('tags', 'SelectRule')
        ->label(_l('tags'))
        ->options(function ($ci) {
            return collect($ci->db->order_by('name', 'asc')->get(db_prefix() . 'tags')->result_array())->map(fn ($tag) => [
                'value' => $tag['id'],
                'label' => $tag['name'],
            ])->all();
        })->raw(function ($value, $operator, $sql_operator) {
            return db_prefix() . 'leads.id IN (SELECT rel_id FROM ' . db_prefix() . 'taggables WHERE tag_id = ' . $value . ' AND rel_type = "lead")';
        }),
    App_table_filter::new('country', 'SelectRule')->label(_l('lead_country'))->options(function ($ci) {
        return collect(get_all_countries())->map(fn ($country) => [
            'value' => $country['country_id'],
            'label' => $country['short_name'],
        ]);
    }),
    App_table_filter::new('city', 'TextRule')->label(_l('lead_city')),
    App_table_filter::new('state', 'TextRule')->label(_l('lead_state')),
    App_table_filter::new('zip', 'TextRule')->label(_l('lead_zip')),
    App_table_filter::new('is_public', 'BooleanRule')->label(_l('lead_public')),
    App_table_filter::new('lost', 'BooleanRule')->label(_l('lead_lost')),
    App_table_filter::new('junk', 'BooleanRule')->label(_l('lead_junk')),
    App_table_filter::new('lastcontact', 'DateRule')->label(_l('leads_dt_last_contact')),
    App_table_filter::new('dateadded', 'DateRule')->label(_l('date_created')),
    App_table_filter::new('dateassigned', 'DateRule')->label(_l('customer_admin_date_assigned')),
    App_table_filter::new('lead_value', 'NumberRule')->label(_l('lead_add_edit_lead_value')),
    App_table_filter::new('status', 'MultiSelectRule')->label(_l('lead_status'))->options(function () use ($statuses) {
        return collect($statuses)->map(fn ($status) => [
            'value'   => $status['id'],
            'label'   => $status['name'],
            'subtext' => $status['isdefault'] == 1 ? _l('leads_converted_to_client') : null,
        ]);
    }),
    App_table_filter::new('source', 'MultiSelectRule')->label(_l('lead_source'))->options(function ($ci) {
        return collect($ci->leads_model->get_source())->map(fn ($source) => [
            'value' => $source['id'],
            'label' => $source['name'],
        ]);
    }),
];

$rules[] = App_table_filter::new('batch_name', 'SelectRule')->label('Section Name / Batch Name')->options(function ($ci) {
    $batches = $ci->db->select('DISTINCT(batch_name)')->where('batch_name IS NOT NULL')->where('batch_name !=', '')->get(db_prefix() . 'leads')->result_array();
    return collect($batches)->map(fn ($batch) => [
        'value' => $batch['batch_name'],
        'label' => $batch['batch_name'],
    ])->all();
});

$rules[] = App_table_filter::new('assigned', 'SelectRule')->label(_l('leads_dt_assigned'))
    ->withEmptyOperators()
    ->emptyOperatorValue(0)
    ->isVisible(fn () => staff_can('view', 'leads'))
    ->options(function ($ci) {
        $staff = $ci->staff_model->get('', ['active' => 1]);

        return collect($staff)->map(function ($staff) {
            return [
                'value' => $staff['staffid'],
                'label' => $staff['firstname'] . ' ' . $staff['lastname'],
            ];
        })->all();
    });

if (isset($consent_purposes)) {
    $rules[] = App_table_filter::new('gdpr_content', 'SelectRule')
        ->label(_l('gdpr_consent'))
        ->options(function () use ($consent_purposes) {
            return collect($consent_purposes)->map(fn ($purpose) => [
                'value' => $purpose['id'],
                'label' => $purpose['name'],
            ]);
        })->raw(function ($value, $operator, $sql_operator) {
            return db_prefix() . 'leads.id ' . $sql_operator . ' (SELECT lead_id FROM ' . db_prefix() . 'consents WHERE purpose_id=' . $value . ' and action="opt-in" AND date IN (SELECT MAX(date) FROM ' . db_prefix() . 'consents WHERE purpose_id=' . $value . ' AND lead_id=' . db_prefix() . 'leads.id))';
        });
}

return App_table::find('leads')
    ->outputUsing(function ($params) use ($statuses) {
        extract($params);

        $lockAfterConvert      = get_option('lead_lock_after_convert_to_customer');
        $has_permission_delete = staff_can('delete', 'leads');
        $custom_fields         = get_table_custom_fields('leads');
        $consentLeads          = get_option('gdpr_enable_consent_for_leads');

        $aColumns = [
            '1',
            db_prefix() . 'leads.id as id',
            db_prefix() . 'leads.name as name',
        ];
        if (is_gdpr() && $consentLeads == '1') {
            $aColumns[] = '1';
        }
        $aColumns = array_merge($aColumns, [
            'company',
            'loan_type',
            db_prefix() . 'leads.email as email',
            db_prefix() . 'leads.phonenumber as phonenumber',
            'lead_value',
            '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'leads.id and rel_type="lead" ORDER by tag_order ASC LIMIT 1) as tags',
            'firstname as assigned_firstname',
            db_prefix() . 'leads_status.name as status_name',
            db_prefix() . 'leads.description as description',
            db_prefix() . 'leads_sources.name as source_name',
            'lastcontact',
            'dateadded',
        ]);

        $sIndexColumn = 'id';
        $sTable       = db_prefix() . 'leads';

        $join = [
            'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'leads.assigned',
            'LEFT JOIN ' . db_prefix() . 'leads_status ON ' . db_prefix() . 'leads_status.id = ' . db_prefix() . 'leads.status',
            'LEFT JOIN ' . db_prefix() . 'leads_sources ON ' . db_prefix() . 'leads_sources.id = ' . db_prefix() . 'leads.source',
        ];

        foreach ($custom_fields as $key => $field) {
            $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
            array_push($customFieldsColumns, $selectAs);
            array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
            array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'leads.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
        }

        $where = [];

        if ($filtersWhere = $this->getWhereFromRules()) {
            $where[] = $filtersWhere;
        }

        if ($this->ci->input->post('batch_name') || $this->ci->input->get('batch_name')) {
            $batch_name = $this->ci->input->post('batch_name') ?: $this->ci->input->get('batch_name');
            array_push($where, 'AND ' . db_prefix() . 'leads.batch_name = "' . $this->ci->db->escape_str($batch_name) . '"');
        }

        if ($this->ci->input->post('lead_category') || $this->ci->input->get('category')) {
            $category = $this->ci->input->post('lead_category') ?: $this->ci->input->get('category');
            if ($category === 'converted') {
                array_push($where, 'AND ' . db_prefix() . 'leads.id IN (SELECT leadid FROM ' . db_prefix() . 'clients)');
            } elseif ($category === 'cold_wp') {
                array_push($where, 'AND lost = 0 AND junk = 0');
            } elseif ($category === 'ads_wp') {
                array_push($where, 'AND ' . db_prefix() . 'leads.source = (SELECT id FROM ' . db_prefix() . 'leads_sources WHERE name = "Ads WhatsApp" LIMIT 1)');
            } elseif ($category === 'ads_excel_list') {
                array_push($where, 'AND ' . db_prefix() . 'leads.source = (SELECT id FROM ' . db_prefix() . 'leads_sources WHERE name = "Ads Excel List" LIMIT 1)');
            }
        }

        // Status Wise Filter
        $view_status_id = $this->ci->input->post('view_status_id') ?: ($this->ci->input->get('status_id') ?: $this->ci->input->get('status'));
        if ($view_status_id !== null && $view_status_id !== '') {
            if ($view_status_id === 'lost') {
                array_push($where, 'AND ' . db_prefix() . 'leads.lost = 1');
            } elseif ($view_status_id === 'junk') {
                array_push($where, 'AND ' . db_prefix() . 'leads.junk = 1');
            } elseif (is_numeric($view_status_id)) {
                array_push($where, 'AND (' . db_prefix() . 'leads.status = ' . (int)$view_status_id . ' AND ' . db_prefix() . 'leads.lost = 0 AND ' . db_prefix() . 'leads.junk = 0)');
            }
        }

        // Has Notes Filter
        $view_has_notes = $this->ci->input->post('view_has_notes') ?: $this->ci->input->get('has_notes');
        if ($view_has_notes !== null && $view_has_notes !== '') {
            if ($view_has_notes === 'has_notes') {
                array_push($where, 'AND (' . db_prefix() . 'leads.description IS NOT NULL AND TRIM(' . db_prefix() . 'leads.description) != "")');
            } elseif ($view_has_notes === 'no_notes') {
                array_push($where, 'AND (' . db_prefix() . 'leads.description IS NULL OR TRIM(' . db_prefix() . 'leads.description) = "")');
            }
        }

        // Notes Search Keyword
        $view_notes_keyword = $this->ci->input->post('view_notes_keyword') ?: $this->ci->input->get('notes_keyword');
        if ($view_notes_keyword !== null && $view_notes_keyword !== '') {
            array_push($where, 'AND ' . db_prefix() . 'leads.description LIKE "%' . $this->ci->db->escape_like_str($view_notes_keyword) . '%"');
        }

        if (staff_cant('view', 'leads')) {
            array_push($where, 'AND (assigned =' . get_staff_user_id() . ' OR addedfrom = ' . get_staff_user_id() . ' OR is_public = 1)');
        }

        $aColumns = hooks()->apply_filters('leads_table_sql_columns', $aColumns);

        // Fix for big queries. Some hosting have max_join_limit
        if (count($custom_fields) > 4) {
            @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
        }

        $additionalColumns = hooks()->apply_filters('leads_table_additional_columns_sql', [
            'junk',
            'lost',
            'color',
            'status',
            'assigned',
            'lastname as assigned_lastname',
            db_prefix() . 'leads.addedfrom as addedfrom',
            '(SELECT count(leadid) FROM ' . db_prefix() . 'clients WHERE ' . db_prefix() . 'clients.leadid=' . db_prefix() . 'leads.id) as is_converted',
            'zip',
            'click_1',
            'click_2',
            'click_1_time',
            'click_2_time',
        ]);

        $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalColumns);

        $output  = $result['output'];
        $rResult = $result['rResult'];

        $category = $this->ci->input->post('lead_category');
        if (empty($category) || $category === 'ads_excel_list') {
            $show_count = get_option('excel_lead_show_count') ? (int)get_option('excel_lead_show_count') : 30;
            if ($show_count !== -1 && $show_count > 0) {
                $start = (int)$this->ci->input->post('start');
                $allowed = $show_count - $start;
                if ($allowed <= 0) {
                    $rResult = [];
                } else {
                    $rResult = array_slice($rResult, 0, $allowed);
                }
                $output['iTotalRecords'] = min((int)$output['iTotalRecords'], $show_count);
                $output['iTotalDisplayRecords'] = min((int)$output['iTotalDisplayRecords'], $show_count);
                $output['recordsTotal'] = min((int)$output['iTotalRecords'], $show_count);
                $output['recordsFiltered'] = min((int)$output['iTotalDisplayRecords'], $show_count);
            }
        }

        $startIndex = (int)$this->ci->input->post('start') + 1;

        foreach ($rResult as $aRow) {
            $row = [];

            $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['id'] . '"><label></label></div>';

            $hrefAttr = 'href="' . admin_url('leads/index/' . $aRow['id']) . '" onclick="init_lead(' . $aRow['id'] . ');return false;"';
            $row[]    = '<a ' . $hrefAttr . ' class="tw-font-medium">' . $startIndex++ . '</a>';

            $nameRow = '<a ' . $hrefAttr . ' class="tw-font-medium">' . e($aRow['name']) . '</a>';

            $nameRow .= '<div class="row-options">';
            $nameRow .= '<a ' . $hrefAttr . '>' . _l('view') . '</a>';

            $locked = false;

            if ($aRow['is_converted'] > 0) {
                $locked = ((! is_admin() && $lockAfterConvert == 1) ? true : false);
            }

            if (! $locked) {
                $nameRow .= ' | <a href="' . admin_url('leads/index/' . $aRow['id'] . '?edit=true') . '" onclick="init_lead(' . $aRow['id'] . ', true);return false;">' . _l('edit') . '</a>';
            }

            if ($aRow['addedfrom'] == get_staff_user_id() || $has_permission_delete) {
                $nameRow .= ' | <a href="' . admin_url('leads/delete/' . $aRow['id']) . '" class="_delete text-danger">' . _l('delete') . '</a>';
            }
            $nameRow .= '</div>';

            $row[] = $nameRow;

            if (is_gdpr() && $consentLeads == '1') {
                $consentHTML = '<p class="bold"><a href="#" onclick="view_lead_consent(' . $aRow['id'] . '); return false;">' . _l('view_consent') . '</a></p>';
                $consents    = $this->ci->gdpr_model->get_consent_purposes($aRow['id'], 'lead');

                foreach ($consents as $consent) {
                    $consentHTML .= '<p style="margin-bottom:0px;">' . e($consent['name']) . (! empty($consent['consent_given']) ? '<i class="fa fa-check text-success pull-right"></i>' : '<i class="fa fa-remove text-danger pull-right"></i>') . '</p>';
                }
                $row[] = $consentHTML;
            }
            $row[] = !empty($aRow['loan_type']) ? e($aRow['loan_type']) : (!empty($aRow['company']) ? e($aRow['company']) : '<span class="text-muted">—</span>');

            $row[] = ($aRow['email'] != '' ? '<a href="mailto:' . e($aRow['email']) . '">' . e($aRow['email']) . '</a>' : '');

            $row[] = ($aRow['phonenumber'] != '' ? '<a href="#" class="lead-phone-click" data-id="' . $aRow['id'] . '" data-phone="' . e($aRow['phonenumber']) . '">' . e($aRow['phonenumber']) . '</a>' : '');

            $base_currency = get_base_currency();
            if (!empty($aRow['lead_value']) && $aRow['lead_value'] > 0) {
                $val = (float)$aRow['lead_value'];
                if ($val >= 100000) {
                    $lakh_val = $val / 100000;
                    $lakh_formatted = (floor($lakh_val) == $lakh_val) ? number_format($lakh_val, 0) : number_format($lakh_val, 1);
                    $formatted_val = '₹' . $lakh_formatted . ' Lakh';
                } else {
                    $formatted_val = app_format_money($aRow['lead_value'], $base_currency->id);
                    $formatted_val = str_replace('â‚¹', '₹', $formatted_val);
                }
                $row[] = '<span class="tw-font-medium">' . $formatted_val . '</span>';
            } else {
                $row[] = '<span class="text-muted">—</span>';
            }

            $row[] .= render_tags($aRow['tags']);

            $assignedOutput = '';
            if ($aRow['assigned'] != 0) {
                $full_name = e($aRow['assigned_firstname'] . ' ' . $aRow['assigned_lastname']);

                $assignedOutput = '<a data-toggle="tooltip" data-title="' . $full_name . '" href="' . admin_url('profile/' . $aRow['assigned']) . '">' . staff_profile_image($aRow['assigned'], [
                    'staff-profile-image-small',
                ]) . '</a>';

                // For exporting
                $assignedOutput .= '<span class="hide">' . $full_name . '</span>';
            }

            $row[] = $assignedOutput;

            $outputStatus = '';

            if ($aRow['status_name'] == null) {
                if ($aRow['lost'] == 1) {
                    $outputStatus = '<span class="label label-danger">' . _l('lead_lost') . '</span>';
                } elseif ($aRow['junk'] == 1) {
                    $outputStatus = '<span class="label label-warning">' . _l('lead_junk') . '</span>';
                }
            } else {
                if (! $locked) {
                    $outputStatus .= '<div class="dropdown inline-block">';
                    $outputStatus .= '<a href="#" class="dropdown-toggle tw-flex tw-items-center tw-gap-1 tw-flex-nowrap hover:tw-opacity-80 tw-align-middle lead-status-' . $aRow['status'] . ' label' . (empty($aRow['color']) ? ' label-default' : '') . '" style="color:' . $aRow['color'] . ';border:1px solid ' . adjust_hex_brightness($aRow['color'], 0.4) . ';background: ' . adjust_hex_brightness($aRow['color'], 0.04) . ';" id="tableLeadsStatus-' . $aRow['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                    $outputStatus .= e($aRow['status_name']);
                    $outputStatus .= '<i class="chevron"></i>';
                    $outputStatus .= '</a>';

                    $outputStatus .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsStatus-' . $aRow['id'] . '">';

                    foreach ($statuses as $leadChangeStatus) {
                        if ($aRow['status'] != $leadChangeStatus['id']) {
                            $outputStatus .= '<li>
                          <a href="#" onclick="lead_mark_as(' . $leadChangeStatus['id'] . ',' . $aRow['id'] . '); return false;">
                             ' . e($leadChangeStatus['name']) . '
                          </a>
                       </li>';
                        }
                    }
                    $outputStatus .= '</ul>';
                    $outputStatus .= '</div>';
                } else {
                    $outputStatus = '<span class="lead-status-' . $aRow['status'] . ' label' . (empty($aRow['color']) ? ' label-default' : '') . '" style="color:' . $aRow['color'] . ';border:1px solid ' . adjust_hex_brightness($aRow['color'], 0.4) . ';background: ' . adjust_hex_brightness($aRow['color'], 0.04) . ';">' . e($aRow['status_name']) . '</span>';
                }
            }

            $row[] = $outputStatus;

            // Notes inline editable column
            $row[] = '<div style="position:relative; min-width: 180px;">
                <textarea class="lead-notes-textarea form-control" data-id="' . $aRow['id'] . '" style="width: 100%; border: 1px solid #ccd0d4; border-radius: 4px; padding: 4px 8px; font-size: 12.5px; resize: vertical; min-height: 42px; line-height: 1.4;">' . e($aRow['description']) . '</textarea>
                <span class="save-indicator text-success" style="display:none; position:absolute; bottom:4px; right:8px; font-size:10px;"><i class="fa fa-check"></i> Saved</span>
            </div>';

            $row[] = e($aRow['source_name']);

            $row[] = ($aRow['lastcontact'] == '0000-00-00 00:00:00' || ! is_date($aRow['lastcontact']) ? '' : '<span data-toggle="tooltip" data-title="' . e(_dt($aRow['lastcontact'])) . '" class="text-has-action is-date">' . e(time_ago($aRow['lastcontact'])) . '</span>');

            $row[] = '<span data-toggle="tooltip" data-title="' . e(_dt($aRow['dateadded'])) . '" class="text-has-action is-date">' . e(time_ago($aRow['dateadded'])) . '</span>';

            if (is_admin()) {
                $light1_color = ($aRow['click_1'] == 1) ? '#25d366' : '#bbb';
                $light2_color = ($aRow['click_2'] == 1) ? '#25d366' : '#bbb';
                
                $light1_time = '';
                if ($aRow['click_1'] == 1 && !empty($aRow['click_1_time'])) {
                    $light1_time = '<br><span style="font-size:10px; color:#777; display:block; margin-top:2px;" data-toggle="tooltip" data-title="' . e(_dt($aRow['click_1_time'])) . '">' . e(time_ago($aRow['click_1_time'])) . '</span>';
                }
                
                $light2_time = '';
                if ($aRow['click_2'] == 1 && !empty($aRow['click_2_time'])) {
                    $light2_time = '<br><span style="font-size:10px; color:#777; display:block; margin-top:2px;" data-toggle="tooltip" data-title="' . e(_dt($aRow['click_2_time'])) . '">' . e(time_ago($aRow['click_2_time'])) . '</span>';
                }

                $light1 = '<div style="text-align:center; min-width:40px;"><span style="display:inline-block; width:12px; height:12px; border-radius:50%; background-color:' . $light1_color . ';" title="Phone number clicked"></span><span style="font-size:11px; margin-left:3px; font-weight:bold; vertical-align:middle;">1</span>' . $light1_time . '</div>';
                $light2 = '<div style="text-align:center; min-width:40px;"><span style="display:inline-block; width:12px; height:12px; border-radius:50%; background-color:' . $light2_color . ';" title="Popup action clicked"></span><span style="font-size:11px; margin-left:3px; font-weight:bold; vertical-align:middle;">2</span>' . $light2_time . '</div>';
                
                $row[] = '<div style="display:flex; align-items:flex-start; gap:15px;">' . $light1 . $light2 . '</div>';
            }

            // Details Button & WhatsApp Button
            $detailsBtn = '<button type="button" class="btn btn-info btn-xs mbot5" style="display:block; width:100%;" onclick="initLeadLoanDetails(' . $aRow['id'] . '); return false;"><i class="fa fa-edit"></i> Details</button>';
            
            $wasWpSent = total_rows('cold_wp_messages', ['lead_id' => $aRow['id']]) > 0;
            if ($wasWpSent) {
                $wpBtn = '<button type="button" class="btn btn-default btn-xs send-single-wp" style="display:block; width:100%; background-color:#dcdcdc; color:#777;" title="sended" data-id="' . $aRow['id'] . '" data-name="' . e($aRow['name']) . '" data-company="' . e($aRow['company']) . '" data-phone="' . e($aRow['phonenumber']) . '"><i class="fa fa-refresh"></i> Re-send</button>';
            } else {
                $wpBtn = '<button type="button" class="btn btn-success btn-xs send-single-wp" style="display:block; width:100%; background-color:#25d366; border-color:#25d366; color:#fff;" data-id="' . $aRow['id'] . '" data-name="' . e($aRow['name']) . '" data-company="' . e($aRow['company']) . '" data-phone="' . e($aRow['phonenumber']) . '"><i class="fa fa-whatsapp"></i> WhatsApp</button>';
            }
            
            $row[] = $detailsBtn . $wpBtn;

            // Custom fields add values
            foreach ($customFieldsColumns as $customFieldColumn) {
                $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
            }

            $row['DT_RowId']    = 'lead_' . $aRow['id'];
            $row['DT_RowClass'] = 'has-border-left';

            if ($aRow['assigned'] == get_staff_user_id()) {
                $row['DT_RowClass'] .= ' row-border-info';
            }

            if (isset($row['DT_RowClass'])) {
                $row['DT_RowClass'] .= ' has-row-options';
            } else {
                $row['DT_RowClass'] = 'has-row-options';
            }

            $row = hooks()->apply_filters('leads_table_row_data', $row, $aRow);

            $output['aaData'][] = $row;
        }

        return $output;
    })->setRules($rules);
