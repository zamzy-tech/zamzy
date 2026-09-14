<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Ads_wp_leads extends AdminController
{
    private $source_name = 'Ads WhatsApp';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('leads_model');

        // Ensure the "Ads WhatsApp" lead source exists
        $this->db->where('name', $this->source_name);
        $exists = $this->db->get(db_prefix() . 'leads_sources')->row();
        if (!$exists) {
            $this->db->insert(db_prefix() . 'leads_sources', ['name' => $this->source_name]);
        }
    }

    /**
     * Main listing page
     */
    public function index()
    {
        if (!is_admin()) {
            access_denied('Ads WhatsApp Leads');
        }

        // Get source ID
        $this->db->where('name', $this->source_name);
        $source_row = $this->db->get(db_prefix() . 'leads_sources')->row();
        $source_id  = $source_row ? $source_row->id : null;

        // Filters
        $filter_date   = $this->input->get('filter_date');
        $filter_batch  = $this->input->get('filter_batch');
        $filter_search = $this->input->get('search');

        // Build query
        $this->db->select(db_prefix() . 'leads.*, ' . db_prefix() . 'leads_status.name as status_name');
        $this->db->join(db_prefix() . 'leads_status', db_prefix() . 'leads_status.id = ' . db_prefix() . 'leads.status', 'left');
        $this->db->where(db_prefix() . 'leads.source', $source_id);

        if ($filter_date) {
            $this->db->where('DATE(' . db_prefix() . 'leads.dateadded)', $filter_date);
        }
        if ($filter_batch) {
            $this->db->where(db_prefix() . 'leads.batch_name', $filter_batch);
        }
        if ($filter_search) {
            $search = $this->db->escape_like_str($filter_search);
            $this->db->where('(' . db_prefix() . 'leads.name LIKE "%' . $search . '%" ESCAPE \'!\' OR '
                . db_prefix() . 'leads.phonenumber LIKE "%' . $search . '%" ESCAPE \'!\' OR '
                . db_prefix() . 'leads.company LIKE "%' . $search . '%" ESCAPE \'!\')');
        }

        $this->db->order_by(db_prefix() . 'leads.dateadded', 'DESC');
        $leads = $this->db->get(db_prefix() . 'leads')->result_array();

        // Stats
        $today_count = 0;
        $week_count  = 0;
        $today       = date('Y-m-d');
        $week_start  = date('Y-m-d', strtotime('monday this week'));
        foreach ($leads as $l) {
            $ld = date('Y-m-d', strtotime($l['dateadded']));
            if ($ld === $today) $today_count++;
            if ($ld >= $week_start) $week_count++;
        }

        // Distinct batches for filter dropdown
        $batches = $this->db->select('DISTINCT(batch_name)')
            ->where('source', $source_id)
            ->where('batch_name IS NOT NULL')
            ->where('batch_name !=', '')
            ->order_by('batch_name', 'asc')
            ->get(db_prefix() . 'leads')
            ->result_array();

        $statuses = $this->leads_model->get_status();

        $data['leads']         = $leads;
        $data['statuses']      = $statuses;
        $data['batches']       = $batches;
        $data['source_id']     = $source_id;
        $data['source_name']   = $this->source_name;
        $data['today_count']   = $today_count;
        $data['week_count']    = $week_count;
        $data['filter_date']   = $filter_date;
        $data['filter_batch']  = $filter_batch;
        $data['filter_search'] = $filter_search;
        $data['title']         = 'Ads WhatsApp Leads';

        $this->load->view('admin/ads_wp_leads/index', $data);
    }

    /**
     * Delete a lead (AJAX)
     */
    public function delete_lead($id)
    {
        if (!is_admin()) {
            ajax_access_denied();
        }
        $id = (int) $id;
        if ($id > 0) {
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'leads');
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
}
