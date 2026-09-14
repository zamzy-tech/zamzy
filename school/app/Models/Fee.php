<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Fee extends Model {
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'due_date',
        'due_charges',
        'due_charges_amount',
        'include_fee_installments',
        'class_id',
        'school_id',
        'session_year_id',
        'created_at',
        'updated_at'
    ];
    protected $appends = ['include_fee_installments', 'total_compulsory_fees', 'total_optional_fees', 'compulsory_fees', 'optional_fees'];

    //'compulsory_fees','optional_fees',

    public function installments() {
        return $this->hasMany(FeesInstallment::class, 'fees_id');
    }

    public function fees_class_type() {
        return $this->hasMany(FeesClassType::class, 'fees_id');
    }

    //    public function type() {
    //        /*
    //         * NOTE : This relationship is currently only depends on Fees ID. Ideally there should be some way to add multiple foreignkey.
    //         * Find out the way to check class_id also here.
    //        */
    //        return $this->hasManyThrough(FeesType::class,FeesClassType::class, 'fees_id','id','id','fees_type_id');
    //    }

    public function fees_paid() {
        return $this->hasMany(FeesPaid::class, 'fees_id')->withTrashed();
    }

    public function class() {
        return $this->belongsTo(ClassSchool::class)->withTrashed();
    }

    public function session_year() {
        return $this->belongsTo(SessionYear::class)->withTrashed();
    }

    //    public function getCompulsoryFeesAttribute() {
    //        if ($this->relationLoaded('fees_class')) {
    //            return $this->fees_class->where('optional',0);
    //        }
    //        return null;
    //    }
    //
    //    public function getOptionalFeesAttribute() {
    //        if ($this->relationLoaded('fees_class')) {
    //            return $this->fees_class->where('optional',1);
    //        }
    //        return null;
    //    }

    public function getIncludeFeeInstallmentsAttribute() {
        if ($this->relationLoaded('installments')) {
            return $this->installments->count() > 0;
        }
        return null;
    }

    public function getTotalCompulsoryFeesAttribute() {
        if ($this->relationLoaded('fees_class_type')) {
            $compulsoryFees = $this->fees_class_type->filter(function ($data) {
                return $data->optional == 0;
            });
            return $compulsoryFees->sum('amount');
        }
        return null;
    }

    public function getTotalOptionalFeesAttribute() {
        if ($this->relationLoaded('fees_class_type')) {
            $optionalFees = $this->fees_class_type->filter(function ($data) {
                return $data->optional == 1;
            });
            return $optionalFees->sum('amount');
        }
        return null;
    }


    public function getCompulsoryFeesAttribute() {
        if ($this->relationLoaded('fees_class_type')) {
            $compulsoryFees = $this->fees_class_type->filter(function ($data) {
                return $data->optional == 0;
            });
            // Reset the keys
            $compulsoryFees = $compulsoryFees->values();

            return $compulsoryFees;
        }
        return null;
    }

    public function getOptionalFeesAttribute() {
        if ($this->relationLoaded('fees_class_type')) {
            $optionalFees = $this->fees_class_type->filter(function ($data) {
                return $data->optional == 1;
            });
            // Reset the keys
            $optionalFees = $optionalFees->values();

            return $optionalFees;
        }
        return null;
    }

    public function getDueDateAttribute($value) {
        //        $data = getSchoolSettings('date_format');
        return date('d-m-Y', strtotime($value));
    }

    protected function setDueDateAttribute($value) {
        $this->attributes['due_date'] = date('Y-m-d', strtotime($value));
    }

    public function scopeOwner($query) {
        if (Auth::check()) {
            if (Auth::user()->hasRole('Super Admin')) {
                return $query;
            }

            if (Auth::user()->hasRole('School Admin') || Auth::user()->hasRole('Teacher')) {
                return $query->where('school_id', Auth::user()->school_id);
            }

            if (Auth::user()->hasRole('Student')) {
                return $query->where('school_id', Auth::user()->school_id);
            }

            if (Auth::user()->school_id) {
                return $query->where('school_id', Auth::user()->school_id);
            }
        }

        return $query;
    }

    public function filterForStudent($student) {
        if (!$student) return $this;
        
        $studentModel = null;
        if ($student instanceof \App\Models\User) {
            $studentModel = $student->student;
        } elseif ($student instanceof \App\Models\Students) {
            $studentModel = $student;
        }
        
        if (!$studentModel) return $this;
        
        $applyClassFee = $studentModel->apply_class_fee;
        $applyVanFee = $studentModel->apply_van_fee;
        
        if ($this->relationLoaded('fees_class_type')) {
            $filtered = $this->fees_class_type->filter(function ($fct) use ($applyClassFee, $applyVanFee) {
                $typeName = strtolower($fct->fees_type->name ?? '');
                if (strpos($typeName, 'van') !== false) {
                    if (strpos($typeName, '1') !== false) {
                        return $applyVanFee == 1;
                    } elseif (strpos($typeName, '2') !== false) {
                        return $applyVanFee == 2;
                    } else {
                        return $applyVanFee > 0;
                    }
                } else {
                    return $applyClassFee == 1;
                }
            });

            // Check if a van fee is already present in the filtered records
            $hasVanFee = $filtered->contains(function ($fct) {
                return strpos(strtolower($fct->fees_type->name ?? ''), 'van') !== false;
            });
            
            if (!$hasVanFee && $applyVanFee > 0) {
                $vanFeeAmount = ($applyVanFee == 1) ? 9600 : 12000;
                $vanFeeTypeName = ($applyVanFee == 1) ? 'Van Fees 1' : 'Van Fees 2';
                
                $vanFeeType = new \App\Models\FeesType([
                    'id' => ($applyVanFee == 1 ? 3 : 4),
                    'name' => $vanFeeTypeName,
                ]);
                $vanClassType = new \App\Models\FeesClassType([
                    'id' => 999999000 + $applyVanFee,
                    'fees_id' => $this->id,
                    'fees_type_id' => $vanFeeType->id,
                    'amount' => $vanFeeAmount,
                    'optional' => 0,
                ]);
                $vanClassType->setRelation('fees_type', $vanFeeType);
                $filtered->push($vanClassType);
            }
            
            // Push admission fee if checked and not already present
            $hasAdmissionFee = $filtered->contains(function ($fct) {
                return strpos(strtolower($fct->fees_type->name ?? ''), 'admission') !== false;
            });

            if (!$hasAdmissionFee && $studentModel->apply_admission_fee) {
                $admissionFeeType = new \App\Models\FeesType([
                    'id' => 999999003,
                    'name' => 'Admission Fee',
                ]);
                $admissionClassType = new \App\Models\FeesClassType([
                    'id' => 999999003,
                    'fees_id' => $this->id,
                    'fees_type_id' => $admissionFeeType->id,
                    'amount' => 2000,
                    'optional' => 0,
                ]);
                $admissionClassType->setRelation('fees_type', $admissionFeeType);
                $filtered->push($admissionClassType);
            }

            // Push previous year balance if greater than 0
            if (isset($studentModel->previous_year_balance) && $studentModel->previous_year_balance > 0) {
                $prevYearBalanceType = new \App\Models\FeesType([
                    'id' => 999999004,
                    'name' => 'Previous Year Balance',
                ]);
                $prevYearBalanceClassType = new \App\Models\FeesClassType([
                    'id' => 999999004,
                    'fees_id' => $this->id,
                    'fees_type_id' => $prevYearBalanceType->id,
                    'amount' => $studentModel->previous_year_balance,
                    'optional' => 0,
                ]);
                $prevYearBalanceClassType->setRelation('fees_type', $prevYearBalanceType);
                $filtered->push($prevYearBalanceClassType);
            }

            $this->setRelation('fees_class_type', $filtered->values());
        }

        // Custom EMI payment packages mapping
        if (!empty($studentModel->payment_package)) {
            $totalCompulsoryFees = $this->total_compulsory_fees;
            
            $hasAdmissionInRelation = false;
            if ($this->relationLoaded('fees_class_type')) {
                $hasAdmissionInRelation = $this->fees_class_type->contains(function ($fct) {
                    return strpos(strtolower($fct->fees_type->name ?? ''), 'admission') !== false;
                });
            }
            if ($studentModel->apply_admission_fee && !$hasAdmissionInRelation) {
                $totalCompulsoryFees += 2000;
            }

            $hasVanInRelation = false;
            if ($this->relationLoaded('fees_class_type')) {
                $hasVanInRelation = $this->fees_class_type->contains(function ($fct) {
                    return strpos(strtolower($fct->fees_type->name ?? ''), 'van') !== false;
                });
            }
            if ($applyVanFee > 0 && !$hasVanInRelation) {
                $totalCompulsoryFees += ($applyVanFee == 1) ? 9600 : 12000;
            }
            
            // Find initial payment (type 'Full Payment' compulsory fees)
            $initialPayment = 0;
            $studentUser = $student instanceof \App\Models\User ? $student : $studentModel->user;
            if ($studentUser) {
                if ($studentUser->relationLoaded('compulsory_fees')) {
                    $initialPayment = $studentUser->compulsory_fees->where('type', 'Full Payment')->sum('amount');
                } else {
                    $initialPayment = \App\Models\CompulsoryFee::where([
                        'student_id' => $studentModel->user_id,
                        'type' => 'Full Payment'
                    ])->sum('amount');
                }
            }
            
            $remainingAmountToDivide = max(0, $totalCompulsoryFees - $initialPayment);
            $customInstallments = [];
            $payment_package = $studentModel->payment_package;
            
            if ($payment_package === 'yearly') {
                $customInstallments[] = (new \App\Models\FeesInstallment())->forceFill([
                    'id' => 1000000001,
                    'name' => 'Yearly Pay',
                    'due_date' => '2026-06-30',
                    'due_charges_type' => 'fixed',
                    'due_charges' => 0,
                    'amount' => $remainingAmountToDivide,
                    'fees_id' => $this->id,
                ]);
            } elseif ($payment_package === 'half_yearly') {
                $part = round($remainingAmountToDivide / 2, 2);
                $customInstallments[] = (new \App\Models\FeesInstallment())->forceFill([
                    'id' => 1000000001,
                    'name' => '1st Half',
                    'due_date' => '2026-06-30',
                    'due_charges_type' => 'fixed',
                    'due_charges' => 0,
                    'amount' => $part,
                    'fees_id' => $this->id,
                ]);
                $customInstallments[] = (new \App\Models\FeesInstallment())->forceFill([
                    'id' => 1000000002,
                    'name' => '2nd Half',
                    'due_date' => '2026-11-30',
                    'due_charges_type' => 'fixed',
                    'due_charges' => 0,
                    'amount' => round($remainingAmountToDivide - $part, 2),
                    'fees_id' => $this->id,
                ]);
            } elseif ($payment_package === 'quarterly') {
                $part = round($remainingAmountToDivide / 3, 2);
                $customInstallments[] = (new \App\Models\FeesInstallment())->forceFill([
                    'id' => 1000000001,
                    'name' => '1st Quarter',
                    'due_date' => '2026-06-30',
                    'due_charges_type' => 'fixed',
                    'due_charges' => 0,
                    'amount' => $part,
                    'fees_id' => $this->id,
                ]);
                $customInstallments[] = (new \App\Models\FeesInstallment())->forceFill([
                    'id' => 1000000002,
                    'name' => '2nd Quarter',
                    'due_date' => '2026-09-15',
                    'due_charges_type' => 'fixed',
                    'due_charges' => 0,
                    'amount' => $part,
                    'fees_id' => $this->id,
                ]);
                $customInstallments[] = (new \App\Models\FeesInstallment())->forceFill([
                    'id' => 1000000003,
                    'name' => '3rd Quarter',
                    'due_date' => '2026-12-31',
                    'due_charges_type' => 'fixed',
                    'due_charges' => 0,
                    'amount' => round($remainingAmountToDivide - ($part * 2), 2),
                    'fees_id' => $this->id,
                ]);
            } elseif ($payment_package === 'monthly') {
                $part = round($remainingAmountToDivide / 11, 2);
                $months = [
                    '2026-07-05', '2026-08-05', '2026-09-05', '2026-10-05', '2026-11-05',
                    '2026-12-05', '2027-01-05', '2027-02-05', '2027-03-05', '2027-04-05',
                    '2027-05-05'
                ];
                for ($i = 0; $i < 11; $i++) {
                    $amt = ($i === 10) ? round($remainingAmountToDivide - ($part * 10), 2) : $part;
                    $customInstallments[] = (new \App\Models\FeesInstallment())->forceFill([
                        'id' => 1000000001 + $i,
                        'name' => 'Month ' . ($i + 1),
                        'due_date' => $months[$i],
                        'due_charges_type' => 'fixed',
                        'due_charges' => 0,
                        'amount' => $amt,
                        'fees_id' => $this->id,
                    ]);
                }
            }
            $this->setRelation('installments', collect($customInstallments));
        }
        
        return $this;
    }
}

