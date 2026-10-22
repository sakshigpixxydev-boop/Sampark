<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SchoolLead extends Model
{
    protected $table = 'school_leads';
    protected $primaryKey = 'lead_id';

    protected $fillable = [
        'initiator_id',
        'school_name',
        'email',
        'phone',
        'estimated_students',
        'address',
        'website',
        'registration_token',
        'token_expires_at',
        'token_used_at',
        'status',
        'school_id',
        'notes',
        'tc_content',
        'tc_agreed_at',
        'tc_expiry_date',
        'expiry_notification_sent_at',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'token_used_at' => 'datetime',
        'estimated_students' => 'integer',
        'tc_agreed_at' => 'datetime',
        'tc_expiry_date' => 'date',
        'expiry_notification_sent_at' => 'datetime',
    ];

    // Relationships
    public function initiator()
    {
        return $this->belongsTo(SysUser::class, 'initiator_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    // Methods

    /**
     * Generate a unique short token (8-12 characters, URL-safe)
     */
    public static function generateUniqueToken($length = 10)
    {
        do {
            // Generate URL-safe random string
            $token = Str::random($length);
            // Make it URL-safe by replacing special characters
            $token = str_replace(['+', '/', '='], ['', '', ''], base64_encode(random_bytes($length)));
            $token = substr($token, 0, $length);
        } while (self::where('registration_token', $token)->exists());

        return $token;
    }

    /**
     * Check if token is valid (not used, not expired)
     */
    public function isTokenValid()
    {
        if ($this->token_used_at !== null) {
            return false;
        }

        if ($this->token_expires_at && $this->token_expires_at->isPast()) {
            $this->update(['status' => 'expired']);
            return false;
        }

        return $this->status === 'pending';
    }

    /**
     * Mark token as used
     */
    public function markTokenAsUsed($schoolId)
    {
        $this->update([
            'token_used_at' => now(),
            'status' => 'registered',
            'school_id' => $schoolId,
        ]);
    }

    /**
     * Get registration URL
     */
    public function getRegistrationUrl()
    {
        return url('/register-school/' . $this->registration_token);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRegistered($query)
    {
        return $query->where('status', 'registered');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeValidTokens($query)
    {
        return $query->where('status', 'pending')
            ->whereNull('token_used_at')
            ->where(function ($q) {
                $q->whereNull('token_expires_at')
                    ->orWhere('token_expires_at', '>', now());
            });
    }

    // Boot method to set default token expiration
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lead) {
            if (!$lead->registration_token) {
                $lead->registration_token = self::generateUniqueToken();
            }

            if (!$lead->token_expires_at) {
                $lead->token_expires_at = now()->addDays(30);
            }

            // Snapshot the Terms & Conditions
            if (!$lead->tc_content) {
                $template = AgreementTemplate::getActiveBySlug('school_registration');
                if ($template) {
                    $lead->tc_content = $template->content;
                }
            }
        });
    }
}
