<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once(APPPATH . 'libraries/import/App_import.php');

class Import_leads extends App_import
{
    private $uniqueValidationFields = [];

    private $customFieldsValues = [];

    protected $notImportableFields = [];

    protected $requiredFields = ['name'];

    protected $sources;

    protected $statuses;

    public function __construct()
    {
        $this->notImportableFields = hooks()->apply_filters('not_importable_leads_fields', ['id', 'assigned', 'dateadded', 'last_status_change', 'addedfrom', 'leadorder', 'date_converted', 'lost', 'junk', 'is_imported_from_email_integration', 'email_integration_uid', 'is_public', 'dateassigned', 'client_id', 'lastcontact', 'last_lead_status', 'from_form_id', 'default_language', 'hash', 'click_1', 'click_2', 'click_1_time', 'click_2_time']);

        $uniqueValidationFields = json_decode(get_option('lead_unique_validation'));

        if (count($uniqueValidationFields) > 0) {
            $this->uniqueValidationFields = $uniqueValidationFields;
            $message                      = '';

            foreach ($uniqueValidationFields as $key => $field) {
                if ($key === 0) {
                    $message .= 'Based on your leads <b class="text-danger">unique validation</b> configured <a href="' . admin_url('settings?group=leads#unique_validation_wrapper') . '" target="_blank">options</a>, the lead <b>won\'t</b> be imported if:<br />';
                }

                $message .= '<br />&nbsp;&nbsp;&nbsp; - Lead <b>' . $field . '</b> already exists OR';
            }

            if ($message != '') {
                $message = substr($message, 0, -3);
            }

            $message .= '<br /><br />If you still want to import all leads, uncheck all unique validation field';

            $this->addImportGuidelinesInfo($message);
        }

        parent::__construct();

        $this->sources  = $this->ci->db->get('leads_sources')->result_array();
        $this->statuses = $this->ci->db->get('leads_status')->result_array();
    }

    public function perform()
    {
        $this->initialize();

        $databaseFields      = $this->getImportableDatabaseFields();
        $customFields        = $this->getCustomFields();
        $totalDatabaseFields = count($databaseFields);

        // Common shorthand / alias column names → actual DB field names
        $fieldAliases = [
            'number'        => 'phonenumber',
            'phone'         => 'phonenumber',
            'phone number'  => 'phonenumber',
            'mobile'        => 'phonenumber',
            'mobile number' => 'phonenumber',
            'contact'       => 'phonenumber',
            'contact number'=> 'phonenumber',
            'business'      => 'company',
            'company name'  => 'company',
            'organisation'  => 'company',
            'organization'  => 'company',
            'full name'     => 'name',
            'client name'   => 'name',
            'lead name'     => 'name',
            'customer name' => 'name',
            'mail'          => 'email',
            'email address' => 'email',
            'e-mail'        => 'email',
            'website url'   => 'website',
            'web'           => 'website',
            'section'       => 'batch_name',
            'batch'         => 'batch_name',
            'group'         => 'batch_name',
        ];

        $colMapping = [];
        $headers = $this->getHeaders();
        if (!empty($headers)) {
            foreach ($headers as $index => $header) {
                $header = trim(strtolower($header));
                if (empty($header)) continue;

                $matched = false;
                foreach ($databaseFields as $dbField) {
                    $fieldLabel = trim(strtolower($this->formatFieldNameForHeading($dbField)));
                    if ($header === trim(strtolower($dbField)) || $header === $fieldLabel) {
                        $colMapping[$index] = ['type' => 'db', 'field' => $dbField];
                        $matched = true;
                        break;
                    }
                }

                if (!$matched) {
                    foreach ($customFields as $cf) {
                        if ($header === trim(strtolower($cf['name']))) {
                            $colMapping[$index] = ['type' => 'custom', 'field' => $cf];
                            $matched = true;
                            break;
                        }
                    }
                }

                // Alias fallback: map common shorthand names to DB fields
                if (!$matched && isset($fieldAliases[$header])) {
                    $targetField = $fieldAliases[$header];
                    if (in_array($targetField, $databaseFields)) {
                        $colMapping[$index] = ['type' => 'db', 'field' => $targetField];
                        $matched = true;
                    }
                }
            }
        }

        if (empty($colMapping)) {
            // Fallback to index-based mapping
            foreach ($databaseFields as $i => $dbField) {
                $colMapping[$i] = ['type' => 'db', 'field' => $dbField];
            }
            foreach ($customFields as $i => $cf) {
                $colMapping[$totalDatabaseFields + $i] = ['type' => 'custom', 'field' => $cf];
            }
        }

        foreach ($this->getRows() as $rowNumber => $row) {
            $insert = [];
            $rowCustomFields = [];

            foreach ($colMapping as $index => $mapInfo) {
                if (!isset($row[$index])) continue;

                $val = $this->checkNullValueAddedByUser($row[$index]);

                if ($mapInfo['type'] === 'db') {
                    $dbField = $mapInfo['field'];
                    if ($dbField == 'name' && empty($val)) {
                        $val = '/';
                    } elseif ($dbField == 'country') {
                        $val = $this->countryValue($val);
                    } elseif ($dbField == 'source') {
                        $val = $this->sourceValue($val);
                    } elseif ($dbField == 'status') {
                        $val = $this->statusValue($val);
                    }
                    $insert[$dbField] = $val;
                } elseif ($mapInfo['type'] === 'custom') {
                    $cf = $mapInfo['field'];
                    $rowCustomFields[$cf['id']] = [
                        'value' => $val,
                    ];
                }
            }

            if (!isset($insert['name']) || empty($insert['name'])) {
                $insert['name'] = '/';
            }

            $insert = $this->trimInsertValues($insert);

            if (count($insert) > 0) {
                if ($this->isDuplicateLead($insert)) {
                    continue;
                }

                $this->incrementImported();

                $id = null;
                $this->customFieldsValues[$rowNumber] = $rowCustomFields;

                if (!$this->isSimulation()) {
                    if (!isset($insert['dateadded'])) {
                        $insert['dateadded'] = date('Y-m-d H:i:s');
                    }

                    if (!isset($insert['addedfrom'])) {
                        $insert['addedfrom'] = get_staff_user_id();
                    }

                    if ($this->ci->input->post('responsible')) {
                        $insert['assigned'] = $this->ci->input->post('responsible');
                    }

                    if ($this->ci->input->post('batch_name')) {
                        $insert['batch_name'] = $this->ci->input->post('batch_name');
                    }

                    $tags = '';

                    if (isset($insert['tags']) || is_null($insert['tags'])) {
                        if (!is_null($insert['tags'])) {
                            $tags = $insert['tags'];
                        }

                        unset($insert['tags']);
                    }

                    $this->ci->db->insert('leads', $insert);
                    $id = $this->ci->db->insert_id();

                    if ($id) {
                        handle_tags_save($tags, $id, 'lead');
                    }
                } else {
                    $this->simulationData[$rowNumber] = $this->formatValuesForSimulation($insert);
                }

                $dummy = 0;
                $this->handleCustomFieldsInsert($id, $row, $dummy, $rowNumber, 'leads');
            }

            if ($this->isSimulation() && $rowNumber >= $this->maxSimulationRows) {
                break;
            }
        }
    }

