<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamTimetable;
use App\Models\ClassSection;
use App\Models\Students;
use App\Services\CachingService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;
use Throwable;

class HallTicketController extends Controller
{
    private CachingService $cache;

    public function __construct(CachingService $cache)
    {
        $this->cache = $cache;
    }

    public function index()
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        
        $session_year = $this->cache->getDefaultSessionYear();
        
        // Fetch class sections
        $teacherId = Auth::user()->hasRole('Teacher') ? Auth::user()->teacher->user_id : null;
        
        $classSections = ClassSection::owner()->with('class.stream', 'section', 'medium');
        if ($teacherId) {
            $classTeacher = \App\Models\ClassTeacher::where('teacher_id', $teacherId)->get();
            $classSections = $classSections->whereIn('id', $classTeacher->pluck('class_section_id'));
        }
        $classSections = $classSections->get();

        $selectedClassSectionId = request()->class_section_id;
        $selectedExamId = request()->exam_id;
        
        $exams = collect();
        $students = collect();

        if ($selectedClassSectionId) {
            $classSection = ClassSection::findOrFail($selectedClassSectionId);
            $exams = Exam::owner()->where('class_id', $classSection->class_id)
                ->where('session_year_id', $session_year->id)
                ->get();
            
            if ($selectedExamId) {
                $students = Students::owner()->whereHas('user', function ($q) {
                    $q->where('status', 1);
                })->with('user')
                    ->where('class_section_id', $selectedClassSectionId)
                    ->get();
            }
        }

        return view('exams.hall_ticket', compact('classSections', 'exams', 'students', 'selectedClassSectionId', 'selectedExamId'));
    }

    public function generate(Request $request)
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        
        $request->validate([
            'class_section_id' => 'required|integer',
            'exam_id' => 'required|integer',
            'student_ids' => 'nullable|array',
            'signatures' => 'nullable|array',
            'signatures.*.label' => 'required|string|max:100',
            'signatures.*.align' => 'nullable|in:left,center,right',
            'signatures.*.image' => 'nullable|image|max:2048',
        ]);

        try {
            $classSection = ClassSection::with('class')->findOrFail($request->class_section_id);
            $exam = Exam::findOrFail($request->exam_id);
            
            // Fetch timetable
            $timetable = ExamTimetable::where('exam_id', $request->exam_id)
                ->with('class_subject.subject')
                ->orderBy('date', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();

            // Fetch students (only active, non-deleted users)
            $studentsQuery = Students::owner()->whereHas('user', function ($q) {
                $q->where('status', 1);
            })->with('user')
                ->where('class_section_id', $request->class_section_id);
            
            if ($request->student_ids) {
                $studentsQuery->whereIn('id', $request->student_ids);
            }
            
            $students = $studentsQuery->get();

            if ($students->isEmpty()) {
                return redirect()->back()->with('error', 'No students selected or found.');
            }

            // Fetch settings for logo and name
            $settings = $this->cache->getSchoolSettings();
            
            // Handle logo pathing
            $logoData = explode("storage/", $settings['horizontal_logo'] ?? '');
            $settings['horizontal_logo'] = end($logoData);
            if (empty($settings['horizontal_logo'])) {
                $systemSettings = $this->cache->getSystemSettings();
                $sysLogoData = explode("storage/", $systemSettings['horizontal_logo'] ?? '');
                $settings['horizontal_logo'] = end($sysLogoData);
            }

            // Handle signature pathing (legacy single signature from school settings)
            if (isset($settings['signature'])) {
                $sigData = explode("storage/", $settings['signature']);
                $settings['signature'] = end($sigData);
            }

            // Process dynamic signatures from form
            $signatures = [];
            if ($request->has('signatures')) {
                foreach ($request->signatures as $key => $sig) {
                    $sigItem = [
                        'label' => $sig['label'],
                        'align' => $sig['align'] ?? 'center',
                        'image_path' => null,
                    ];
                    
                    // Check if a signature image was uploaded for this slot
                    if ($request->hasFile("signatures.{$key}.image")) {
                        $file = $request->file("signatures.{$key}.image");
                        $sigItem['image_path'] = $file->getRealPath();
                    }
                    
                    $signatures[] = $sigItem;
                }
            }
            
            // Fallback: if no signatures provided, use 3 default ones
            if (empty($signatures)) {
                $signatures = [
                    ['label' => "Student's Signature", 'align' => 'left', 'image_path' => null],
                    ['label' => 'Class Teacher', 'align' => 'center', 'image_path' => null],
                    ['label' => 'Principal', 'align' => 'right', 'image_path' => isset($settings['signature']) ? public_path('storage/' . $settings['signature']) : null],
                ];
            }

            $pdf = PDF::loadView('exams.hall_ticket_pdf', compact('students', 'exam', 'timetable', 'settings', 'classSection', 'signatures'));
            return $pdf->stream('hall_tickets.pdf');

        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "HallTicketController -> generate");
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function getExams($class_section_id)
    {
        try {
            $classSection = ClassSection::findOrFail($class_section_id);
            $session_year = $this->cache->getDefaultSessionYear();
            $exams = Exam::owner()->where('class_id', $classSection->class_id)
                ->where('session_year_id', $session_year->id)
                ->get();
            return response()->json($exams);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
