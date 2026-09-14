<?php

defined('BASEPATH') or exit('No direct script access allowed');

return App_table::find('converted_leads')
    ->outputUsing(function ($params) {
        extract($params);

        $this->ci->load->model('leads_model');

        // Fetch the statuses that represent "Converted Leads" (including Contacted / Interested)
        $statuses_res = $this->ci->db->select('id, name, color')->where_in('name', [
            'Contacted / Interested',
            'Converted / Deal Closed',
            'Printed',
            'Hand to Kiran',
            'Hand to Bank',
            'Need to Reupload',
            'Bank Approved'
        ])->get(db_prefix() . 'leads_status')->result_array();

        $status_ids = array_column($statuses_res, 'id');
        $statuses = $this->ci->leads_model->get_status();

        $aColumns = [
            db_prefix() . 'leads.status as status_id',
            db_prefix() . 'leads.name as name',
            db_prefix() . 'leads.phonenumber as phonenumber',
            db_prefix() . 'lead_loan_details.loan_type as loan_type',
            db_prefix() . 'leads.id as id', // For actions
        ];

        $sIndexColumn = 'id';
        $sTable       = db_prefix() . 'leads';

        $join = [
            'LEFT JOIN ' . db_prefix() . 'leads_status ON ' . db_prefix() . 'leads_status.id = ' . db_prefix() . 'leads.status',
            'LEFT JOIN ' . db_prefix() . 'lead_loan_details ON ' . db_prefix() . 'lead_loan_details.lead_id = ' . db_prefix() . 'leads.id',
        ];

        $where = [];

        if (empty($status_ids)) {
            $where[] = 'AND ' . db_prefix() . 'leads.client_id != 0';
        } else {
            $where[] = 'AND (' . db_prefix() . 'leads.client_id != 0 OR ' . db_prefix() . 'leads.status IN (' . implode(',', $status_ids) . '))';
        }

        // Access permission logic
        if (staff_cant('view', 'leads')) {
            array_push($where, 'AND (assigned =' . get_staff_user_id() . ' OR addedfrom = ' . get_staff_user_id() . ' OR is_public = 1)');
        }

        $additionalColumns = [
            'hash',
            db_prefix() . 'leads_status.name as status_name',
            db_prefix() . 'leads_status.color as status_color',
            db_prefix() . 'leads.client_id as client_id',
        ];

        $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalColumns);

        $output  = $result['output'];
        $rResult = $result['rResult'];

        foreach ($rResult as $aRow) {
            $row = [];

            // 1. Status Dropdown Column
            $status_color = $aRow['status_color'] ?: '#777777';
            $outputStatus = '';
            $outputStatus .= '<div class="dropdown inline-block">';
            $outputStatus .= '<a href="#" class="dropdown-toggle label" style="color:' . $status_color . ';border:1px solid ' . adjust_hex_brightness($status_color, 0.4) . ';background: ' . adjust_hex_brightness($status_color, 0.04) . ';padding: 5px 10px;" id="tableLeadsStatus-' . $aRow['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
            $outputStatus .= e($aRow['status_name'] ?: 'Converted');
            $outputStatus .= ' <i class="fa fa-caret-down"></i>';
            $outputStatus .= '</a>';

            $outputStatus .= '<ul class="dropdown-menu" aria-labelledby="tableLeadsStatus-' . $aRow['id'] . '">';
            foreach ($statuses as $leadChangeStatus) {
                if ($aRow['status_id'] != $leadChangeStatus['id']) {
                    $outputStatus .= '<li>
                        <a href="#" onclick="custom_lead_mark_as(' . $leadChangeStatus['id'] . ',' . $aRow['id'] . '); return false;">
                            ' . e($leadChangeStatus['name']) . '
                        </a>
                    </li>';
                }
            }
            $outputStatus .= '</ul>';
            $outputStatus .= '</div>';
            $row[] = $outputStatus;

            // 2. Name
            $hrefAttr = 'href="' . admin_url('leads/index/' . $aRow['id']) . '" onclick="init_lead(' . $aRow['id'] . ');return false;"';
            $row[] = '<a ' . $hrefAttr . ' class="tw-font-medium">' . e($aRow['name']) . '</a>';

            // 3. Number
            $row[] = ($aRow['phonenumber'] != '' ? '<a href="#" class="lead-phone-click" data-phone="' . e($aRow['phonenumber']) . '">' . e($aRow['phonenumber']) . '</a>' : 'N/A');

            // 4. Load Type
            $row[] = $aRow['loan_type'] ? e($aRow['loan_type']) : 'N/A';

            // 5. Actions Column (Upload, Print, Copy Link)
            $actions = '';
            
            // Details (includes Upload)
            $actions .= '<button type="button" class="btn btn-info btn-xs mright5" onclick="initLeadLoanDetails(' . $aRow['id'] . ');"><i class="fa fa-edit"></i> Details</button>';
            
            // Print
            $actions .= '<a href="' . admin_url('leads/print_lead_details/' . $aRow['id']) . '" target="_blank" class="btn btn-default btn-xs mright5"><i class="fa fa-print"></i> Print</a>';
            
            // Copy Link
            $portal_url = site_url('document_share/login/' . $aRow['hash']);
            $actions .= '<button type="button" class="btn btn-success btn-xs copy-link-btn" data-link="' . $portal_url . '"><i class="fa fa-copy"></i> Copy Link</button>';

            $row[] = $actions;

            $row['DT_RowId'] = 'lead_' . $aRow['id'];
            $row['DT_RowClass'] = 'has-row-options';
            
            $output['aaData'][] = $row;
        }

        return $output;
    });
