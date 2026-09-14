<?php $__env->startSection('title'); ?>
    <?php echo e(__('dashboard')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-theme text-white mr-2">
                    <i class="fa fa-home"></i>
                </span> <?php echo e(__('dashboard')); ?>

            </h3>
        </div>
        
        <?php if(Auth::user()->hasRole('School Admin') || Auth::user()->school_id): ?>
            <div class="row">
                
                <?php if(Auth::user()->hasRole('School Admin')): ?>
                    <?php if($license_expire <= ($settings['current_plan_expiry_warning_days'] ?? 7) && $subscription): ?>
                        <div class="col-sm-12 col-md-12">
                            <div class="alert alert-danger" role="alert">
                                <li>
                                    <?php echo e(__('Kindly note that your license will expire on')); ?> <strong
                                        class="package-expire-date">
                                        <?php echo e(date('F d, Y', strtotime($subscription->end_date))); ?> - 11:59 PM. </strong>
                                    <?php echo e(__('If you want to modify your upcoming plan or remove any add-ons, please ensure that these changes are made before your current license expires')); ?>.
                                </li>
                                <li class="mt-2">
                                    <?php echo e(__('If you want to activate or deactivate students, teachers, or staff members in your upcoming plan, Please')); ?>

                                    <a href="<?php echo e(url('users/status')); ?>"><?php echo e(__('click here')); ?>.</a>
                                </li>

                                <?php if($prepiad_upcoming_plan && $prepiad_upcoming_plan->package->type == 0 && !$check_payment): ?>
                                    
                                    <?php if($paymentConfiguration && $paymentConfiguration->payment_method == 'Stripe'): ?>
                                        <li class="mt-2">
                                        <?php echo e(__('We kindly request that you make the necessary payment for the upcoming prepaid plan to avoid any interruptions in service')); ?>

                                            <a href="<?php echo e(url('subscriptions/pay-prepaid-upcoming-plan', $prepiad_upcoming_plan->package_id) . '/type/' . $prepiad_upcoming_plan_type . '/subscription/' . $prepiad_upcoming_plan->id); ?>"><?php echo e(__('click_here_to_pay')); ?></a>
                                        </li>
                                    <?php else: ?>
                                        <form action="<?php echo e(url('subscriptions/razorpay')); ?>" class="razorpay-form" method="POST">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="package_id" class="package_id" value="<?php echo e($prepiad_upcoming_plan->package_id); ?>">
                                            <input type="hidden" name="amount" class="bill_amount" value="<?php echo e($prepiad_upcoming_plan->charges); ?>">

                                            <input type="hidden" name="type" class="type" value="package">
                                            <input type="hidden" name="package_type" class="package_type" value="upcoming">

                                            <input type="hidden" name="razorpay_payment_id" class="razorpay_payment_id" value="">
                                            <input type="hidden" name="razorpay_signature" class="razorpay_signature" value="">
                                            <input type="hidden" name="razorpay_order_id" class="razorpay_order_id" value="">

                                            <input type="hidden" name="paymentTransactionId" class="paymentTransactionId" value="">

                                            <input type="hidden" name="subscription_id" class="subscription_id" value="<?php echo e($prepiad_upcoming_plan->id); ?>">
                                            <input type="hidden" name="upcoming_plan_type" value="<?php echo e($prepiad_upcoming_plan_type); ?>">

                                            <li class="mt-2">
                                                <?php echo e(__('We kindly request that you make the necessary payment for the upcoming prepaid plan to avoid any interruptions in service')); ?>

                                                <a href="#" id="razorpay-button"><?php echo e(__('click_here_to_pay')); ?></a>
                                            </li>
                                            
                                        </form>

                                        
                                    <?php endif; ?>                                        
                                    
                                <?php endif; ?>

                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="col-sm-12 col-md-12">
                        <?php $__currentLoopData = $previous_subscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subscription): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($subscription->status == 3): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo e(__('Please make the necessary payment as your license has expired on')); ?> <strong
                                        class="package-expire-date">
                                        <?php echo e(date('F d, Y', strtotime($subscription->end_date))); ?>.
                                    </strong>
                                </div>
                            <?php break; ?>
                        <?php endif; ?>
                        <?php if($subscription->status == 4): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo e(__('We apologize for inconvenience but your payment was not successful Please try to process the payment again')); ?>.
                            </div>
                        <?php break; ?>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php if(Auth::user()->hasRole('School Admin')): ?>
        <div class="row">
            
            <div class="col-md-2-4 stretch-card grid-margin">
                <div class="card">
                    <div class="card-body custom-card-body">
                        <div class="d-flex flex-row flex-wrap">
                            <div class="ms-3">
                                <?php echo e(__('total_teachers')); ?>

                                <p class="text-muted">
                                <h3><?php echo e($teacher); ?></h3>
                                </p>
                                <p class="mt-2 text-success font-weight-bold"> </p>
                            </div>
                            <img class="ml-auto" src="<?php echo e(url('images/teachers.svg')); ?>" alt="">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2-4 stretch-card grid-margin">
                <div class="card">
                    <div class="card-body custom-card-body">
                        <div class="d-flex flex-row flex-wrap">
                            <div class="ms-3">
                                <?php echo e(__('total_students')); ?>

                                <p class="text-muted">
                                <h3><?php echo e($student); ?></h3>
                                </p>
                                <p class="mt-2 text-success font-weight-bold"> </p>
                            </div>
                            <img class="ml-auto" src="<?php echo e(url('images/students.svg')); ?>" alt="">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2-4 stretch-card grid-margin">
                <div class="card">
                    <div class="card-body custom-card-body">
                        <div class="d-flex flex-row flex-wrap">
                            <div class="ms-3">
                                <?php echo e(__('Total Guardians')); ?>

                                <p class="text-muted">
                                <h3><?php echo e($parent); ?></h3>
                                </p>
                                <p class="mt-2 text-success font-weight-bold"> </p>
                            </div>
                            <img class="ml-auto" src="<?php echo e(url('images/guardians.svg')); ?>" alt="">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2-4 stretch-card grid-margin">
                <div class="card">
                    <div class="card-body custom-card-body">
                        <div class="d-flex flex-row flex-wrap">
                            <div class="ms-3">
                                <?php echo e(__('total_classes')); ?>

                                <p class="text-muted">
                                <h3><?php echo e($classes_counter); ?></h3>
                                </p>
                                <p class="mt-2 text-success font-weight-bold"> </p>
                            </div>
                            <img class="ml-auto" src="<?php echo e(url('images/classes.svg')); ?>" alt="">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2-4 stretch-card grid-margin">
                <div class="card">
                    <div class="card-body custom-card-body">
                        <div class="d-flex flex-row flex-wrap">
                            <div class="ms-3">
                                <?php echo e(__('total_streams')); ?>

                                <p class="text-muted">
                                <h3><?php echo e($streams); ?></h3>
                                </p>
                                <p class="mt-2 text-success font-weight-bold"> </p>
                            </div>
                            <img class="ml-auto" src="<?php echo e(url('images/stream.svg')); ?>" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    

    <div class="row">

        
        <?php if(Auth::user()->canany(['expense-create', 'expense-list'])): ?>
            <div class="col-md-8 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body custom-card-body">
                        <div class="row">
                            <div class="col-md-9">
                                <h4 class="card-title"><?php echo e(__('expense')); ?></h4>
                            </div>
                            <div class="col-md-3 text-right">
                                <select name="session_year_id" id="filter_expense_session_year_id"
                                    class="form-control form-control-sm">
                                    <?php $__currentLoopData = $sessionYear; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if($session->default == 1): ?>
                                            <option value="<?php echo e($session->id); ?>" selected><?php echo e($session->name); ?></option>
                                        <?php else: ?>
                                            <option value="<?php echo e($session->id); ?>"><?php echo e($session->name); ?></option>
                                        <?php endif; ?>
                                        
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>

                        <?php if(!\App\Services\FeaturesService::hasFeature('Expense Management')): ?>
                            <div
                                class="align-items-center d-flex flex-column justify-content-center v-scroll text-small">
                                <?php echo e(__('Purchase') . ' ' . __('Expense Management') . ' ' . __('to Continue using this functionality')); ?>

                            </div>
                        <?php endif; ?>

                        <?php if(\App\Services\FeaturesService::hasFeature('Expense Management')): ?>
                            <div class="chartjs-wrapper mt-2" style="height: 330px">
                                <div id="expenseChart" style="direction: ltr;"> </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-md-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body custom-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="card-title"><?php echo e(__('leaves')); ?></h3>
                        </div>
                        <div class="col-md-6 dropdown text-right">
                            <?php echo Form::select(
                                'leave_filter',
                                ['Today' => __('today'), 'Tomorrow' => __('tomorrow'), 'Upcoming' => __('upcoming')],
                                'today',
                                ['class' => 'form-control form-control-sm filter_leaves'],
                            ); ?>

                        </div>
                    </div>

                    <div class="v-scroll mt-2">
                        <table class="table custom-table">
                            <?php if(!\App\Services\FeaturesService::hasFeature('Staff Leave Management')): ?>
                                <tbody class="leave-list">
                                    <tr>
                                        <td colspan="2" class="text-center text-small">
                                            <?php echo e(__('Purchase') . ' ' . __('Staff Leave Management') . ' ' . __('to Continue using this functionality')); ?>

                                        </td>
                                    </tr>
                                </tbody>
                            <?php endif; ?>

                            <?php if(\App\Services\FeaturesService::hasFeature('Staff Leave Management')): ?>
                                <tbody class="leave-list">

                                </tbody>
                            <?php endif; ?>


                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <?php if(Auth::user()->can('attendance-list')): ?>
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body custom-card-body">
                    <div class="row">
                        <div class="col-sm-12 col-md-6">
                            <h3 class="card-title"><?php echo e(__('attendance')); ?></h3>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <?php echo Form::select('class_id', count($class_names) > 0 ? $class_names : ['' => 'Not Data Available'], null, ['class' => 'form-control form-control-sm class-section-attendance',]); ?>

                        </div>
                    </div>
                    <div id="attendanChart">

                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="col-md-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body custom-card-body">
                    <h4 class="card-title"><?php echo e(__('announcement')); ?></h4>
                    <div class="table-responsive v-scroll">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th> <?php echo e(__('no.')); ?></th>
                                    <th class="col-md-2"> <?php echo e(__('title')); ?></th>
                                    <th> <?php echo e(__('description')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($announcement)): ?>
                                    <?php $__currentLoopData = $announcement; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($key + 1); ?></td>
                                            <td><?php echo e($row->title); ?></td>
                                            <td><?php echo e($row->description); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body custom-card-body">
                    <div class="row">
                        <div class="col-md-5">
                            <h3 class="card-title">
                                <?php echo e(__('exam_result')); ?>

                            </h3>
                        </div>
                        <div class="col-md-3">
                            <select name="session_year_id" id="exam_result_session_year_id"
                                class="form-control form-control-sm">
                                <?php $__currentLoopData = $sessionYear; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($session->default == 1): ?>
                                        <option value="<?php echo e($session->id); ?>" selected><?php echo e($session->name); ?></option>
                                    <?php else: ?>
                                        <option value="<?php echo e($session->id); ?>"><?php echo e($session->name); ?></option>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select name="exam_name" id="exam_reuslt_exam_name"
                                class="form-control form-control-sm">
                                <option value=""><?php echo e(__('select') . ' ' . __('exam')); ?></option>
                                <?php $__currentLoopData = $exams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($exam->name); ?>"
                                        data-session-year="<?php echo e($exam->session_year_id); ?>">
                                        <?php echo e($exam->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="mt-1 mb-3 v-scroll">
                        <div class="exam-report" id="class-progress-report">

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php if(Auth::user()->can('fees-paid')): ?>
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body custom-card-body">
                    <div class="row">
                        <div class="col-md-7">
                            <h3 class="card-title">
                                <?php echo e(__('fees_over_due')); ?>  <span class="ml-2"><i class="fa fa-exclamation-circle" title="<?php echo e(__('inactivate_student_by_submitting_records')); ?>"></i></span>
                            </h3>
                        </div>
                        <div class="col-sm-12 col-md-5">
                            <?php echo Form::select('class_section_id', $class_section_names, null, [
                                'class' => 'form-control form-control-sm fees-over-due-class',
                                'id' => 'fees-over-due-class-section'
                            ]); ?>

                        </div>
                    </div>
                    <form method="POST" id="fees-overdue-form" class="fees-overdue-form" action="<?php echo e(route('deactivate-student-account')); ?>">
                        <div class="mt-1 mb-3 v-scroll">
                            <table class="table custom-table">
                                <?php if(!\App\Services\FeaturesService::hasFeature('Fees Management')): ?>
                                     <tbody class="leave-list">
                                        <tr>
                                            <td colspan="2" class="text-center text-small">
                                                <?php echo e(__('Purchase') . ' ' . __('Fees Management') . ' ' . __('to Continue using this functionality')); ?>

                                            </td>
                                        </tr>
                                    </tbody>
                                <?php endif; ?>

                                <?php if(\App\Services\FeaturesService::hasFeature('Fees Management')): ?>
                                    <tbody class="fees-over-due-list">
                                    </tbody>
                                <?php endif; ?>  
                            </table>           
                        </div>
                        <?php if(\App\Services\FeaturesService::hasFeature('Fees Management')): ?>
                            <input type="submit" class="btn btn-success float-right fees-overdue-btn" name="submit" value="<?php echo e(__('submit')); ?>">
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body custom-card-body">
                    <h3 class="card-title">
                        <?php echo e(__('fees_details')); ?>

                    </h3>

                    <div id="fees_details_chart">

                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body custom-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="card-title"><?php echo e(__('birthday')); ?></h3>
                        </div>
                        <div class="col-md-6 text-right">
                            <?php echo Form::select(
                                'birthday_filter',
                                ['today' => __('today'), 'this_month' => __('this_month'), 'next_month' => __('next_month')],
                                'today',
                                ['class' => 'form-control form-control-sm filter_birthday'],
                            ); ?>

                        </div>
                    </div>
                    <div class="v-scroll mt-2">
                        <table class="table custom-table">
                            <tbody class="birthday-list">

                            </tbody>
                        </table>
                    </div>


                </div>
            </div>
        </div>

        <div class="col-md-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body custom-card-body">
                    <h4 class="card-title"><?php echo e(__('holiday')); ?></h4>
                    <div class="v-scroll dashboard-description">
                        <table class="table custom-table">
                            <?php if(!\App\Services\FeaturesService::hasFeature('Holiday Management')): ?>
                                <tbody class="leave-list">
                                    <tr>
                                        <td colspan="2" class="text-center text-small">
                                            <?php echo e(__('Purchase') . ' ' . __('Holiday Management') . ' ' . __('to Continue using this functionality')); ?>

                                        </td>
                                    </tr>
                                </tbody>
                            <?php endif; ?>

                            <?php if(\App\Services\FeaturesService::hasFeature('Holiday Management')): ?>
                                <tbody>
                                    <?php $__currentLoopData = $holiday; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $holiday): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($holiday->title); ?></td>
                                        <td><span
                                                class="float-right text-muted"><?php echo e(date('d - M', strtotime($holiday->date))); ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body custom-card-body">
                    <h4 class="card-title"><?php echo e(__('student_gender')); ?></h4>
                    <div id="gender-ratio-chart"></div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>



