<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Traits\Auditable;

class School extends Model
{
    use Auditable;

    protected $table = 'school';
    protected $primaryKey = 'school_id';
    public $timestamps = false;

    protected $fillable = [
        'lead_id',
        'initiator_id',
        'schooltype_id',
        'eduboard_id',
        'schoolname',
        'logourl',
        'address1',
        'address2',
        'city_village',
        'takula_id',
        'pincode',
        'phone',
        'mobile',
        'email',
        'website',
        'geolocation',
        'referral_link',
        'establishedyear',
        'adminname',
        'admincontact',
        'auth_role',
        'auth_role_other',
        'auth_name',
        'auth_email',
        'auth_phone',
        'auth2_role',
        'auth2_role_other',
        'auth2_name',
        'auth2_email',
        'auth2_phone',
        'inactive',
        'status',
        'reg_token',
        'reg_form_name',
        'reg_start_date',
        'reg_end_date',
        'notes',
        'createdby',
        'created_at',
        'updated_at',
        'agreement_pdf_path',
        'agreement_status',
        'custom_fees',
        'services_features',
        'offline_payment_name',
        'offline_payment_contact',
        'qr_code_path',
    ];

    protected $casts = [
        'inactive' => 'boolean',
        'status' => 'integer',
        'reg_start_date' => 'datetime',
        'reg_end_date' => 'datetime',
        'created_at' => 'date',
        'updated_at' => 'date',
        'custom_fees' => 'array',
        'services_features' => 'array',
    ];

    // Relationships
    public function schoolType()
    {
        return $this->belongsTo(SchoolType::class, 'schooltype_id');
    }

    public function educationalBoard()
    {
        return $this->belongsTo(EducationalBoard::class, 'eduboard_id');
    }

    public function taluka()
    {
        return $this->belongsTo(Taluko::class, 'takula_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'school_id');
    }

    public function users()
    {
        return $this->belongsToMany(SysUser::class, 'userschool', 'school_id', 'sysuser_id');
    }

    public function feePlans()
    {
        return $this->hasMany(FeePlan::class, 'school_id');
    }

    public function discountRules()
    {
        return $this->hasMany(DiscountRule::class, 'school_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'school_id');
    }

    public function lead()
    {
        return $this->belongsTo(SchoolLead::class, 'lead_id');
    }

    public function initiator()
    {
        return $this->belongsTo(SysUser::class, 'initiator_id');
    }

    public function uploadBatches()
    {
        return $this->hasMany(UploadBatch::class, 'school_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('inactive', false);
    }

    public function scopeFinal($query)
    {
        return $query->where('status', 1);
    }

    public function scopeLead($query)
    {
        return $query->where('status', 0);
    }

    // Agreement Helpers
    public function isAgreementReadOnly()
    {
        return $this->agreement_status === 'coordinator_approved';
    }
}
