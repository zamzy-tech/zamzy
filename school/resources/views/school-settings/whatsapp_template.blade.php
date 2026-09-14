@extends('layouts.master')

@section('title')
    {{ __('WhatsApp Template') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('WhatsApp Template') }}
            </h3>
        </div>
        <div class="row grid-margin">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form id="formdata1" class="school-whatsapp-template" action="{{ route('school-settings.whatsapp-template.update') }}" method="POST" novalidate="novalidate">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label>{{ __('template') }} <span class="text-danger">*</span></label>
                                <div class="col-12 d-flex row">
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input type="radio" class="form-check-input whatsapp-template" checked name="template" id="whatsapp-template-staff-radio" value="staff-template" required="required">
                                            {{ __('Staff Registration Template') }}
                                        </label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input type="radio" class="form-check-input whatsapp-template" name="template" id="whatsapp-template-parent-radio" value="parent-template" required="required">
                                            {{ __('Parent Registration Template') }}
                                        </label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input type="radio" class="form-check-input whatsapp-template" name="template" id="whatsapp-template-fee-radio" value="fee-template" required="required">
                                            {{ __('Fee Payment Template') }}
                                        </label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input type="radio" class="form-check-input whatsapp-template" name="template" id="whatsapp-template-absent-radio" value="absent-template" required="required">
                                            {{ __('Absent Template') }}
                                        </label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input type="radio" class="form-check-input whatsapp-template" name="template" id="whatsapp-template-leave-radio" value="leave-template" required="required">
                                            {{ __('School Leave Template') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row staff-whatsapp-template">
                                <div class="form-group col-md-12 col-sm-12">
                                    <textarea name="staff_data" rows="12" class="form-control" required placeholder="{{ __('WhatsApp Template') }}">{{ htmlspecialchars_decode($settings['whatsapp-template-staff'] ?? "*Welcome to {school_name}*\n\nDear {full_name},\nYour account has been created successfully.\n\n*Login Details:*\nURL: {url}\nUsername: {email}\nPassword: {password}\n\n*Download App:* {app_link}\n\nThank you,\n{school_name}") }}</textarea>
                                </div>

                                <div class="form-group col-sm-12 col-md-12">
                                    <a data-value="{full_name}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('full_name') }} }</a>
                                    <a data-value="{email}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('email') }} }</a>
                                    <a data-value="{password}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('password') }} }</a>
                                    <a data-value="{school_name}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('school_name') }} }</a>
                                    <a data-value="{url}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('url') }} }</a>
                                    <a data-value="{app_link}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('app_link') }} }</a>
                                </div>
                            </div>

                            <div class="row parent-whatsapp-template">
                                <div class="form-group col-md-12 col-sm-12">
                                    <textarea name="parent_data" rows="12" class="form-control" required placeholder="{{ __('WhatsApp Template') }}">{{ htmlspecialchars_decode($settings['whatsapp-template-parent'] ?? "*Admission Approved - Welcome to {school_name}*\n\nDear {parent_name},\nYour child {child_name}'s admission application has been approved.\n\n*Parent Login Details:*\nLogin Link: {url}\nUsername/Mobile: {parent_mobile}\nPassword: {parent_password}\n\n*Student Login Details:*\nUsername/Admission No: {admission_no}\nPassword: {child_password}\n\n*Download App:* {app_link}\n\nThank you,\n{school_name}") }}</textarea>
                                </div>

                                <div class="form-group col-sm-12 col-md-12">
                                    <a data-value="{parent_name}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('parent_name') }} }</a>
                                    <a data-value="{parent_mobile}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('parent_mobile') }} }</a>
                                    <a data-value="{parent_password}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('parent_password') }} }</a>
                                    <a data-value="{child_name}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('child_name') }} }</a>
                                    <a data-value="{admission_no}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('admission_no') }} }</a>
                                    <a data-value="{child_password}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('child_password') }} }</a>
                                    <a data-value="{school_name}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('school_name') }} }</a>
                                    <a data-value="{url}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('url') }} }</a>
                                    <a data-value="{app_link}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('app_link') }} }</a>
                                </div>
                            </div>

                            <div class="row fee-whatsapp-template">
                                <div class="form-group col-md-12 col-sm-12">
                                    <textarea name="fee_payment_data" rows="12" class="form-control" required placeholder="{{ __('WhatsApp Template') }}">{{ htmlspecialchars_decode($settings['whatsapp-template-fee-payment'] ?? "*Fees Payment Received*\n\nDear Parent/Student, fee payment of {currency_symbol} {amount} has been received successfully towards {fee_name} for {student_name}.\n\nThank you,\n{school_name}") }}</textarea>
                                </div>

                                <div class="form-group col-sm-12 col-md-12">
                                    <a data-value="{parent_name}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('parent_name') }} }</a>
                                    <a data-value="{student_name}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('student_name') }} }</a>
                                    <a data-value="{fee_name}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('fee_name') }} }</a>
                                    <a data-value="{amount}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('amount') }} }</a>
                                    <a data-value="{currency_symbol}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('currency_symbol') }} }</a>
                                    <a data-value="{school_name}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('school_name') }} }</a>
                                    <a data-value="{balance_amount}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ Balance Amount }</a>
                                    <a data-value="{due_date}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ Due Date }</a>
                                    <a data-value="{url}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('url') }} }</a>
                                </div>
                            </div>

                            <div class="row absent-whatsapp-template">
                                <div class="form-group col-md-12 col-sm-12">
                                    <textarea name="absent_data" rows="12" class="form-control" required placeholder="{{ __('WhatsApp Template') }}">{{ htmlspecialchars_decode($settings['whatsapp-template-absent'] ?? "*Absent Notification*\n\nDear {parent_name},\nThis is to inform you that your child {student_name} is absent on {date}.\n\nThank you,\n{school_name}") }}</textarea>
                                </div>

                                <div class="form-group col-sm-12 col-md-12">
                                    <a data-value="{parent_name}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('parent_name') }} }</a>
                                    <a data-value="{student_name}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('student_name') }} }</a>
                                    <a data-value="{date}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('date') }} }</a>
                                    <a data-value="{school_name}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('school_name') }} }</a>
                                </div>
                            </div>

                            <div class="row leave-whatsapp-template">
                                <div class="form-group col-md-12 col-sm-12">
                                    <textarea name="leave_data" rows="12" class="form-control" required placeholder="{{ __('WhatsApp Template') }}">{{ htmlspecialchars_decode($settings['whatsapp-template-leave'] ?? "*Leave Request {status}*\n\nDear {user_name},\nYour leave request has been {status} by the administration.\n\nThank you,\n{school_name}") }}</textarea>
                                </div>

                                <div class="form-group col-sm-12 col-md-12">
                                    <a data-value="{user_name}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('user_name') }} }</a>
                                    <a data-value="{status}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('status') }} }</a>
                                    <a data-value="{school_name}" class="btn btn-gradient-light whatsapp-btn-tag mt-2">{ {{ __('school_name') }} }</a>
                                </div>
                            </div>

                            <input class="btn btn-theme float-right" type="submit" value="{{ __('submit') }}">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        window.onload = setTimeout(() => {
            $('.whatsapp-template').trigger('change');
        }, 500);

        $('.whatsapp-template').change(function (e) { 
            e.preventDefault();
            let type = $('input[name="template"]:checked').val();

            $('.staff-whatsapp-template').hide();
            $('.parent-whatsapp-template').hide();
            $('.fee-whatsapp-template').hide();
            $('.absent-whatsapp-template').hide();
            $('.leave-whatsapp-template').hide();

            if (type == 'staff-template') {
                $('.staff-whatsapp-template').show(500);
            } else if(type == 'parent-template') {
                $('.parent-whatsapp-template').show(500);
            } else if(type == 'fee-template') {
                $('.fee-whatsapp-template').show(500);
            } else if(type == 'absent-template') {
                $('.absent-whatsapp-template').show(500);
            } else if(type == 'leave-template') {
                $('.leave-whatsapp-template').show(500);
            }
        });

        $('.whatsapp-btn-tag').click(function(e) {
            e.preventDefault();
            var tag = $(this).data('value');
            var $textarea = $(this).closest('.row').find('textarea');
            var val = $textarea.val();
            var start = $textarea[0].selectionStart;
            var end = $textarea[0].selectionEnd;
            $textarea.val(val.substring(0, start) + tag + val.substring(end));
            $textarea.focus();
            $textarea[0].selectionStart = $textarea[0].selectionEnd = start + tag.length;
        });
    </script>
@endsection