<?php if(Auth::user()->hasRole('Super Admin') || !Auth::user()->school_id): ?>
    <div class="row">

        <div class="col-md-3 stretch-card grid-margin">
            <div class="card">
                <div class="card-body custom-card-body">
                    <div class="d-flex flex-row flex-wrap">
                        <div class="ms-3">
                            <?php echo e(__('total_schools')); ?>

                            <p class="text-muted">
                            <h3><?php echo e($super_admin['total_school']); ?></h3>
                            </p>
                            <p class="mt-2 text-success font-weight-bold"> </p>
                        </div>
                        <img class="ml-auto" src="<?php echo e(url('images/total-schools.svg')); ?>" alt="">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 stretch-card grid-margin">
            <div class="card">
                <div class="card-body custom-card-body">
                    <div class="d-flex flex-row flex-wrap">
                        <div class="ms-3">
                            <?php echo e(__('active_schools')); ?>

                            <p class="text-muted">
                            <h3><?php echo e($super_admin['active_schools']); ?></h3>
                            </p>
                            <p class="mt-2 text-success font-weight-bold"> </p>
                        </div>
                        <img class="ml-auto" src="<?php echo e(url('images/active-schools.svg')); ?>" alt="">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 stretch-card grid-margin">
            <div class="card">
                <div class="card-body custom-card-body">
                    <div class="d-flex flex-row flex-wrap">
                        <div class="ms-3">
                            <?php echo e(__('inactive_schools')); ?>

                            <p class="text-muted">
                            <h3><?php echo e($super_admin['inactive_schools']); ?></h3>
                            </p>
                            <p class="mt-2 text-success font-weight-bold"> </p>
                        </div>
                        <img class="ml-auto" src="<?php echo e(url('images/inactive-schools.svg')); ?>" alt="">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 stretch-card grid-margin">
            <div class="card">
                <div class="card-body custom-card-body">
                    <div class="d-flex flex-row flex-wrap">
                        <div class="ms-3">
                            <?php echo e(__('total_packages')); ?>

                            <p class="text-muted">
                            <h3><?php echo e($super_admin['total_packages']); ?></h3>
                            </p>
                            <p class="mt-2 text-success font-weight-bold"> </p>
                        </div>
                        <img class="ml-auto" src="<?php echo e(url('images/package.svg')); ?>" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7 grid-margin stretch-card">
            <div class="card">
                <div class="card-body custom-card-body">
                    <div class="row">
                        <div class="col-md-9">
                            <h4 class="card-title">
                                <?php echo e(__('transaction')); ?>

                            </h4>
                        </div>
                        <div class="col-md-3">
                            <?php echo Form::selectRange('year', $start_year, date('Y'), date('Y'), [
                                'class' => 'form-control form-control-sm year-filter',
                            ]); ?>

                        </div>
                    </div>

                    <div id="subscriptionTransactionChart">

                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-5 grid-margin stretch-card">
            <div class="card">
                <div class="card-body custom-card-body">
                    <h4 class="card-title">
                        <?php echo e(__('schools')); ?>

                    </h4>
                    <div class="v-scroll">
                        <table class="table custom-table">
                            <thead>
                                <th></th>
                                <th><?php echo e(__('school')); ?></th>
                                <th class="text-right"><?php echo e(__('admin')); ?></th>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $schools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo e($school->logo); ?>" onerror="onErrorImage(event)"
                                                class="me-2" alt="image">
                                        </td>
                                        <td><?php echo e($school->name); ?></td>
                                        <td class="text-right"><?php echo e($school->user->full_name); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body custom-card-body">
                    <h4 class="card-title">
                        <?php echo e(__('subscription')); ?> <?php echo e(__('details')); ?>

                    </h4>
                    <div id="packageChart"> </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body custom-card-body">
                    <h4 class="card-title">
                        <?php echo e(__('staff')); ?> <?php echo e(__('details')); ?>

                    </h4>
                    <div class="v-scroll">
                        <table class="table custom-table">
                            <?php if(!\App\Services\FeaturesService::hasFeature('Staff Management')): ?>
                                <thead>
                                    <th></th>
                                    <th><?php echo e(__('name')); ?></th>
                                    <th><?php echo e(__('role')); ?></th>
                                    <th class="text-right"><?php echo e(__('assign_schools')); ?></th>
                            </thead>
                            <?php endif; ?>
                            <?php if(\App\Services\FeaturesService::hasFeature('Staff Management')): ?>
                                <tbody>
                                    <?php $__currentLoopData = $staffs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $staff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                        <td>
                                            <img src="<?php echo e($staff->image); ?>" onerror="onErrorImage(event)"
                                                class="me-2" alt="image">
                                        </td>
                                        <td><?php echo e($staff->full_name); ?></td>
                                        <td><?php echo e($staff->roles->first()->name ?? ''); ?></td>
                                        <td><?php echo e($staff->school_names); ?></td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body custom-card-body">
                    <h4 class="card-title">
                        <?php echo e(__('addon')); ?>

                    </h4>
                    <div id="addonChart"> </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>

