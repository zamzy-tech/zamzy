@extends('layouts.master')

@section('title')
    {{ __('Generate Hall Ticket') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('Generate Hall Ticket') }}
            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('Search / Filter') }}
                        </h4>
                        
                        <!-- Search Form (GET) -->
                        <form method="GET" action="{{ route('exams.hall-ticket') }}" id="search-form">
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-5">
                                    <label for="class_section_id">{{ __('class_section') }} <span class="text-danger">*</span></label>
                                    <select name="class_section_id" id="class_section_id" required class="form-control">
                                        <option value="">-- {{ __('select_class_section') }} --</option>
                                        @foreach ($classSections as $cs)
                                            <option value="{{ $cs->id }}" {{ $selectedClassSectionId == $cs->id ? 'selected' : '' }}>
                                                {{ $cs->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-12 col-md-5">
                                    <label for="exam_id">{{ __('exams') }} <span class="text-danger">*</span></label>
                                    <select name="exam_id" id="exam_id" required class="form-control">
                                        <option value="">-- {{ __('select') . ' ' . __('exam') }} --</option>
                                        @foreach ($exams as $exam)
                                            <option value="{{ $exam->id }}" {{ $selectedExamId == $exam->id ? 'selected' : '' }}>
                                                {{ $exam->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-sm-12 col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-theme w-100">{{ __('Search') }}</button>
                                </div>
                            </div>
                        </form>

                        @if ($selectedClassSectionId && $selectedExamId)
                            <hr class="mt-4 mb-4">
                            
                            <!-- Generation Form (POST) -->
                            <form method="POST" action="{{ route('exams.hall-ticket.generate') }}" target="_blank" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="class_section_id" value="{{ $selectedClassSectionId }}">
                                <input type="hidden" name="exam_id" value="{{ $selectedExamId }}">

                                <h4 class="card-title mt-4">
                                    {{ __('Students List') }}
                                </h4>

                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th width="40px">
                                                    <div class="form-check m-0">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" id="select-all-students" checked>
                                                            <i class="input-helper"></i>
                                                        </label>
                                                    </div>
                                                </th>
                                                <th>{{ __('no.') }}</th>
                                                <th>{{ __('admission_no') }}</th>
                                                <th>{{ __('roll_no') }}</th>
                                                <th>{{ __('name') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($students as $index => $student)
                                                <tr>
                                                    <td>
                                                        <div class="form-check">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="form-check-input student-checkbox" checked>
                                                                <i class="input-helper"></i>
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $student->admission_no }}</td>
                                                    <td>{{ $student->roll_number }}</td>
                                                    <td>{{ $student->user->full_name }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">{{ __('No students found for this class section') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if ($students->isNotEmpty())
                                    <hr class="mt-4 mb-4">
                                    {{-- Signature Configuration Section --}}
                                    <h4 class="card-title">
                                        <i class="fa fa-pen-nib"></i> {{ __('Signature Configuration') }}
                                    </h4>
                                    <p class="text-muted" style="font-size: 13px;">Add signature slots to the hall ticket. You can add multiple signatures (e.g., Student, Class Teacher, Principal, HOD). Optionally upload a signature image for each.</p>

                                    <div id="signatures-container">
                                        {{-- Default signature slots --}}
                                        <div class="signature-row row mb-3 align-items-end" data-index="0">
                                            <div class="form-group col-md-4 mb-0">
                                                <label>{{ __('Label') }} <span class="text-danger">*</span></label>
                                                <input type="text" name="signatures[0][label]" class="form-control" value="Student's Signature" placeholder="e.g. Student's Signature" required>
                                            </div>
                                            <div class="form-group col-md-3 mb-0">
                                                <label>{{ __('Alignment') }}</label>
                                                <select name="signatures[0][align]" class="form-control">
                                                    <option value="left" selected>Left</option>
                                                    <option value="center">Center</option>
                                                    <option value="right">Right</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4 mb-0">
                                                <label>{{ __('Signature Image') }} <small class="text-muted">(Optional)</small></label>
                                                <input type="file" name="signatures[0][image]" class="form-control" accept="image/*">
                                            </div>
                                            <div class="form-group col-md-1 mb-0 text-center">
                                                <button type="button" class="btn btn-sm btn-danger remove-signature-btn" title="Remove">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="signature-row row mb-3 align-items-end" data-index="1">
                                            <div class="form-group col-md-4 mb-0">
                                                <label>{{ __('Label') }} <span class="text-danger">*</span></label>
                                                <input type="text" name="signatures[1][label]" class="form-control" value="Class Teacher" placeholder="e.g. Class Teacher" required>
                                            </div>
                                            <div class="form-group col-md-3 mb-0">
                                                <label>{{ __('Alignment') }}</label>
                                                <select name="signatures[1][align]" class="form-control">
                                                    <option value="left">Left</option>
                                                    <option value="center" selected>Center</option>
                                                    <option value="right">Right</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4 mb-0">
                                                <label>{{ __('Signature Image') }} <small class="text-muted">(Optional)</small></label>
                                                <input type="file" name="signatures[1][image]" class="form-control" accept="image/*">
                                            </div>
                                            <div class="form-group col-md-1 mb-0 text-center">
                                                <button type="button" class="btn btn-sm btn-danger remove-signature-btn" title="Remove">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="signature-row row mb-3 align-items-end" data-index="2">
                                            <div class="form-group col-md-4 mb-0">
                                                <label>{{ __('Label') }} <span class="text-danger">*</span></label>
                                                <input type="text" name="signatures[2][label]" class="form-control" value="Principal" placeholder="e.g. Principal" required>
                                            </div>
                                            <div class="form-group col-md-3 mb-0">
                                                <label>{{ __('Alignment') }}</label>
                                                <select name="signatures[2][align]" class="form-control">
                                                    <option value="left">Left</option>
                                                    <option value="center">Center</option>
                                                    <option value="right" selected>Right</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4 mb-0">
                                                <label>{{ __('Signature Image') }} <small class="text-muted">(Optional)</small></label>
                                                <input type="file" name="signatures[2][image]" class="form-control" accept="image/*">
                                            </div>
                                            <div class="form-group col-md-1 mb-0 text-center">
                                                <button type="button" class="btn btn-sm btn-danger remove-signature-btn" title="Remove">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" id="add-signature-btn" class="btn btn-sm btn-outline-primary mt-2 mb-4">
                                        <i class="fa fa-plus"></i> {{ __('Add Signature Slot') }}
                                    </button>

                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-success float-right">
                                                <i class="fa fa-print"></i> {{ __('Generate & Print Hall Tickets') }}
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </form>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            var getExamsUrl = "{{ route('exams.hall-ticket.get-exams', ':id') }}";

            $('#class_section_id').change(function() {
                var classSectionId = $(this).val();
                var examSelect = $('#exam_id');
                
                // Clear existing options
                examSelect.html('<option value="">-- {{ __("select") . " " . __("exam") }} --</option>');
                
                if (classSectionId) {
                    var url = getExamsUrl.replace(':id', classSectionId);
                    $.get(url, function(data) {
                        $.each(data, function(index, exam) {
                            examSelect.append('<option value="' + exam.id + '">' + exam.name + '</option>');
                        });
                    });
                }
            });

            // Select All Checkbox logic
            $('#select-all-students').change(function() {
                $('.student-checkbox').prop('checked', $(this).prop('checked'));
            });

            // If any individual checkbox is unchecked, uncheck the select-all checkbox
            $('.student-checkbox').change(function() {
                if (!$(this).prop('checked')) {
                    $('#select-all-students').prop('checked', false);
                } else {
                    if ($('.student-checkbox:checked').length === $('.student-checkbox').length) {
                        $('#select-all-students').prop('checked', true);
                    }
                }
            });
            // Signature Repeater Logic
            var signatureIndex = 3; // Already have 3 default rows (0, 1, 2)

            $('#add-signature-btn').click(function() {
                var html = `
                    <div class="signature-row row mb-3 align-items-end" data-index="${signatureIndex}">
                        <div class="form-group col-md-4 mb-0">
                            <label>{{ __('Label') }} <span class="text-danger">*</span></label>
                            <input type="text" name="signatures[${signatureIndex}][label]" class="form-control" placeholder="e.g. HOD, Invigilator" required>
                        </div>
                        <div class="form-group col-md-3 mb-0">
                            <label>{{ __('Alignment') }}</label>
                            <select name="signatures[${signatureIndex}][align]" class="form-control">
                                <option value="left">Left</option>
                                <option value="center" selected>Center</option>
                                <option value="right">Right</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4 mb-0">
                            <label>{{ __('Signature Image') }} <small class="text-muted">(Optional)</small></label>
                            <input type="file" name="signatures[${signatureIndex}][image]" class="form-control" accept="image/*">
                        </div>
                        <div class="form-group col-md-1 mb-0 text-center">
                            <button type="button" class="btn btn-sm btn-danger remove-signature-btn" title="Remove">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                $('#signatures-container').append(html);
                signatureIndex++;
            });

            // Remove signature row
            $(document).on('click', '.remove-signature-btn', function() {
                var container = $('#signatures-container');
                if (container.find('.signature-row').length > 1) {
                    $(this).closest('.signature-row').remove();
                } else {
                    alert('You must have at least one signature slot.');
                }
            });
        });
    </script>
@endsection
