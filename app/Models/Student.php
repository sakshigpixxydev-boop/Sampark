<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Traits\Auditable;

class Student extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'student';
    protected $primaryKey = 'student_id';
    public $timestamps = true;
    
    protected $dates = ['deleted_at'];

    public static function getLanguagesMap()
    {
        return [
            'Gujarati' => 'Gujarati',
            'Hindi' => 'Hindi',
            'English' => 'English'
        ];
    }

    public static function getEducationMediumMap()
    {
        return [
            '1' => 'Gujarati',
            '2' => 'English',
            '3' => 'Hindi'
        ];
    }

    public static function getLearnAboutMap()
    {
        return [
            '1' => 'School',
            '2' => 'Satsang Mandal / Karyakar',
            '3' => 'Social Media',
            '4' => 'Friends / Relative',
            '5' => 'Other'
        ];
    }
    
    protected $fillable = [
        'portal_id',
        'deleted_reason',
        'studenttype_id',
        'satsangmandal_id',
        'shikharm_id',
        'kshetra_id',
        'eduboard_id',
        'other_eduboard_name',
        'std_id',
        'stream',
        'school_id',
        'state_id',
        'dist_id',
        'takula_id',
        'fname',
        'mname',
        'lname',
        'gender',
        'email',
        'birthdate',
        'address',
        'city_village',
        'pincode',
        'contact_no',
        'whatsapp_no',
        'educationmedium',
        'institute',
        'achievement_stsang',
        'achievement_academic',
        'activities',
        'learnabout',
        'materiallanguage',
        'taluka_id_postal',
        'city_postal',
        'address_postal',
        'notes',
        'refby',
        'createdby',
        'lmsuser',
        'lmspswd',
        'school_educator_name',
        'school_educator_no',
        'geo_tagging_school_address',
        'initiator_contact',
        'application_status',
        'fees_status',
        'enrolment_no',
        'school_name',
        'school_address',
        'subscription',
        'razorpay_order_id',
        'razorpay_payment_id',
        'country_id',
        'satsang_karyakar_name',
        'satsang_karyakar_contact',
        'registered_via_school_link',
        'school_approval_status',
        'is_lead',
        'lead_captured_at',
        'lead_source',
        'lead_payment_link_sent_at',
        'lead_converted_at',
        'batch_id',
        'selected_language',
        'school_payment_method',
        'school_payment_plan_name',
        'school_payment_amount',
        'school_payment_screenshot',
        'school_payment_upi_id',
        'school_payment_status',
        'school_payment_verified_by',
        'school_payment_verified_at',
        'school_payment_rejection_reason',
    ];
    
    protected $casts = [
        'birthdate' => 'date',
        'registered_via_school_link' => 'boolean',
        'school_payment_verified_at' => 'datetime',
        'school_payment_amount' => 'decimal:2',
        'is_lead' => 'boolean',
        'lead_captured_at' => 'datetime',
        'lead_payment_link_sent_at' => 'datetime',
        'lead_converted_at' => 'datetime',
    ];

    /**
     * Name of the global scope that hides Student Lead rows.
     */
    public const SCOPE_EXCLUDE_LEADS = 'excludeLeads';

    /**
     * Name of the global scope behind the header's All / School / Regular
     * dropdown.
     */
    public const SCOPE_VIEW_COHORT = 'viewCohort';

    /**
     * Student Leads live in this same table but must never appear in the
     * student, registration, dashboard, search or export listings. A global
     * scope is used rather than editing every query because `Student::` is
     * referenced in well over a hundred places.
     *
     * Note: this does not interfere with writes. Eloquent uses
     * newQueryWithoutScopes() when persisting a loaded model, so
     * $lead->update([...]) still works.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(self::SCOPE_EXCLUDE_LEADS, function ($query) {
            // Table-qualified so the scope survives joins.
            $query->where('student.is_lead', 0);
        });

        // The header dropdown narrows every page at once. ViewCohort returns
        // null unless a signed-in user who may use the switch has picked a
        // cohort, so the public flow, jobs and console stay unfiltered.
        static::addGlobalScope(self::SCOPE_VIEW_COHORT, function ($query) {
            \App\Support\ViewCohort::apply($query);
        });
    }

    /**
     * Ignore the header's cohort selection for this query.
     */
    public function scopeAllCohorts($query)
    {
        return $query->withoutGlobalScope(self::SCOPE_VIEW_COHORT);
    }

    /**
     * Only Student Lead rows.
     */
    public function scopeOnlyLeads($query)
    {
        return $query->withoutGlobalScope(self::SCOPE_EXCLUDE_LEADS)
                     ->where('student.is_lead', 1);
    }

    /**
     * Students and leads together.
     */
    public function scopeWithLeads($query)
    {
        return $query->withoutGlobalScope(self::SCOPE_EXCLUDE_LEADS);
    }

    // Accessors for backward compatibility
    public function getFirstnameAttribute()
    {
        return $this->fname;
    }
    
    public function getMiddlenameAttribute()
    {
        return $this->mname;
    }
    
    public function getLastnameAttribute()
    {
        return $this->lname;
    }
    
    // Relationships
    public function document()
    {
        return $this->hasOne(Document::class, 'student_id', 'student_id');
    }

    public function studentType()
    {
        return $this->belongsTo(StudentType::class, 'studenttype_id');
    }
    
    public function educationalBoard()
    {
        return $this->belongsTo(EducationalBoard::class, 'eduboard_id');
    }
    
    public function standard()
    {
        return $this->belongsTo(Standard::class, 'std_id');
    }
    
    public function uploadBatch()
    {
        return $this->belongsTo(UploadBatch::class, 'batch_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
    
    public function taluka()
    {
        return $this->belongsTo(Taluko::class, 'takula_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'dist_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
    
    public function guardians()
    {
        return $this->hasMany(Guardian::class, 'student_id');
    }
    
    public function semesterBatches()
    {
        return $this->belongsToMany(SemesterBatch::class, 'student_sembatches', 'student_id', 'sembatches_id')
                    ->withPivot('status', 'assigned_at', 'completed_at');
    }
    
    public function histories()
    {
        return $this->hasMany(StudentHistory::class, 'student_id', 'student_id');
    }
    
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'student_id');
    }
    
    public function parcels()
    {
        return $this->hasMany(Parcel::class, 'student_id');
    }
    
    public function discountRules()
    {
        return $this->hasMany(DiscountRule::class, 'student_id');
    }
    
    public function users()
    {
        return $this->belongsToMany(SysUser::class, 'userstudent', 'student_id', 'sysuser_id');
    }
    
    public function creator()
    {
        return $this->belongsTo(SysUser::class, 'createdby', 'sysuser_id');
    }
    
    public function calculateFees($semesterBatchId)
    {
        // Logic to calculate fees based on batch and fee plans
        $feePlans = FeePlan::where(function($q) use ($semesterBatchId) {
                $q->where('sembatches_id', $semesterBatchId)
                  ->orWhereHas('semesterBatches', function ($q2) use ($semesterBatchId) {
                      $q2->where('fee_plan_sembatches.sembatches_id', $semesterBatchId);
                  });
            })
            ->where(function($query) {
                $query->where('school_id', $this->school_id)
                      ->orWhereNull('school_id');
            })
            ->get();
            
        return $feePlans->sum('amount');
    }
    
    public function applyDiscounts($subtotal)
    {
        // Apply school-level discounts first
        $schoolDiscounts = DiscountRule::where('school_id', $this->school_id)
            ->where('active', true)
            ->where('valid_from', '<=', now())
            ->where(function($query) {
                $query->whereNull('valid_to')
                      ->orWhere('valid_to', '>=', now());
            })
            ->get();
        
        // Then apply student-level discounts
        $studentDiscounts = DiscountRule::where('student_id', $this->student_id)
            ->where('active', true)
            ->where('valid_from', '<=', now())
            ->where(function($query) {
                $query->whereNull('valid_to')
                      ->orWhere('valid_to', '>=', now());
            })
            ->get();
        
        $totalDiscount = 0;
        
        foreach ($schoolDiscounts->merge($studentDiscounts) as $discount) {
            if ($discount->discount_type === 'FLAT') {
                $totalDiscount += $discount->discount_value;
            } else { // PERCENT
                $totalDiscount += ($subtotal * $discount->discount_value / 100);
            }
        }
        
        return $totalDiscount;
    }
    
    /**
     * Generate a new unique enrolment number in the format SNK-{month}{year}-{4 digit sequence number}
     *
     * @param int|null $studentId Optional student ID if already created
     * @param int $offset Optional offset to add to the sequence (useful for bulk generation)
     * @return string
     */
    public static function generateNextEnrolmentNumber($studentId = null, $offset = 0)
    {
        $monthYear = strtoupper(date('My')); // e.g. JAN26
        $prefix = "SNK-{$monthYear}-";
        
        if ($studentId) {
            $sequence = $studentId;
        } else {
            $sequence = (int)self::max('student_id') + 1 + $offset;
        }
        
        $paddedSequence = str_pad($sequence, 4, '0', STR_PAD_LEFT);
        return "{$prefix}{$paddedSequence}";
    }

    /**
     * Synchronize a student's fees_status based on their invoices and payments.
     *
     * @param int $studentId
     * @return void
     */
    public static function syncFeesStatus($studentId)
    {
        $student = self::with('invoices.payments')->find($studentId);
        if (!$student) {
            return;
        }

        $totalDue = $student->invoices->where('status', '!=', 'VOID')->sum('total_due');
        $totalPaid = $student->invoices->where('status', '!=', 'VOID')->sum(function($inv) {
            return $inv->payments->where('status', '1')->sum('amount');
        });

        if ($totalDue <= 0) {
            $student->fees_status = 'Paid';
        } elseif ($totalPaid >= $totalDue) {
            $student->fees_status = 'Paid';
        } elseif ($totalPaid > 0) {
            $student->fees_status = 'Partially Paid';
        } else {
            $student->fees_status = 'Unpaid';
        }

        $student->save();
    }
}

