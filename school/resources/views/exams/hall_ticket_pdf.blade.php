<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Hall Tickets</title>
    <style>
        @page {
            margin: 4mm 8mm;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .ticket-container {
            border: 1.5px dashed #000;
            border-radius: 5px;
            padding: 6px 10px;
            box-sizing: border-box;
            background: #fff;
            height: 134mm;
            position: relative;
            margin-bottom: 4mm;
            overflow: hidden;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        .header-table td {
            padding: 2px;
            vertical-align: middle;
        }
        .school-name {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .ticket-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            margin-top: 4px;
            margin-bottom: 6px;
            text-decoration: underline;
            letter-spacing: 1px;
        }
        .student-info-table {
            border: 1px solid #ccc;
            margin-bottom: 0;
        }
        .student-info-table th, .student-info-table td {
            border: 1px solid #ccc;
            padding: 4px 6px;
            font-size: 11px;
            line-height: 1.3;
        }
        .student-info-table th {
            text-align: left;
            background-color: #f5f5f5;
            width: 25%;
        }
        .student-info-table td {
            width: 25%;
        }
        .timetable-table {
            border: 1px solid #000;
            margin-top: 4px;
        }
        .timetable-table th, .timetable-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 10px;
            text-align: center;
            line-height: 1.3;
        }
        .timetable-table th {
            background-color: #eaeaea;
        }
        .ticket-bottom {
            position: absolute;
            bottom: 8px;
            left: 12px;
            right: 12px;
        }
        .footer-signatures {
            margin-top: 8px;
            margin-bottom: 0;
        }
        .footer-signatures td {
            font-size: 10px;
            font-weight: bold;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

@foreach ($students as $index => $student)
    <div class="ticket-container {{ ($index > 0 && $index % 2 === 0) ? 'page-break' : '' }}">
        <!-- Header (Logo & School Name on SAME line, 100% Centered) -->
        <div style="width: 100%; text-align: center; margin-bottom: 4px;">
            <div style="text-align: center; margin-bottom: 3px;">
                @if ($settings['horizontal_logo'] ?? '')
                    <img height="38" src="{{ public_path('storage/') . $settings['horizontal_logo'] }}" alt="Logo" style="vertical-align: middle; margin-right: 8px; display: inline-block;">
                @else
                    <img height="38" src="{{ public_path('assets/horizontal-logo2.svg') }}" alt="Logo" style="vertical-align: middle; margin-right: 8px; display: inline-block;">
                @endif
                <span class="school-name" style="vertical-align: middle; font-size: 16px; font-weight: bold; text-transform: uppercase; display: inline-block;">{{ $settings['school_name'] }}</span>
            </div>
            <div style="text-align: center; font-size: 11px; line-height: 1.3;">
                {{ $settings['school_address'] }}<br>
                Phone: {{ !empty($settings['school_phone']) && $settings['school_phone'] != '9491920982' ? $settings['school_phone'] . ', 9491920982' : '9491920982' }} | Email: {{ $settings['school_email'] }}
            </div>
        </div>
        
        <hr style="border: 0.5px solid #000; margin: 5px 0;">

        <!-- Title -->
        <div class="ticket-title">EXAM HALL TICKET / ADMIT CARD</div>

        <!-- Student Info with Photo -->
        <table style="width: 100%; border: none; margin-bottom: 6px;">
            <tr>
                <td style="width: 82%; padding: 0; vertical-align: top; border: none;">
                    <table class="student-info-table" style="margin-bottom: 0;">
                        <tr>
                            <th>Student Name:</th>
                            <td>{{ $student->user->full_name }}</td>
                            <th>Admission No (GR No):</th>
                            <td>{{ $student->admission_no }}</td>
                        </tr>
                        <tr>
                            <th>Class & Section:</th>
                            <td>{{ $classSection->full_name }}</td>
                            <th>Roll Number:</th>
                            <td>{{ $student->roll_number }}</td>
                        </tr>
                        <tr>
                            <th>Exam Name:</th>
                            <td>{{ $exam->name }}</td>
                            <th>Academic Year:</th>
                            <td>{{ $exam->session_year->name }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 18%; text-align: center; vertical-align: top; border: none; padding-left: 8px;">
                    <div style="border: 1px solid #ccc; width: 80px; height: 95px; padding: 2px; box-sizing: border-box; text-align: center; background-color: #fafafa; margin-left: auto;">
                        @if ($student->user->getRawOriginal('image') && file_exists(public_path('storage/' . $student->user->getRawOriginal('image'))))
                            <img src="{{ public_path('storage/' . $student->user->getRawOriginal('image')) }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <div style="font-size: 8px; color: #999; padding-top: 32px;">PHOTO</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <!-- Exam Timetable Title -->
        <div style="font-size: 11px; font-weight: bold; margin-top: 4px; margin-bottom: 4px;">Exam Schedule / Timetable:</div>

        <!-- Timetable Grid -->
        <table class="timetable-table">
            <thead>
                <tr>
                    <th style="width: 8%;">Sr. No.</th>
                    <th style="width: 42%;">Subject</th>
                    <th style="width: 25%;">Exam Date</th>
                    <th style="width: 25%;">Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($timetable as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td style="text-align: left; padding-left: 4px;">{{ $item->class_subject->subject->name }} ({{ $item->class_subject->subject->type }})</td>
                        <td>{{ date($settings['date_format'], strtotime($item->date)) }}</td>
                        <td>{{ date($settings['time_format'] ?? 'h:i A', strtotime($item->start_time)) }} - {{ date($settings['time_format'] ?? 'h:i A', strtotime($item->end_time)) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center;">No exam timetable scheduled for this class.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Bottom Section: Instructions + Signatures (Pinned to bottom) -->
        <div class="ticket-bottom">
            <!-- Important Instructions -->
            <div style="font-size: 9px; line-height: 1.4; border: 1px dashed #999; padding: 4px 8px; border-radius: 3px;">
                <strong>Important Instructions for Candidates:</strong>
                <ol style="margin: 3px 0 0 14px; padding: 0;">
                    <li>Candidates must carry this card to the examination hall. Entry without a valid hall ticket is prohibited.</li>
                    <li>Be present in the exam hall at least 15 minutes before the scheduled time.</li>
                    <li>Books, bags, mobile phones, or smart devices are not allowed in the exam hall.</li>
                </ol>
            </div>

            <!-- Signatures - 3 Tabs -->
            <table class="full-width footer-signatures" style="width: 100%;">
                <tr>
                    <td style="width: 33%; text-align: left; vertical-align: bottom; height: 45px;">
                        <div style="border-top: 1px dotted #000; padding-top: 4px; margin-top: 20px;">
                            Student's Signature
                        </div>
                    </td>
                    <td style="width: 34%; text-align: center; vertical-align: bottom; height: 45px;">
                        <div style="border-top: 1px dotted #000; padding-top: 4px; margin-top: 20px;">
                            Class Teacher
                        </div>
                    </td>
                    <td style="width: 33%; text-align: right; vertical-align: bottom; height: 45px;">
                        <div style="border-top: 1px dotted #000; padding-top: 4px; margin-top: 20px;">
                            Principal / Controller of Exams
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
@endforeach

</body>
</html>