<?php if(Auth::user()->school_id): ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<?php endif; ?>




<?php if(!Auth::user()->school_id): ?>
<script>
    window.onload = setTimeout(() => {
        $('.year-filter').trigger('change');

        addon_graph(<?php echo json_encode($addon_graph[0]); ?>, <?php echo json_encode($addon_graph[1]); ?>);
        package_graph(<?php echo json_encode($package_graph[0]); ?>, <?php echo json_encode($package_graph[1]); ?>);
    }, 500);
</script>
<?php endif; ?>
<script>
    window.onload = setTimeout(() => {
        $('#filter_expense_session_year_id').trigger('change');
        $('.filter_birthday').trigger('change');
        $('.filter_leaves').trigger('change');
        $('#exam_result_session_year_id').trigger('change');
        
        const selectElement = document.getElementById('exam_reuslt_exam_name');
        if (selectElement) {
            var selectedIndex = selectElement.selectedIndex || 0;
            var options = selectElement.options;
            
            // Iterate through options starting from the next index
            for (var i = selectedIndex + 1; i < options.length; i++) {
                if (options[i].style.display !== "none") {
                    // Set the next visible option as selected
                    selectElement.selectedIndex = i;
                    break;
                }
            }
        }


        $('#exam_reuslt_exam_name').trigger('change');
        fees_details(<?php echo json_encode($fees_detail); ?>);

        $('.class-section-attendance').trigger('change');
        $('#fees-over-due-class-section').trigger('change');

    }, 500);
</script>

<?php if($boys || $girls): ?>
<script>
    gender_ratio(<?php echo $boys; ?>, <?php echo $girls; ?>, <?php echo $total_students; ?>);
</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/dashboard.blade.php ENDPATH**/ ?>