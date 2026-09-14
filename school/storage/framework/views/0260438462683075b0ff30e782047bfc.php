<!-- partial:../../partials/_sidebar.html -->
<nav class="sidebar sidebar-offcanvas" id="sidebar">

    <div class="sidebar-search pl-4 pr-4">
        <input type="text" id="menu-search" placeholder="<?php echo e(__('search_menu')); ?>" class="form-control menu-search border-theme form-control-sm">
    </div>

    <div class="sidebar-search pl-4 pr-4 mt-2">
        <input type="text" id="menu-search-mini" placeholder="<?php echo e(__('search_menu')); ?>" class="form-control d-lg-none border-theme">
    </div>

    <ul class="nav">
        
        <li class="nav-item">
            <a href="<?php echo e(url('/dashboard')); ?>" class="nav-link">
                <i class="fa fa-home menu-icon"></i>
                <span class="menu-title"><?php echo e(__('dashboard')); ?></span>
            </a>
        </li>
        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['medium-create','section-create','subject-create','class-create','subject-create','promote-student-create','transfer-student-create'])): ?>
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#academics-menu" aria-expanded="false" aria-controls="academics-menu">
                    <i class="fa fa-university menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('academics')); ?></span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="academics-menu">
                    <ul class="nav flex-column sub-menu">
                        <?php if(false): ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('medium-create')): ?>
                            <li class="nav-item"><a href="<?php echo e(route('mediums.index')); ?>" class="nav-link"> <?php echo e(__('medium')); ?> </a></li>
                        <?php endif; ?>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('section-create')): ?>
                            <li class="nav-item"><a href="<?php echo e(route('section.index')); ?>" class="nav-link"> <?php echo e(__('section')); ?> </a></li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('subject-create')): ?>
                            <li class="nav-item"><a href="<?php echo e(route('subjects.index')); ?>" class="nav-link"> <?php echo e(__('subject')); ?> </a></li>
                        <?php endif; ?>

                        <?php if(false): ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('semester-create')): ?>
                            <li class="nav-item"><a href="<?php echo e(route('semester.index')); ?>" class="nav-link"> <?php echo e(__('Semester')); ?> </a></li>
                        <?php endif; ?>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('stream-create')): ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo e(route('stream.index')); ?>"> <?php echo e(__('Stream')); ?> </a></li>
                        <?php endif; ?>

                        <?php if(false): ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('shift-create')): ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo e(route('shift.index')); ?>"> <?php echo e(__('Shift')); ?> </a></li>
                        <?php endif; ?>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('class-create')): ?>
                            <li class="nav-item"><a href="<?php echo e(route('class.index')); ?>" class="nav-link"> <?php echo e(__('Class')); ?> </a></li>
                            <li class="nav-item"><a href="<?php echo e(route('class.subject.index')); ?>" class="nav-link"> <?php echo e(__('Class Subject')); ?> </a></li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('class-group-create')): ?>
                            <li class="nav-item"><a href="<?php echo e(route('class-group.index')); ?>" class="nav-link"> <?php echo e(__('class_group')); ?> </a></li>
                        <?php endif; ?> 
                        


                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('class-section-create')): ?>
                            <li class="nav-item"><a href="<?php echo e(route('class-section.index')); ?>" class="nav-link"><?php echo e(__('Class Section & Teachers')); ?> </a></li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any('promote-student-create','transfer-student-create')): ?>
                            <li class="nav-item"><a href="<?php echo e(route('promote-student.index')); ?>" class="nav-link text-wrap"><?php echo e(__('Transfer & Promote Students')); ?></a></li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('student-create')): ?>
                            <li class="nav-item"><a href="<?php echo e(route('students.roll-number.index')); ?>" class="nav-link"><?php echo e(__('assign')); ?> <?php echo e(__('roll_no')); ?></a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        
        <?php if(\Spatie\Permission\PermissionServiceProvider::bladeMethodWrapper('hasRole', 'School Admin')): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['form-fields-list', 'form-fields-create', 'form-fields-edit', 'form-fields-delete'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('form-fields.index')); ?>">
                        <i class="fa fa-list-alt menu-icon"></i>
                        <span class="menu-title"> <?php echo e(__('custom_fields')); ?> </span>
                    </a>
                </li>
            <?php endif; ?>
        <?php endif; ?>

        
        <?php if(\Spatie\Permission\PermissionServiceProvider::bladeMethodWrapper('hasRole', 'Teacher')): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo e(route('class-section.index')); ?>">
                <i class="fa fa-university menu-icon"></i>
                <span class="menu-title"> <?php echo e(__('Class Section')); ?> </span>
            </a>
        </li>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['student-create', 'student-list', 'student-reset-password', 'class-teacher','form-fields-list', 'form-fields-create', 'form-fields-edit', 'form-fields-delete','guardian-create'])): ?>
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#student-menu" aria-expanded="false" aria-controls="academics-menu">
                    <i class="fa fa-graduation-cap menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('students')); ?></span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="student-menu">
                    <ul class="nav flex-column sub-menu">
                        
                        
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('student-create')): ?>
                            <li class="nav-item"><a href="<?php echo e(route('students.create')); ?>" class="nav-link"><?php echo e(__('student_admission')); ?></a></li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('student-create')): ?>
                            <li class="nav-item"><a href="<?php echo e(route('online-registration.index')); ?>" class="nav-link" data-access="true"><?php echo e(__('admission_inquiries')); ?></a></li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['student-list', 'class-teacher'])): ?>
                            <li class="nav-item"><a href="<?php echo e(route('students.index')); ?>" class="nav-link"><?php echo e(__('student_details')); ?></a></li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('student-reset-password')): ?>
                            <li class="nav-item"><a href="<?php echo e(route('students.reset-password.index')); ?>" class="nav-link"><?php echo e(__('students') . ' ' . __('reset_password')); ?></a></li>
                        <?php endif; ?>
                        
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('student-create')): ?>
                            <li class="nav-item"><a href="<?php echo e(route('students.create-bulk-data')); ?>" class="nav-link"><?php echo e(__('add_bulk_data')); ?></a></li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('student-edit')): ?>
                            <li class="nav-item"><a href="<?php echo e(route('students.upload-profile')); ?>" class="nav-link"><?php echo e(__('upload_profile_images')); ?></a></li>
                        <?php endif; ?>

                        
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('guardian-create')): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('guardian.index')); ?>" class="nav-link"> <?php echo e(__('Guardian')); ?> </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('teacher-create')): ?>
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#teacher-menu" aria-expanded="false" aria-controls="academics-menu">
                    <i class="fa fa-user menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('teacher')); ?></span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="teacher-menu">
                    <ul class="nav flex-column sub-menu">
                        
                        <li class="nav-item">
                            <a href="<?php echo e(route('teachers.index')); ?>" class="nav-link">
                                <span class="menu-title"><?php echo e(__('manage_teacher')); ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo e(route('teachers.create-bulk-upload')); ?>" class="nav-link">
                                <span class="menu-title"><?php echo e(__('bulk upload')); ?></span>
                            </a>
                        </li>

                    </ul>
                </div>
            </li>
        <?php endif; ?>



        
        <?php if(Auth::user()->hasRole('Teacher')): ?>
            <li class="nav-item">
                <a href="<?php echo e(route('timetable.teacher.show', Auth::user()->id)); ?>" class="nav-link" data-access="true">
                    <i class="fa fa-calendar menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('timetable')); ?></span>
                </a>
            </li>
        <?php else: ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['timetable-create', 'timetable-list'])): ?>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#timetable-menu" aria-expanded="false" aria-controls="timetable-menu" data-access="true">
                        <i class="fa fa-calendar menu-icon"></i>
                        <span class="menu-title"><?php echo e(__('timetable')); ?></span>
                        <i class="menu-arrow"></i>
                    </a>

                    <div class="collapse" id="timetable-menu">
                        <ul class="nav flex-column sub-menu">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('timetable-create')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('timetable.index')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true"><?php echo e(__('create_timetable')); ?> </a>
                                </li>
                            <?php endif; ?>

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('timetable-list')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('timetable.teacher.index')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                                        <?php echo e(__('teacher_timetable')); ?>

                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
            <?php endif; ?>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['holiday-create', 'holiday-list'])): ?>
            <li class="nav-item">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('holiday-list')): ?>
                    <a href="<?php echo e(route('holiday.index')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                        <i class="fa fa-calendar-check-o menu-icon"></i>
                        <span class="menu-title"><?php echo e(__('holiday_list')); ?></span>
                    </a>
                <?php endif; ?>
            </li>
        <?php endif; ?>
        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['lesson-list', 'lesson-create', 'lesson-edit', 'lesson-delete', 'topic-list', 'topic-create',
            'topic-edit', 'topic-delete'])): ?>
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#subject-lesson-menu" aria-expanded="false" aria-controls="subject-lesson-menu" data-access="true">
                    <i class="fa fa-book menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('subject_lesson')); ?></span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="subject-lesson-menu">
                    <ul class="nav flex-column sub-menu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['lesson-list', 'lesson-create', 'lesson-edit', 'lesson-delete'])): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(url('lesson')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true"> <?php echo e(__('create_lesson')); ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['topic-list', 'topic-create', 'topic-edit', 'topic-delete'])): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(url('lesson-topic')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true"> <?php echo e(__('create_topic')); ?></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['assignment-create', 'assignment-submission'])): ?>
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#student-assignment-menu" aria-expanded="false"
                   aria-controls="student-assignment-menu" data-access="true">
                   <i class="fa fa-tasks menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('student_assignment')); ?></span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="student-assignment-menu">
                    <ul class="nav flex-column sub-menu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('assignment-create')): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('assignment.index')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                                    <?php echo e(__('create_assignment')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('assignment-submission')): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('assignment.submission')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                                    <?php echo e(__('assignment_submission')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('slider-create')): ?>
            <li class="nav-item">
                <a href="<?php echo e(route('sliders.index')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                    <i class="fa fa-list menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('sliders')); ?></span>
                </a>
            </li>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['notification-create','notification-list','notification-delete'])): ?>
            <li class="nav-item">
                <a href="<?php echo e(route('notifications.index')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                    <i class="fa fa-bell menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('notification')); ?></span>
                </a>
            </li>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['class-teacher','attendance-list','attendance-create','attendance-edit','attendance-delete'])): ?>
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#attendance-menu" data-access="true" aria-expanded="false"
                   aria-controls="attendance-menu">
                   <i class="fa fa-check menu-icon"></i>
                   <span class="menu-title"><?php echo e(__('attendance')); ?></span>
                   <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="attendance-menu">
                    <ul class="nav flex-column sub-menu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['class-teacher','attendance-create'])): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('attendance.index')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                                    <?php echo e(__('add_attendance')); ?>

                                </a>
                            </li>
                        <?php endif; ?>

                        
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['class-teacher','attendance-list'])): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('attendance.view')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                                    <?php echo e(__('view_attendance')); ?>

                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="<?php echo e(route('attendance.month')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                                    <?php echo e(__('month_wise')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('announcement-create')): ?>
            <li class="nav-item">
                <a href="<?php echo e(route('announcement.index')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                    <i class="fa fa-bullhorn menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('announcement')); ?></span>
                </a>
            </li>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['exam-create', 'exam-upload-marks', 'grade-create', 'exam-result','view-exam-marks'])): ?>
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#exam-menu" aria-expanded="false"
                   aria-controls="exam-menu" data-access="true">
                   <i class="fa fa-book menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('Offline Exam')); ?></span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="exam-menu">
                    <ul class="nav flex-column sub-menu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('exam-create')): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('exams.index')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true"> <?php echo e(__('manage_offline_exam')); ?>

                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('exams.hall-ticket')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true"> <?php echo e(__('Generate Hall Ticket')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-exam-marks')): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('exam.view-marks')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true"> <?php echo e(__('Unpublished Exam Marks')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                      
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('exam-upload-marks')): ?>

                            <li class="nav-item">
                                <a href="<?php echo e(route('exams.timetable')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                                    <?php echo e(__('timetable')); ?>

                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="<?php echo e(route('exams.upload-marks')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                                    <?php echo e(__('upload')); ?> <?php echo e(__('Exam Marks')); ?>

                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="<?php echo e(route('exam.bulk-upload-marks')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                                    <?php echo e(__('bulk upload')); ?> <?php echo e(__('Exam Marks')); ?>

                                </a>
                            </li>
                            
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('exam-result')): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('exams.get-result')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                                    <?php echo e(__('Published Exam Result')); ?>

                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('grade-create')): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('exam.grade.index')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                                    <?php echo e(__('exam_grade')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['online-exam-create', 'online-exam-list', 'online-exam-edit', 'online-exam-delete'])): ?>
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#online-exam-menu" aria-expanded="false"
                   aria-controls="online-exam-menu" data-access="true">
                   <i class="fa fa-laptop menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('online_exam')); ?></span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="online-exam-menu">
                    <ul class="nav flex-column sub-menu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('online-exam-list')): ?>
                            <li class="nav-item">

                                <a href="<?php echo e(route('online-exam.index')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true"> <?php echo e(__('manage_online_exam')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('online-exam-create')): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('online-exam-question.index')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true"> <?php echo e(__('manage_questions')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['fees-list', 'fees-type-list', 'fees-classes-list', 'fees-paid'])): ?>
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#fees-menu" aria-expanded="false" aria-controls="fees-menu" data-access="true">
                    <i class="fa fa-dollar menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('Fees')); ?></span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="fees-menu">
                    <ul class="nav flex-column sub-menu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('fees-type-list')): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('fees-type.index')); ?>" class="nav-link" data-access="true"> <?php echo e(__('Fees Type')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('fees-list')): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('fees.index')); ?>" class="nav-link" data-access="true"> <?php echo e(__('Manage Fee')); ?></a>
                            </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('fees-paid')): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('fees.paid.index')); ?>" class="nav-link" data-access="true"> <?php echo e(__('Student Fees')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('fees-paid')): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('fees.transactions.log.index')); ?>" class="nav-link" data-access="true"> <?php echo e(__('Fees Transaction Logs')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['leave-list', 'leave-create', 'leave-edit', 'leave-delete'])): ?>
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#staff-leave-menu" data-access="true" aria-expanded="false"
                   aria-controls="staff-leave-menu">
                   <i class="fa fa-plane menu-icon"></i>
                   <span class="menu-title"><?php echo e(__('leave')); ?></span>
                   <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="staff-leave-menu">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a href="<?php echo e(route('leave.index')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                                <?php echo e(__('apply_leave')); ?>

                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo e(route('leave.report')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                                <?php echo e(__('leave_report')); ?>

                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <?php if(Auth::user()->school_id && Auth::user()->staff): ?>
            <li class="nav-item">
                <a href="<?php echo e(route('payroll.slip.index')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                    <i class="fa fa-money menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('payroll')); ?> <?php echo e(__('slips')); ?></span>
                </a>
            </li>
        <?php endif; ?>

        <?php if(!env('STANDALONE_SCHOOL')): ?>
        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['schools-list','schools-create', 'schools-edit', 'schools-delete','school-custom-field-list','school-custom-field-create','school-custom-field-edit','school-custom-field-delete'])): ?>
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#school-menu" aria-expanded="false" aria-controls="school-menu">
                    <i class="fa fa-university menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('schools')); ?></span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="school-menu">
                    <ul class="nav flex-column sub-menu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['school-custom-field-list','school-custom-field-create','school-custom-field-edit','school-custom-field-delete'])): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('school-custom-fields.index')); ?>" class="nav-link">
                                    <?php echo e(__('school_register_form_fields')); ?>

                                </a>
                            </li>
                            <?php if(isset($systemSettings['school_inquiry']) && $systemSettings['school_inquiry'] == 1): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('school-inquiry.index')); ?>" class="nav-link">
                                        <?php echo e(__('school_inquires')); ?>

                                    </a>
                                </li>  
                            <?php endif; ?>
                        <?php endif; ?> 
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['schools-list','schools-create', 'schools-edit', 'schools-delete'])): ?> 
                            <li class="nav-item">
                                <a href="<?php echo e(route('schools.index')); ?>" class="nav-link">
                                    <?php echo e(__('schools_details')); ?>

                                </a>
                            </li> 
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
        <?php endif; ?>


        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['package-list', 'package-create', 'package-edit', 'package-delete'])): ?>
            <li class="nav-item">
                <a href="<?php echo e(route('package.index')); ?>" class="nav-link">
                    <i class="fa fa-codepen menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('package')); ?></span>
                </a>
            </li>
        <?php endif; ?>
        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['addons-list', 'addons-create', 'addons-edit', 'addons-delete'])): ?>
            <li class="nav-item">
                <a href="<?php echo e(route('addons.index')); ?>" class="nav-link">
                    <i class="fa fa-puzzle-piece menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('addons')); ?></span>
                </a>
            </li>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['addons-list', 'addons-create', 'addons-edit', 'addons-delete','package-list', 'package-create', 'package-edit', 'package-delete'])): ?>
            <li class="nav-item">
                <a href="<?php echo e(url('features')); ?>" class="nav-link">
                    <i class="fa fa-list-ul menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('features')); ?></span>
                </a>
            </li>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('subscription-view')): ?>
            <li class="nav-item">
                <a href="<?php echo e(url('subscriptions/report')); ?>" class="nav-link">
                    <i class="fa fa-puzzle-piece menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('subscription')); ?></span>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?php echo e(url('subscriptions/transactions')); ?>" class="nav-link">
                    <i class="fa fa-money menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('subscription_transaction')); ?></span>
                </a>
            </li>
        <?php endif; ?>
        <?php endif; ?>


        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['expense-category-create', 'expense-category-list','expense-category-edit', 'expense-category-delete','expense-create', 'expense-list','expense-edit', 'expense-delete'])): ?>
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#expense-menu" aria-expanded="false" aria-controls="expense-menu" data-access="true">
                    <i class="fa fa-money menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('expense')); ?></span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="expense-menu">
                    <ul class="nav flex-column sub-menu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['expense-category-create', 'expense-category-list','expense-category-edit', 'expense-category-delete'])): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('expense-category.index')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true"><?php echo e(__('manage_category')); ?> </a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['expense-create', 'expense-list','expense-edit', 'expense-delete'])): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('expense.index')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                                    <?php echo e(__('manage_expense')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['payroll-create', 'payroll-list', 'payroll-edit', 'payroll-delete','payroll-settings-list','payroll-settings-create','payroll-settings-edit', 'payroll-settings-delete'])): ?>
            <li class="nav-item">
                <a href="#payroll-menu" class="nav-link" data-toggle="collapse" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                    <i class="fa fa-credit-card-alt menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('payroll')); ?></span>    
                    <i class="menu-arrow"></i>       
                </a>
                <div class="collapse" id="payroll-menu">
                    <ul class="nav flex-column sub-menu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['payroll-create', 'payroll-list', 'payroll-edit', 'payroll-delete'])): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('payroll.index')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true"><?php echo e(__('manage_payroll')); ?> </a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['payroll-settings-list','payroll-settings-create','payroll-settings-edit', 'payroll-settings-delete'])): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('payroll-setting.index')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                                    <?php echo e(__('payroll_setting')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        
        

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['gallery-create','gallery-list','gallery-edit','gallery-delete'])): ?>
            <li class="nav-item">
                <a href="<?php echo e(route('gallery.index')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                    <i class="fa fa-picture-o menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('gallery')); ?></span>
                </a>
            </li>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['certificate-create', 'certificate-list','certificate-edit', 'certificate-delete','student-list', 'class-teacher','id-card-settings'])): ?>
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#certificate-menu" aria-expanded="false" aria-controls="certificate-menu" data-access="true">
                    <i class="fa fa-trophy menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('certificate_id_card')); ?></span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="certificate-menu">
                    <ul class="nav flex-column sub-menu">
                        
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['certificate-create', 'certificate-list','certificate-edit', 'certificate-delete'])): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(url('certificate-template')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                                    <?php echo e(__('certificate_template')); ?>

                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['certificate-list'])): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(url('certificate')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                                    <?php echo e(__('student_certificate')); ?>

                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['certificate-list'])): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(url('certificate/staff-certificate')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                                    <?php echo e(__('staff_certificate')); ?>

                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('id-card-settings')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('id-card-settings')); ?>" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true"><?php echo e(__('id_card_settings')); ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['student-list', 'class-teacher'])): ?>
                            <li class="nav-item"><a href="<?php echo e(route('students.generate-id-card-index')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true"><?php echo e(__('student_id_card')); ?></a></li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('staff-list')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('staff.id-card')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true"><?php echo e(__('staff_id_card')); ?></a>
                                </li>
                            <?php endif; ?>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <?php if(Auth::user()->school_id): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['role-list', 'role-create', 'role-edit', 'role-delete', 'staff-list', 'staff-create', 'staff-edit','staff-delete'])): ?>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#staff-management" aria-expanded="false" aria-controls="staff-management-menu" data-access="true">
                        <i class="fa fa-user-secret menu-icon"></i>
                        <span class="menu-title"><?php echo e(__('Staff Management')); ?></span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="staff-management">
                        <ul class="nav flex-column sub-menu">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['role-list', 'role-create', 'role-edit', 'role-delete'])): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('roles.index')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true"><?php echo e(__('Role & Permission')); ?></a>
                                </li>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['staff-list', 'staff-create', 'staff-edit', 'staff-delete'])): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('staff.index')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true"><?php echo e(__('staff')); ?></a>
                                </li>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['staff-list', 'staff-create', 'staff-edit', 'staff-delete'])): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('staff.create-bulk-upload')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true"><?php echo e(__('bulk upload')); ?></a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
            <?php endif; ?>

            
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['approve-leave'])): ?>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#staff-leave-management" aria-expanded="false" aria-controls="staff-leave-management-menu" data-access="true">
                        <i class="fa fa-plane menu-icon"></i>
                        <span class="menu-title"><?php echo e(__('Staff Leave')); ?></span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="staff-leave-management">
                        <ul class="nav flex-column sub-menu">

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('approve-leave')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('leave.request')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true"><?php echo e(__('staff')); ?> <?php echo e(__('leave')); ?></a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo e(url('leave/report')); ?>" class="nav-link" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true"><?php echo e(__('leave_report')); ?></a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
            <?php endif; ?>
        <?php else: ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['role-list', 'role-create', 'role-edit', 'role-delete', 'staff-list', 'staff-create', 'staff-edit','staff-delete'])): ?>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#staff-management" aria-expanded="false" aria-controls="staff-management-menu">
                        <i class="fa fa-user-secret menu-icon"></i>
                        <span class="menu-title"><?php echo e(__('Staff Management')); ?></span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="staff-management">
                        <ul class="nav flex-column sub-menu">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['role-list', 'role-create', 'role-edit', 'role-delete'])): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('roles.index')); ?>" class="nav-link"><?php echo e(__('Role & Permission')); ?></a>
                                </li>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['staff-list', 'staff-create', 'staff-edit', 'staff-delete'])): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('staff.index')); ?>" class="nav-link"><?php echo e(__('staff')); ?></a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['custom-school-email'])): ?>
            <li class="nav-item">
                <a href="<?php echo e(route('schools.send.mail')); ?>" class="nav-link">
                    <i class="fa fa-envelope menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('email_schools')); ?></span>
                </a>
            </li>
        <?php endif; ?>


        
        <?php if(!env('STANDALONE_SCHOOL')): ?>
        <?php if(\Spatie\Permission\PermissionServiceProvider::bladeMethodWrapper('hasRole', 'School Admin')): ?>
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#subscription" aria-expanded="false"
               aria-controls="subscription-menu">
               <i class="fa fa-puzzle-piece menu-icon"></i>
                <span class="menu-title"><?php echo e(__('subscription')); ?></span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="subscription">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('subscriptions.history')); ?>"><?php echo e(__('subscription')); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('subscriptions.index')); ?>"><?php echo e(__('plans')); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('addons.plan')); ?>"><?php echo e(__('addons')); ?></a>
                    </li>
                </ul>
            </div>
        </li>
        <?php endif; ?>

        
        <li class="nav-item">
            <a href="<?php echo e(url('staff/support')); ?>" class="nav-link">
                <i class="fa fa-question menu-icon"></i>
                <span class="menu-title"><?php echo e(__('support')); ?></span>
            </a>
        </li>

        <li class="nav-item">
            <a href="<?php echo e(url('features')); ?>" class="nav-link">
                <i class="fa fa-list-ul menu-icon"></i>
                <span class="menu-title"><?php echo e(__('features')); ?></span>
            </a>
        </li>

        <?php endif; ?>

        <?php if(!env('STANDALONE_SCHOOL')): ?>
        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('web-settings')): ?>
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#web_settings" aria-expanded="false"
               aria-controls="web_settings-menu">
               <i class="fa fa-cogs menu-icon"></i>
                <span class="menu-title"><?php echo e(__('web_settings')); ?></span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="web_settings">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('web-settings.index')); ?>"><?php echo e(__('general_settings')); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('web-settings.feature.sections')); ?>"><?php echo e(__('feature_sections')); ?></a>
                    </li>

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['faqs-create','faqs-list','faqs-edit','faqs-delete'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo e(route('faqs.index')); ?>"><?php echo e(__('faqs')); ?></a>
                        </li>
                    <?php endif; ?>

                </ul>
            </div>
        </li>
        <?php endif; ?>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('school-web-settings')): ?>
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#web_settings" aria-expanded="false"
                aria-controls="web_settings-menu" data-access="true">
                <i class="fa fa-cogs menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('web_settings')); ?></span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="web_settings">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo e(route('school.web-settings.index')); ?>" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true"><?php echo e(__('content')); ?></a>
                        </li>
                        
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['faqs-create','faqs-list','faqs-edit','faqs-delete'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('faqs.index')); ?>" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true"><?php echo e(__('faqs')); ?></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
        <?php endif; ?>
    
        


        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['app-settings', 'language-list', 'school-setting-manage', 'system-setting-manage',
            'fcm-setting-manage', 'email-setting-create', 'privacy-policy', 'contact-us', 'about-us','guidance-create','guidance-list','guidance-edit','guidance-delete', 'email-template'])): ?>
            <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#settings-menu" aria-expanded="false" aria-controls="settings-menu">
                    <i class="fa fa-cog menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('system_settings')); ?></span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="settings-menu">
                    <ul class="nav flex-column sub-menu">
                        <?php if(!env('STANDALONE_SCHOOL')): ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('app-settings')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('system-settings.app')); ?>"><?php echo e(__('app_settings')); ?></a>
                            </li>
                        <?php endif; ?>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('school-setting-manage')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('school-settings.index')); ?>"><?php echo e(__('general_settings')); ?></a>
                            </li>

                            
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('session-year.index')); ?>"><?php echo e(__('session_year')); ?></a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('leave-master.index')); ?>" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true"><?php echo e(__('leave')); ?> <?php echo e(__('settings')); ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if(!env('STANDALONE_SCHOOL')): ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('system-setting-manage')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('system-settings.index')); ?>"><?php echo e(__('general_settings')); ?></a>
                            </li>
                        <?php endif; ?>
                        <?php endif; ?>

                        <?php if(!env('STANDALONE_SCHOOL')): ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('subscription-settings')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('system-settings.subscription-settings')); ?>"><?php echo e(__('subscription_settings')); ?></a>
                            </li>
                        <?php endif; ?>
                        <?php endif; ?>

                        
                        <?php if(!env('STANDALONE_SCHOOL')): ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['guidance-create','guidance-list','guidance-edit','guidance-delete'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('guidances.index')); ?>"><?php echo e(__('guidance')); ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('language-list')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(url('language')); ?>">
                                    <?php echo e(__('language_settings')); ?></a>
                            </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('fcm-setting-manage')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('system-settings.fcm')); ?>"> <?php echo e(__('notification_settings')); ?></a>
                            </li>
                        <?php endif; ?>
                        <?php endif; ?>

                        

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('school-setting-manage')): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('school-settings.online-exam.index')); ?>" class="nav-link text-wrap" data-name="<?php echo e(Auth::user()->getRoleNames()[0]); ?>" data-access="true">
                                    <?php echo e(__('online_exam_terms_condition')); ?>

                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if(!env('STANDALONE_SCHOOL')): ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('email-setting-create')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('system-settings.email.index')); ?>"><?php echo e(__('email_configuration')); ?></a>
                            </li>
                        <?php endif; ?>

                        
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('email-setting-create')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('system-settings.email.template')); ?>"><?php echo e(__('email_template')); ?></a>
                            </li>
                        <?php endif; ?>
                        <?php endif; ?>

                        
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('email-template')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('school-settings.email.template')); ?>"><?php echo e(__('email_template')); ?></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('school-settings.whatsapp.template')); ?>"><?php echo e(__('WhatsApp Template')); ?></a>
                            </li>
                        <?php endif; ?>

                        
                        <?php if(!env('STANDALONE_SCHOOL')): ?>
                        <?php if(\Spatie\Permission\PermissionServiceProvider::bladeMethodWrapper('hasAnyRole', ['Super Admin','School Admin'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo e(route('system-settings.payment.index')); ?>"><?php echo e(__('Payment Settings')); ?></a>
                        </li>
                        <?php endif; ?>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('school-setting-manage')): ?>
                        <li class="nav-item">
                            <a class="nav-link" data-access="true" href="<?php echo e(route('school-settings.third-party')); ?>"><?php echo e(__('Third-Party APIs')); ?></a>
                        </li>
                        <?php endif; ?>

                        <?php if(!env('STANDALONE_SCHOOL')): ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('system-setting-manage')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo e(route('system-settings.third-party')); ?>"><?php echo e(__('Third-Party APIs')); ?></a>
                        </li>
                        <?php endif; ?>
                        <?php endif; ?>

                        

                        

                        <?php if(!env('STANDALONE_SCHOOL')): ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('contact-us')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('system-settings.contact-us')); ?>"> <?php echo e(__('contact_us')); ?></a>
                            </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('about-us')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('system-settings.about-us')); ?>"> <?php echo e(__('about_us')); ?>

                                </a>
                            </li>
                        <?php endif; ?>
                        <?php endif; ?>

                        <?php if(\Spatie\Permission\PermissionServiceProvider::bladeMethodWrapper('hasRole', 'School Admin')): ?>
                        
                        
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo e(route('school-settings.privacy-policy')); ?>"><?php echo e(__('privacy_policy')); ?></a>
                        </li>

                        
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo e(route('school-settings.terms-condition')); ?>"><?php echo e(__('terms_condition')); ?></a>
                        </li>

                        

                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo e(route('school-settings.refund-cancellation')); ?>"><?php echo e(__('refund_cancellation')); ?></a>
                        </li>

                        <?php endif; ?>

                        <?php if(!env('STANDALONE_SCHOOL')): ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('privacy-policy')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('system-settings.privacy-policy')); ?>"><?php echo e(__('privacy_policy')); ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('terms-condition')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('system-settings.terms-condition')); ?>"><?php echo e(__('terms_condition')); ?></a>
                            </li>
                        <?php endif; ?>
                        <?php endif; ?>

                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <?php if(Auth::user()->hasRole('Super Admin')): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo e(route('system-update.index')); ?>">
                    <i class="fa fa-cloud-download menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('system_update')); ?></span>
                </a>
            </li>
        <?php endif; ?>

        <?php if(Auth::user()->hasRole(['Super Admin','School Admin'])): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo e(route('database-backup.index')); ?>">
                    <i class="fa fa-database menu-icon"></i>
                    <span class="menu-title"><?php echo e(__('database_backup')); ?></span>
                </a>
            </li>
        <?php endif; ?>

    </ul>
</nav>
<?php /**PATH /home/shacartc/school.tehub.in/resources/views/layouts/sidebar.blade.php ENDPATH**/ ?>