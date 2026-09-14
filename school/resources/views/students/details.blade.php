@extends('layouts.master')

@section('title')
    {{ __('students') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage') . ' ' . __('students') }}
            </h3>
        </div>

        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list') . ' ' . __('students') }}
                        </h4>
                        <div class="row" id="toolbar">
                            <div class="form-group col-sm-12 col-md-4">
                                <label class="filter-menu">{{ __('Class Section') }} <span class="text-danger">*</span></label>
                                <select name="filter_class_section_id" id="filter_class_section_id" class="form-control">
                                    <option value="">{{ __('select_class_section') }}</option>
                                    @foreach ($class_sections as $class_section)
                                        <option value={{ $class_section->id }}>{{$class_section->full_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-sm-12 col-md-4">
                                <label class="filter-menu">{{ __('Session Year') }} <span class="text-danger">*</span></label>
                                <select name="filter_session_year_id" id="filter_session_year_id" class="form-control">
                                    @foreach ($sessionYears as $sessionYear)
                                        <option value={{ $sessionYear->id }} {{$sessionYear->default==1?"selected":""}}>{{$sessionYear->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @can('student-delete')
                                <div class="form-group col-12 d-flex">
                                    <button id="update-status" class="btn btn-secondary mr-2" disabled><span class="update-status-btn-name">{{ __('Inactive') }}</span></button>
                                    <button id="bulk-delete-btn" class="btn btn-danger" disabled><i class="fa fa-trash"></i> {{ __('Delete Selected') }}</button>
                                </div>
                            @endcan
                        </div>

                        @can('student-delete')
                            <div class="col-12 mt-4 text-right">
                                <b><a href="#" class="table-list-type active mr-2" data-id="0">{{__('active')}}</a></b> | <a href="#" class="ml-2 table-list-type" data-id="1">{{__("Inactive")}}</a>
                            </div>
                        @endcan
                        <div class="row">
                            <div class="col-12">
                                <table aria-describedby="mydesc" class='table' id='table_list'
                                       data-toggle="table" data-url="{{ route('students.show',[1]) }}" data-click-to-select="true"
                                       data-side-pagination="server" data-pagination="true"
                                       data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                       data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true" data-fixed-columns="false"
                                       data-trim-on-search="false" data-mobile-responsive="true" data-sort-name="id"
                                       data-sort-order="desc" data-maintain-selected="true" data-export-types="['pdf','json', 'xml', 'csv', 'txt', 'sql', 'doc', 'excel']" data-show-export="true"
                                       data-export-options='{ "fileName": "students-list-<?= date('d-m-y') ?>" ,"ignoreColumn": ["operate"]}' data-query-params="studentDetailsQueryParams"
                                       data-check-on-init="true" data-escape="true">
                                    <thead>
                                    <tr>
                                        <th data-field="state" data-checkbox="true"></th>
                                        <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{ __('id') }}</th>
                                        <th scope="col" data-field="no">{{ __('no.') }}</th>
                                        <th scope="col" data-field="user.id" data-visible="false">{{ __('User Id') }}</th>
                                        <th scope="col" data-field="user.full_name">{{ __('name') }}</th>
                                        <th scope="col" data-field="user.dob" data-formatter="dateFormatter">{{ __('dob') }}</th>
                                        <th scope="col" data-field="user.image" data-formatter="imageFormatter">{{ __('image') }}</th>
                                        <th scope="col" data-field="aadhar_pic" data-formatter="aadharFormatter">{{ __('aadhar_pic') }}</th>
                                        <th scope="col" data-field="class_section.full_name">{{ __('class_section') }}</th>
                                        <th scope="col" data-field="admission_no">Admission Number</th>
                                        <th scope="col" data-field="pen_no">{{ __('PEN NO') }}</th>
                                        <th scope="col" data-field="roll_number">{{ __('roll_no') }}</th>
                                        <th scope="col" data-field="user.gender">{{ __('gender') }}</th>
                                        <th scope="col" data-field="admission_date" data-formatter="dateFormatter">{{ __('admission_date') }}</th>
                                        <th scope="col" data-field="guardian.email">{{ __('guardian') . ' ' . __('email') }}</th>
                                        <th scope="col" data-field="guardian.full_name">{{ __('guardian') . ' ' . __('name') }}</th>
                                        <th scope="col" data-field="guardian.mobile">{{ __('guardian') . ' ' . __('mobile') }}</th>
                                        <th scope="col" data-field="guardian.gender">{{ __('guardian') . ' ' . __('gender') }}</th>

                                        {{-- Admission form fields --}}
                                        @foreach ($extraFields as $field)
                                            <th scope="col" data-visible="false" data-escape="false" data-field="{{ $field->name }}">{{ $field->name }}</th>
                                        @endforeach
                                        {{-- End admission form fields --}}

                                        @canany(['student-edit','student-delete'])
                                            <th data-events="studentEvents" class="align-button text-center" scope="col" data-field="operate" data-escape="false">{{ __('action') }}</th>
                                        @endcanany
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('student-edit')
        <div class="modal fade" id="editModal" data-backdrop="static" tabindex="-1" role="dialog"
             aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel">{{ __('edit') . ' ' . __('students') }}</h4><br>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"><i class="fa fa-close"></i></span>
                        </button>
                    </div>
                    <form id="edit-form" class="edit-student-registration-form" novalidate="novalidate" action="{{ url('students') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                    <label>Admission Number <span class="text-danger">*</span></label>
                                    {!! Form::text('admission_no', null, ['placeholder' => 'Admission Number', 'class' => 'form-control', 'id' => 'edit_admission_no']) !!}

                                </div>

                                <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                    <label>{{ __('Session Year') }} <span class="text-danger">*</span></label>
                                    <select required name="session_year_id" class="form-control" id="session_year_id">
                                        @foreach ($sessionYears as $sessionYear)
                                            <option value="{{ $sessionYear->id }}">{{$sessionYear->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                    <label>{{ __('Class Section') }} <span class="text-danger">*</span></label>
                                    <select required name="class_section_id" class="form-control" id="edit_student_class_section_id">
                                        <option value="">{{ __('select_class_section') }}</option>
                                        @foreach ($class_sections as $class_section)
                                            <option value={{ $class_section->id }}>{{$class_section->full_name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                    <label>{{ __('PEN NO') }}</label>
                                    {!! Form::text('pen_no', null, ['placeholder' => __('PEN NO'), 'class' => 'form-control', 'id' => 'edit_pen_no']) !!}
                                </div>

                                <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                    <label>Previous Year Balance</label>
                                    {!! Form::number('previous_year_balance', 0, ['placeholder' => 'Previous Year Balance', 'class' => 'form-control', 'id' => 'edit_previous_year_balance', 'step' => '0.01', 'min' => '0']) !!}
                                </div>

                            </div>
                            
                            <hr>
                            <div class="row mt-3 mb-3">
                                <div class="col-md-12">
                                    <h4 class="card-title text-info">Fee Settings</h4>
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label class="d-block font-weight-bold">Class Fee Selection</label>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input type="checkbox" name="apply_class_fee" id="edit_apply_class_fee" value="1"> Apply Class Fee <span id="edit_class_fee_amount" style="font-weight: bold; color: #3085d6;">(₹ 0)</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12 col-md-8">
                                    <label class="d-block font-weight-bold">Van Fee Selection</label>
                                    <div class="d-flex align-items-center" style="height: 38px;">
                                        <div class="form-check form-check-inline mr-4">
                                            <label class="form-check-label">
                                                <input type="radio" name="apply_van_fee" value="0" id="edit_van_none"> No Van
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mr-4">
                                            <label class="form-check-label">
                                                <input type="radio" name="apply_van_fee" value="1" id="edit_van_1"> Van 1 (₹ 9,600)
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input type="radio" name="apply_van_fee" value="2" id="edit_van_2"> Van 2 (₹ 12,000)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12 col-md-3">
                                    <label class="d-block font-weight-bold">Admission Fee</label>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input type="checkbox" name="apply_admission_fee" id="edit_apply_admission_fee" value="1"> Admission Fee <span style="font-weight: bold; color: #e74c3c;">(₹ 2,000)</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label class="d-block font-weight-bold">Payment Package (EMI Option)</label>
                                    <select name="payment_package" id="edit_payment_package" class="form-control">
                                        <option value="">Full Payment</option>
                                        <option value="yearly">Yearly Pay (1 Installment)</option>
                                        <option value="half_yearly">Half Yearly (2 Installments)</option>
                                        <option value="quarterly">Quarterly (3 Installments)</option>
                                        <option value="monthly">Monthly (11 Installments)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mt-3 mb-3" id="edit_fee_summary_box" style="display:none;">
                                <div class="col-md-6 col-sm-12">
                                    <div class="card bg-light p-3 border">
                                        <h5 class="text-secondary mb-3">Total Fees Summary</h5>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Class Fee:</span>
                                            <span id="edit_preview_class_fee" class="font-weight-bold">₹ 0</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Van Fee:</span>
                                            <span id="edit_preview_van_fee" class="font-weight-bold">₹ 0</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Admission Fee:</span>
                                            <span id="edit_preview_admission_fee" class="font-weight-bold">₹ 0</span>
                                        </div>
                                        <input type="hidden" id="edit_initial_payment" value="0">
                                        <hr class="my-2">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="h6 mb-0 font-weight-bold">Total Due Amount:</span>
                                            <span id="edit_preview_total_fee" class="h6 mb-0 font-weight-bold text-success">₹ 0</span>
                                        </div>
                                        <div id="edit_preview_initial_payment_row" class="d-flex justify-content-between mb-2" style="display:none !important;">
                                            <span>Initial Payment Paid:</span>
                                            <span id="edit_preview_initial_payment" class="font-weight-bold text-dark">₹ 0</span>
                                        </div>
                                        <div id="edit_preview_remaining_row" class="d-flex justify-content-between mb-2" style="display:none !important;">
                                            <span>Remaining Balance:</span>
                                            <span id="edit_preview_remaining" class="font-weight-bold text-danger">₹ 0</span>
                                        </div>
                                        <div id="edit_preview_installments_section" style="display:none;">
                                            <hr class="my-2">
                                            <h6 class="text-primary mb-2 font-weight-bold">Installment Schedule</h6>
                                            <div id="edit_preview_installments_list" style="font-size: 0.9rem;">
                                                <!-- Dynamic installments list -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            <div class="row mt-5">
                                <div class="form-group col-sm-12 col-md-12 col-lg-8 col-xl-8">
                                    <label>Full Name <span class="text-danger">*</span></label>
                                    {!! Form::text('full_name', null, ['placeholder' => 'Full Name', 'class' => 'form-control', 'id' => 'edit_full_name']) !!}
                                </div>

                                <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                    <label>{{ __('dob') }} <span class="text-danger">*</span></label>
                                    {!! Form::text('dob', null, ['placeholder' => __('dob'), 'class' => 'datepicker-popup-no-future form-control', 'id' => 'edit_dob']) !!}
                                    <span class="input-group-addon input-group-append">
                                    </span>
                                </div>

                                <div class="form-group col-sm-12 col-md-4">
                                    <label>{{ __('gender') }} <span class="text-danger">*</span></label><br>
                                    <div class="d-flex">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('gender', 'male', false ,['id' => 'male']) !!}
                                                {{ __('male') }}
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                {!! Form::radio('gender', 'female', false , ['id' => 'female']) !!}
                                                {{ __('female') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                    <label>{{ __('image') }} </label>
                                    <input type="file" name="image" class="file-upload-default"/>
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" disabled="" placeholder="{{ __('image') }}" required="required" id="edit_image"/>
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                        </span>
                                    </div>
                                    <div style="width: 100px;">
                                        <img src="" id="edit-student-image-tag" class="img-fluid w-100" alt=""/>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                    <label>{{ __('aadhar_pic') }} </label>
                                    <input type="file" name="aadhar_pic" class="file-upload-default"/>
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" disabled="" placeholder="{{ __('aadhar_pic') }}" id="edit_aadhar_pic"/>
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                        </span>
                                    </div>
                                    <div style="width: 100px;" class="mt-2">
                                        <a href="" id="edit-student-aadhar-tag" target="_blank" class="d-none btn btn-theme btn-sm text-white">View Aadhar</a>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('current_address') }} <span class="text-danger">*</span></label>
                                    {!! Form::textarea('current_address', null, ['required', 'placeholder' => __('current_address'), 'class' => 'form-control', 'rows' => 3,'id'=>'edit-current-address']) !!}
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label>{{ __('permanent_address') }} <span class="text-danger">*</span></label>
                                    {!! Form::textarea('permanent_address', null, ['required', 'placeholder' => __('permanent_address'), 'class' => 'form-control', 'rows' => 3,'id'=>'edit-permanent-address']) !!}
                                </div>
                            </div>

                            @if(!empty($extraFields))
                                <div class="row other-details">

                                    {{-- Loop the FormData --}}
                                    @foreach ($extraFields as $key => $data)
                                        @if($data->user_type == 1)
                                            @php $fieldName = str_replace(' ', '_', $data->name) @endphp
                                            {{-- Edit Extra Details ID --}}
                                            {{ Form::hidden('extra_fields['.$key.'][id]', '', ['id' => $fieldName.'_id']) }}

                                            {{-- Form Field ID --}}
                                            {{ Form::hidden('extra_fields['.$key.'][form_field_id]', $data->id) }}

                                            {{-- FormFieldType --}}
                                            {{ Form::hidden('extra_fields['.$key.'][input_type]', $data->type) }}

                                            <div class='form-group col-md-12 col-lg-6 col-xl-4 col-sm-12'>

                                                {{-- Add lable to all the elements excluding checkbox --}}
                                                @if($data->type != 'radio' && $data->type != 'checkbox')
                                                    <label>{{$data->name}} @if($data->is_required)
                                                            <span class="text-danger">*</span>
                                                        @endif</label>
                                                @endif

                                                {{-- Text Field --}}
                                                @if($data->type == 'text')
                                                    {{ Form::text('extra_fields['.$key.'][data]', '', ['class' => 'form-control text-fields', 'id' => $fieldName, 'placeholder' => $data->name, ($data->is_required == 1 ? 'required' : '')]) }}
                                                    {{-- Number Field --}}
                                                @elseif($data->type == 'number')
                                                    {{ Form::number('extra_fields['.$key.'][data]', '', ['min' => 0, 'class' => 'form-control number-fields', 'id' => $fieldName, 'placeholder' => $data->name, ($data->is_required == 1 ? 'required' : '')]) }}

                                                    {{-- Dropdown Field --}}
                                                @elseif($data->type == 'dropdown')
                                                    {{ Form::select(
                                                        'extra_fields['.$key.'][data]',$data->default_values,
                                                        null,
                                                        [
                                                            'id' => $fieldName,
                                                            'class' => 'form-control select-fields',
                                                            ($data->is_required == 1 ? 'required' : ''),
                                                            'placeholder' => 'Select '.$data->name
                                                        ]
                                                    )}}

                                                    {{-- Radio Field --}}
                                                @elseif($data->type == 'radio')
                                                    <label class="d-block">{{$data->name}} @if($data->is_required)
                                                            <span class="text-danger">*</span>
                                                        @endif</label>
                                                    <div class="row form-check-inline ml-1">
                                                        @foreach ($data->default_values as $keyRadio => $value)
                                                            <div class="col-md-12 col-lg-12 col-xl-6 col-sm-12 form-check">
                                                                <label class="form-check-label">
                                                                    {{ Form::radio('extra_fields['.$key.'][data]', $value, null, ['id' => $fieldName.'_'.$keyRadio, 'class' => 'radio-fields',($data->is_required == 1 ? 'required' : '')]) }}
                                                                    {{$value}}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    {{-- Checkbox Field --}}
                                                @elseif($data->type == 'checkbox')
                                                    <label class="d-block">{{$data->name}} @if($data->is_required)
                                                            <span class="text-danger">*</span>
                                                        @endif</label>
                                                    <div class="row form-check-inline ml-1">
                                                        @foreach ($data->default_values as $chkKey => $value)
                                                            <div class="col-lg-12 col-xl-6 col-md-12 col-sm-12 form-check">
                                                                <label class="form-check-label">
                                                                    {{ Form::checkbox('extra_fields['.$key.'][data][]', $value, null, ['id' => $fieldName.'_'.$chkKey, 'class' => 'form-check-input chkclass checkbox-fields',($data->is_required == 1 ? 'required' : '')]) }} {{ $value }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    {{-- Textarea Field --}}
                                                @elseif($data->type == 'textarea')
                                                    {{ Form::textarea('extra_fields['.$key.'][data]', '', ['placeholder' => $data->name, 'id' => $fieldName, 'class' => 'form-control textarea-fields', ($data->is_required ? 'required' : '') , 'rows' => 3]) }}

                                                    {{-- File Upload Field --}}
                                                @elseif($data->type == 'file')
                                                    <div class="input-group col-xs-12">
                                                        {{ Form::file('extra_fields['.$key.'][data]', ['class' => 'file-upload-default', 'id' => $fieldName]) }}
                                                        {{ Form::text('', '', ['class' => 'form-control file-upload-info', 'disabled' => '', 'placeholder' => __('image')]) }}
                                                        <span class="input-group-append">
                                                            <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                                        </span>
                                                    </div>
                                                    <div id="file_div_{{$fieldName}}" class="mt-2 d-none file-div">
                                                        <a href="" id="file_link_{{$fieldName}}" target="_blank">{{$data->name}}</a>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif

                            <div class="row">
                                <div class="form-group col-sm-12 col-md-4">
                                    <div class="d-flex">
                                        <div class="form-check w-fit-content">
                                            <label class="form-check-label ml-4">
                                                <input type="checkbox" class="form-check-input" name="reset_password" value="1">{{ __('reset_password') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            

                            <hr>
                            {{-- Guardian Details --}}
                            <div class="row mt-5">
                                 <div class="form-group col-sm-12 col-md-12">
                                     <label for="edit_guardian_relation">Relation <span class="text-danger">*</span></label>
                                      <select name="guardian_relation" id="edit_guardian_relation" class="form-control">
                                          <option value="father">Father</option>
                                          <option value="mother">Mother</option>
                                          <option value="father_mother">Father & Mother</option>
                                          <option value="guardian" selected>Guardian</option>
                                      </select>
                                 </div>

                                 <div class="form-group col-sm-12 col-md-12">
                                     <label><span class="edit-guardian-relation-label">Guardian</span> {{ __('email') }}</label>
                                     <select class="edit-guardian-search form-control" name="guardian_id"></select>
                                     <input type="hidden" id="edit_guardian_email" name="guardian_email">
                                 </div>

                                {{-- Single Parent Fields --}}
                                <div class="row col-sm-12 col-md-12 p-0 m-0" id="edit_single_guardian_fields">
                                    <div class="form-group col-sm-12 col-md-12 col-lg-8 col-xl-8">
                                        <label><span class="edit-guardian-relation-label">Guardian</span> Full Name <span class="text-danger">*</span></label>
                                        {!! Form::text('guardian_full_name', null, ['placeholder' => __('guardian') . ' ' . __('Full Name'), 'class' => 'form-control', 'id' => 'edit_guardian_full_name']) !!}
                                    </div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-4 col-xl-4">
                                        <label><span class="edit-guardian-relation-label">Guardian</span> {{ __('mobile') }}</label>
                                        {!! Form::number('guardian_mobile', null, ['placeholder' => __('guardian') . ' ' . __('mobile'), 'class' => 'form-control remove-number-increment', 'min' => 1  ,'id' => 'edit_guardian_mobile']) !!}
                                    </div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                        <label><span class="edit-guardian-relation-label">Guardian</span> Photo</label>
                                        <input type="file" name="guardian_image" class="file-upload-default" accept="image/*"/>
                                        <div class="input-group col-xs-12">
                                            <input type="text" class="form-control file-upload-info" disabled="" placeholder="Upload Guardian Photo" id="edit_guardian_image"/>
                                            <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                            </span>
                                        </div>
                                        <div class="mt-2">
                                            <img src="" id="edit-guardian-image-tag" class="img-fluid w-25 d-none" alt=""/>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                        <label><span class="edit-guardian-relation-label">Guardian</span> Aadhar Image</label>
                                        <input type="file" name="guardian_aadhar_pic" class="file-upload-default" accept="image/*,application/pdf"/>
                                        <div class="input-group col-xs-12">
                                            <input type="text" class="form-control file-upload-info" disabled="" placeholder="Upload Guardian Aadhar Image" id="edit_guardian_aadhar_pic"/>
                                            <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                            </span>
                                        </div>
                                        <div class="mt-2">
                                            <a href="" id="edit-guardian-aadhar-tag" target="_blank" class="d-none btn btn-theme btn-sm text-white">View Aadhar</a>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-12">
                                        <label>{{ __('gender') }} <span class="text-danger">*</span></label><br>
                                        <div class="d-flex">
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    {!! Form::radio('guardian_gender', 'male', false , ['id' =>"edit-guardian-male"]) !!}
                                                    {{ __('male') }}
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    {!! Form::radio('guardian_gender', 'female', false , ['id' =>"edit-guardian-female"]) !!}
                                                    {{ __('female') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Double Parent Fields --}}
                                <div class="row col-sm-12 col-md-12 p-0 m-0 d-none" id="edit_double_parent_fields">
                                    <div class="col-md-12"><h4 class="card-title text-info">Father Details</h4></div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-8 col-xl-8">
                                        <label>Father Full Name <span class="text-danger">*</span></label>
                                        {!! Form::text('guardian_full_name', null, ['placeholder' => 'Father Full Name', 'class' => 'form-control', 'id' => 'edit_father_full_name', 'disabled' => true]) !!}
                                    </div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-4 col-xl-4">
                                        <label>Father Mobile</label>
                                        {!! Form::number('guardian_mobile', null, ['placeholder' => 'Father Mobile', 'class' => 'form-control remove-number-increment', 'id' => 'edit_father_mobile','min' => 1, 'disabled' => true]) !!}
                                    </div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                        <label>Father Photo</label>
                                        <input type="file" name="guardian_image" class="file-upload-default" accept="image/*" disabled true/>
                                        <div class="input-group col-xs-12">
                                            <input type="text" class="form-control file-upload-info" disabled="" placeholder="Upload Father Photo" id="edit_father_image"/>
                                            <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                            </span>
                                        </div>
                                        <div class="mt-2">
                                            <img src="" id="edit-father-image-tag" class="img-fluid w-25 d-none" alt=""/>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                        <label>Father Aadhar Image</label>
                                        <input type="file" name="guardian_aadhar_pic" class="file-upload-default" accept="image/*,application/pdf" disabled true/>
                                        <div class="input-group col-xs-12">
                                            <input type="text" class="form-control file-upload-info" disabled="" placeholder="Upload Father Aadhar Image" id="edit_father_aadhar_pic"/>
                                            <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                            </span>
                                        </div>
                                        <div class="mt-2">
                                            <a href="" id="edit-father-aadhar-tag" target="_blank" class="d-none btn btn-theme btn-sm text-white">View Aadhar</a>
                                        </div>
                                    </div>
                                    <input type="hidden" name="guardian_gender" value="male" id="edit_father_gender" disabled true>

                                    <div class="col-md-12 mt-4"><h4 class="card-title text-info">Mother Details</h4></div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-8 col-xl-8">
                                        <label>Mother Full Name <span class="text-danger">*</span></label>
                                        {!! Form::text('mother_name', null, ['placeholder' => 'Mother Full Name', 'class' => 'form-control', 'id' => 'edit_mother_full_name', 'disabled' => true]) !!}
                                    </div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-4 col-xl-4">
                                        <label>Mother Mobile</label>
                                        {!! Form::number('mother_mobile', null, ['placeholder' => 'Mother Mobile', 'class' => 'form-control remove-number-increment', 'id' => 'edit_mother_mobile','min' => 1, 'disabled' => true]) !!}
                                    </div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                        <label>Mother Photo</label>
                                        <input type="file" name="mother_image" class="file-upload-default" accept="image/*" disabled true/>
                                        <div class="input-group col-xs-12">
                                            <input type="text" class="form-control file-upload-info" disabled="" placeholder="Upload Mother Photo" id="edit_mother_image"/>
                                            <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                            </span>
                                        </div>
                                        <div class="mt-2">
                                            <img src="" id="edit-mother-image-tag" class="img-fluid w-25 d-none" alt=""/>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                        <label>Mother Aadhar Image</label>
                                        <input type="file" name="mother_aadhar_pic" class="file-upload-default" accept="image/*,application/pdf" disabled true/>
                                        <div class="input-group col-xs-12">
                                            <input type="text" class="form-control file-upload-info" disabled="" placeholder="Upload Mother Aadhar Image" id="edit_mother_aadhar_pic"/>
                                            <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                            </span>
                                        </div>
                                        <div class="mt-2">
                                            <a href="" id="edit-mother-aadhar-tag" target="_blank" class="d-none btn btn-theme btn-sm text-white">View Aadhar</a>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-4">
                                    <div class="d-flex">
                                        <div class="form-check w-fit-content">
                                            <label class="form-check-label ml-4">
                                                <input type="checkbox" class="form-check-input" name="parent_reset_password" value="1">{{ __('reset_password') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                            <input class="btn btn-theme" type="submit" value={{ __('submit') }}>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            function updateEditGuardianLabels() {
                var relation = $('#edit_guardian_relation').val();
                if (!relation) return;
                
                if (relation === 'father_mother') {
                    $('#edit_single_guardian_fields').addClass('d-none');
                    $('#edit_single_guardian_fields').find('input, select, textarea').prop('disabled', true);
                    
                    $('#edit_double_parent_fields').removeClass('d-none');
                    $('#edit_double_parent_fields').find('input, select, textarea').prop('disabled', false);
                    
                    $('.edit-guardian-relation-label').text('Father & Mother');
                } else {
                    $('#edit_double_parent_fields').addClass('d-none');
                    $('#edit_double_parent_fields').find('input, select, textarea').prop('disabled', true);
                    
                    $('#edit_single_guardian_fields').removeClass('d-none');
                    $('#edit_single_guardian_fields').find('input, select, textarea').prop('disabled', false);
                    
                    var labelText = relation.charAt(0).toUpperCase() + relation.slice(1);
                    $('.edit-guardian-relation-label').text(labelText);
                    
                    $('#edit_guardian_full_name').attr('placeholder', labelText + ' Full Name');
                    $('#edit_guardian_mobile').attr('placeholder', labelText + ' Mobile');
                    
                    if (relation === 'father') {
                        $('#edit-guardian-female').prop('checked', false);
                        $('#edit-guardian-male').prop('checked', true);
                    } else if (relation === 'mother') {
                        $('#edit-guardian-male').prop('checked', false);
                        $('#edit-guardian-female').prop('checked', true);
                    }
                }
            }
            
            $(document).on('change', '#edit_guardian_relation', function() {
                updateEditGuardianLabels();
            });
            
            updateEditGuardianLabels();
        });

        let userIds;
        $('.table-list-type').on('click', function (e) {
            let value = $(this).data('id');
            let ActiveLang = window.trans['Active'];
            let DeactiveLang = window.trans['Inactive'];
            if (value === "" || value === 0 || value == null) {
                $("#update-status").data("id")
                $('.update-status-btn-name').html(DeactiveLang);
            } else {
                $('.update-status-btn-name').html(ActiveLang);
            }
        })


        function updateUserStatus(tableId, buttonClass) {
            let selectedRows = $(tableId).bootstrapTable('getSelections');
            let selectedRowsValues = selectedRows.map(function (row) {
                return row.user_id;
            });
            userIds = JSON.stringify(selectedRowsValues);

            if (buttonClass != null) {
                if (selectedRowsValues.length) {
                    $(buttonClass).prop('disabled', false);
                    $('#bulk-delete-btn').prop('disabled', false);
                } else {
                    $(buttonClass).prop('disabled', true);
                    $('#bulk-delete-btn').prop('disabled', true);
                }
            }
        }

        $('#table_list').bootstrapTable({
            onCheck: function (row) {
                updateUserStatus("#table_list", '#update-status');
            },
            onUncheck: function (row) {
                updateUserStatus("#table_list", '#update-status');
            },
            onCheckAll: function (rows) {
                updateUserStatus("#table_list", '#update-status');
            },
            onUncheckAll: function (rows) {
                updateUserStatus("#table_list", '#update-status');
            }
        });
        $("#update-status").on('click', function (e) {
            Swal.fire({
                title: window.trans["Are you sure"],
                text: window.trans["Change Status For Selected Users"],
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: window.trans["Yes, Change it"],
                cancelButtonText: window.trans["Cancel"]
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = baseUrl + '/students/change-status-bulk';
                    let data = new FormData();
                    data.append("ids", userIds)

                    function successCallback(response) {
                        $('#table_list').bootstrapTable('refresh');
                        $('#update-status').prop('disabled', true);
                        $('#bulk-delete-btn').prop('disabled', true);
                        userIds = null;
                        showSuccessToast(response.message);
                    }

                    function errorCallback(response) {
                        showErrorToast(response.message);
                    }

                    ajaxRequest('POST', url, data, null, successCallback, errorCallback);
                }
            })
        })

        $("#bulk-delete-btn").on('click', function (e) {
            Swal.fire({
                title: window.trans["Are you sure"],
                text: "Delete Selected Students Permanently?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: "Yes, Delete",
                cancelButtonText: window.trans["Cancel"]
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = baseUrl + '/students/trash-bulk';
                    let data = new FormData();
                    data.append("ids", userIds);

                    function successCallback(response) {
                        $('#table_list').bootstrapTable('refresh');
                        $('#update-status').prop('disabled', true);
                        $('#bulk-delete-btn').prop('disabled', true);
                        userIds = null;
                        showSuccessToast(response.message);
                    }

                    function errorCallback(response) {
                        showErrorToast(response.message);
                    }

                    ajaxRequest('POST', url, data, null, successCallback, errorCallback);
                }
            })
        })

        // Dynamic Edit Fees Calculation Code
        const classSectionToClassMap = {
            @foreach($class_sections as $cs)
                "{{ $cs->id }}": "{{ $cs->class_id }}",
            @endforeach
        };
        
        const classFeesMap = {
            @foreach($classFees as $classId => $amount)
                "{{ $classId }}": "{{ $amount }}",
            @endforeach
        };

        function getInstallmentSchedule(packageType, remainingAmount) {
            let installments = [];
            if (packageType === 'yearly') {
                installments.push({
                    name: 'Yearly Pay',
                    date: '30-06-2026',
                    amount: remainingAmount
                });
            } else if (packageType === 'half_yearly') {
                let part = Math.round((remainingAmount / 2) * 100) / 100;
                installments.push({
                    name: '1st Half',
                    date: '30-06-2026',
                    amount: part
                });
                installments.push({
                    name: '2nd Half',
                    date: '30-11-2026',
                    amount: Math.round((remainingAmount - part) * 100) / 100
                });
            } else if (packageType === 'quarterly') {
                let part = Math.round((remainingAmount / 3) * 100) / 100;
                installments.push({
                    name: '1st Quarter',
                    date: '30-06-2026',
                    amount: part
                });
                installments.push({
                    name: '2nd Quarter',
                    date: '15-09-2026',
                    amount: part
                });
                installments.push({
                    name: '3rd Quarter',
                    date: '31-12-2026',
                    amount: Math.round((remainingAmount - part * 2) * 100) / 100
                });
            } else if (packageType === 'monthly') {
                let part = Math.round((remainingAmount / 11) * 100) / 100;
                let months = [
                    '05-07-2026', '05-08-2026', '05-09-2026', '05-10-2026', '05-11-2026',
                    '05-12-2026', '05-01-2027', '05-02-2027', '05-03-2027', '05-04-2027',
                    '05-05-2027'
                ];
                for (let i = 0; i < 11; i++) {
                    let amt = (i === 10) ? Math.round((remainingAmount - part * 10) * 100) / 100 : part;
                    installments.push({
                        name: 'Month ' + (i + 1),
                        date: months[i],
                        amount: amt
                    });
                }
            }
            return installments;
        }

        window.updateEditFeeSummary = function() {
            const csId = $('#edit_student_class_section_id').val();
            const classId = classSectionToClassMap[csId];
            const rawClassFee = classId ? (classFeesMap[classId] || 0) : 0;
            
            if (rawClassFee > 0) {
                $('#edit_class_fee_amount').text('(₹ ' + parseFloat(rawClassFee).toLocaleString('en-IN') + ')');
            } else {
                $('#edit_class_fee_amount').text('(₹ 0)');
            }

            const applyClass = $('#edit_apply_class_fee').is(':checked');
            const classFee = applyClass ? parseFloat(rawClassFee) : 0;
            
            const vanSelection = $('input[name="apply_van_fee"]:checked').val();
            let vanFee = 0;
            if (vanSelection == 1) {
                vanFee = 9600;
            } else if (vanSelection == 2) {
                vanFee = 12000;
            }

            const applyAdmission = $('#edit_apply_admission_fee').is(':checked');
            const admissionFee = applyAdmission ? 2000 : 0;

            const totalDue = classFee + vanFee + admissionFee;
            const initialPayment = parseFloat($('#edit_initial_payment').val()) || 0;
            const remainingAmount = Math.max(0, totalDue - initialPayment);
            const paymentPackage = $('#edit_payment_package').val();

            if (csId) {
                $('#edit_fee_summary_box').show();
                $('#edit_preview_class_fee').text('₹ ' + classFee.toLocaleString('en-IN'));
                $('#edit_preview_van_fee').text('₹ ' + vanFee.toLocaleString('en-IN'));
                $('#edit_preview_admission_fee').text('₹ ' + admissionFee.toLocaleString('en-IN'));
                $('#edit_preview_total_fee').text('₹ ' + totalDue.toLocaleString('en-IN'));

                if (paymentPackage) {
                    $('#edit_preview_initial_payment_row').attr('style', 'display: flex !important;');
                    $('#edit_preview_initial_payment').text('₹ ' + initialPayment.toLocaleString('en-IN'));
                    
                    $('#edit_preview_remaining_row').attr('style', 'display: flex !important;');
                    $('#edit_preview_remaining').text('₹ ' + remainingAmount.toLocaleString('en-IN'));

                    $('#edit_preview_installments_section').show();
                    let schedule = getInstallmentSchedule(paymentPackage, remainingAmount);
                    let html = '';
                    schedule.forEach(inst => {
                        html += `<div class="d-flex justify-content-between mb-1 text-muted">
                            <span>${inst.name} (${inst.date}):</span>
                            <span class="font-weight-bold">₹ ${inst.amount.toLocaleString('en-IN')}</span>
                        </div>`;
                    });
                    $('#edit_preview_installments_list').html(html);
                } else {
                    $('#edit_preview_initial_payment_row').attr('style', 'display: none !important;');
                    $('#edit_preview_remaining_row').attr('style', 'display: none !important;');
                    $('#edit_preview_installments_section').hide();
                }
            } else {
                $('#edit_fee_summary_box').hide();
            }
        }

        $(document).on('change input', '#edit_student_class_section_id, #edit_apply_class_fee, input[name="apply_van_fee"], #edit_apply_admission_fee, #edit_payment_package', function() {
            window.updateEditFeeSummary();
        });

        window.updateEditFeeSummary();
    </script>
@endsection