    protected function findSource($id)
    {
        foreach ($this->sources as $source) {
            if ($source['name'] == $id || $source['id'] == $id) {
                return $source;
            }
        }
    }

    protected function findStatus($id)
    {
        foreach ($this->statuses as $status) {
            if ($status['name'] == $id || $status['id'] == $id) {
                return $status;
            }
        }
    }

    protected function statusValue($value)
    {
        return $this->findStatus($value)['id'] ?? $this->ci->input->post('status');
    }

    protected function sourceValue($value)
    {
        return $this->findSource($value)['id'] ?? $this->ci->input->post('source');
    }

    protected function tags_formatSampleData()
    {
        return 'tag1,tag2';
    }

    public function formatFieldNameForHeading($field)
    {
        if (strtolower($field) == 'title') {
            return 'Position';
        }

        return parent::formatFieldNameForHeading($field);
    }

    protected function email_formatSampleData()
    {
        return uniqid() . '@example.com';
    }

    protected function failureRedirectURL()
    {
        return admin_url('leads/import');
    }

    private function isDuplicateLead($data)
    {
        foreach ($this->uniqueValidationFields as $field) {
            if ((isset($data[$field]) && $data[$field] != '') && total_rows('leads', [$field => $data[$field]]) > 0) {
                return true;
            }
        }

        return false;
    }

    private function formatValuesForSimulation($values)
    {
        foreach ($values as $column => $val) {
            if ($column == 'country' && !empty($val) && is_numeric($val)) {
                $country = $this->getCountry(null, $val);
                if ($country) {
                    $values[$column] = $country->short_name;
                }
            } elseif ($column == 'source') {
                $values[$column] = $this->findSource($val)['name'] ?? 'N/A';
            } elseif ($column == 'status') {
                $values[$column] = $this->findStatus($val)['name'] ?? 'N/A';
            }
        }

        return $values;
    }

    private function getCountry($search = null, $id = null)
    {
        if ($search) {
            $this->ci->db->where('iso2', $search)
            ->or_where('short_name', $search)
            ->or_where('long_name', $search);
        } else {
            $this->ci->db->where('country_id', $id);
        }

        return  $this->ci->db->get('countries')->row();
    }

    private function countryValue($value)
    {
        if ($value != '') {
            if (!is_numeric($value)) {
                $country = $this->getCountry($value);
                $value   = $country ? $country->country_id : 0;
            }
        } else {
            $value = 0;
        }

        return $value;
    }

    protected function handleCustomFieldsInsert($rel_id, $row, &$fieldNumber, $rowNumber, $customFieldTo)
    {
        $rowCustomFields = $this->customFieldsValues[$rowNumber] ?? [];
        foreach ($this->getCustomFields() as $field) {
            $value = '';
            if (isset($rowCustomFields[$field['id']])) {
                $value = $rowCustomFields[$field['id']]['value'];
            }

            if ($this->isSimulation()) {
                $this->simulationData[$rowNumber][$field['name']] = $value;
                continue;
            }

            if ($value != '' && $value !== 'NULL' && $value !== 'null') {
                if ($field['type'] === 'link' && !\app\services\utilities\Str::isHtml($value)) {
                    $value = sprintf('<a href="%s" target="_blank">%s</a>', $value, $value);
                }
                $customFieldData = [
                    'relid'   => $rel_id,
                    'fieldid' => $field['id'],
                    'value'   => trim($value),
                    'fieldto' => $customFieldTo,
                ];
                $this->ci->db->insert(db_prefix() . 'customfieldsvalues', $customFieldData);
            }
        }
    }
}
