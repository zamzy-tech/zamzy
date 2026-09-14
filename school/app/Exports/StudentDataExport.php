<?php

namespace App\Exports;

use App\Imports\FormFieldValuesSheet;
use App\Repositories\FormField\FormFieldsInterface;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

class StudentDataExport implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStrictNullComparison, WithMultipleSheets {
    protected mixed $results;
    protected Collection $formFields;

    public function __construct() {
        $formFieldsInterface = app(FormFieldsInterface::class);
        $this->formFields = $formFieldsInterface->all(['name', 'type', 'default_values', 'is_required', 'user_type']);
    }

    public function title(): string {
        return 'Main Sheet For Admission';
    }

    public function headings(): array {
        $columns = [
            'class_section_id',
            'admission_no',
            'student_full_name',
            'gender',
            'date_of_birth',
            'admission_date',
            'pen_no',
            'current_address',
            'permanent_address',
            'father_and_mother',
            'father_full_name',
            'father_phone_number',
            'mother_full_name',
            'mother_phone_number',
            'apply_class_fee',
            'apply_van_fee',
            'previous_year_balance',
            'admission_amount_paid',
            'admission_fee',
            'total_fees',
            'paid_fees',
            'balance_fees',
        ];
        return $columns;
    }

    public function sheets(): array {
        $sheets = [];

        // add the main data sheet
        $sheets[] = new StudentDataExport();

        // add a new sheet for the form field values
        $sheets[] = new FormFieldValuesSheet($this->formFields);

        // add a sheet listing available classes for reference
        $sheets[] = new ClassSectionsSheet();

        return $sheets;
    }

    private function getActionItems() {
        $fields = [
            '1',
            '1001',
            'Student Full Name',
            'male / female',
            date('d-m-Y'),
            date('d-m-Y'),
            'PEN12345',
            'Current Address Details',
            'Permanent Address Details',
            'father / mother / father & mother',
            'Father Full Name',
            '9876543210',
            'Mother Full Name',
            '9876543211',
            'Yes / No',
            'No Van / Van 1 / Van 2',
            '0.00',
            '0',
            'Yes / No',
            '(auto-calculated)',
            '0',
            '(auto-calculated)',
        ];
        return $fields;
    }

    public function collection() {
        // store the results for later use
        $this->results = $this->getActionItems();

        return collect(array($this->results));
    }
}

class ClassSectionsSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize {
    public function title(): string {
        return 'Available Class Sections';
    }

    public function headings(): array {
        return [
            'Class Section ID',
            'Class Section Name',
            'Class Name',
            'Section Name',
            'Medium Name'
        ];
    }

    public function collection() {
        $classSections = \App\Models\ClassSection::owner()->with('class', 'section', 'medium')->get();
        
        $data = [];
        foreach ($classSections as $cs) {
            $data[] = [
                'id' => $cs->id,
                'full_name' => $cs->full_name,
                'class_name' => $cs->class->name ?? '',
                'section_name' => $cs->section->name ?? '',
                'medium_name' => $cs->medium->name ?? ''
            ];
        }
        
        return collect($data);
    }
}
