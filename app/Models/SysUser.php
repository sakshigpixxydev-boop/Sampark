<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\InitiatorFinancialConfig;
use App\Models\AgreementTemplate;
use App\Models\Blog;
use App\Models\Event;
use App\Models\StudentAnnouncement;
use App\Models\UserRead;
use App\Models\Student;
use App\Models\School;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Traits\Auditable;

class SysUser extends Authenticatable
{
    use Notifiable, Auditable;
    protected $table = 'sysuser';
    protected $primaryKey = 'sysuser_id';
    public $timestamps = false;

    // Disable remember token since table doesn't have this column
    public $rememberTokenName = null;

    protected $fillable = [
        'role_id',
        'fname',
        'lname',
        'mname',
        'email',
        'phone',
        'address1',
        'address2',
        'city',
        'pincode',
        'whatsapp',
        'pswd',
        'pin',
        'inactive',
        'username',
        'refcode',
        'createdby',
        'created_at',
        'updated_at',
        'tc_content',
        'tc_agreed_at',
        'tc_expiry_date',
        'expiry_notification_sent_at',
        'financial_type',
        'signature_path',
    ];

    protected $hidden = [
        'pswd',
        'pin',
    ];

    protected $casts = [
        'inactive' => 'boolean',
        'created_at' => 'date',
        'updated_at' => 'date',
        'tc_agreed_at' => 'datetime',
        'tc_expiry_date' => 'date',
        'expiry_notification_sent_at' => 'datetime',
    ];

    // Accessors for backward compatibility
    public function getFirstnameAttribute()
    {
        return $this->fname;
    }

    public function getLastnameAttribute()
    {
        return $this->lname;
    }

    // Override auth password column
    public function getAuthPassword()
    {
        return $this->pswd;
    }

    // Method to check PIN
    public function checkPin($pin)
    {
        return Hash::check($pin, $this->pin);
    }

    // Mutators
    public function setPswdAttribute($value)
    {
        if ($value) {
            $this->attributes['pswd'] = Hash::make($value);
        }
    }

    public function setPinAttribute($value)
    {
        if ($value) {
            $this->attributes['pin'] = Hash::make($value);
        }
    }

    // Relationships
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function schools()
    {
        return $this->belongsToMany(School::class, 'userschool', 'sysuser_id', 'school_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'userstudent', 'sysuser_id', 'student_id');
    }

    public function financialConfigs()
    {
        return $this->belongsToMany(InitiatorFinancialConfig::class, 'initiator_financial_config_user', 'sysuser_id', 'config_id');
    }

    public function schoolFeePlans()
    {
        return $this->belongsToMany(FeePlan::class, 'fee_plan_user', 'sysuser_id', 'fee_plan_id');
    }

    public function getFinancialConfigAttribute()
    {
        return $this->financialConfigs()->first();
    }

    public function initiatorPayments()
    {
        return $this->hasMany(InitiatorPayment::class, 'initiator_id', 'sysuser_id');
    }

    public function isPaidInitiator()
    {
        return $this->hasRole('Initiator') && $this->financial_type === 'paid';
    }

    public function isVolunteerInitiator()
    {
        return $this->hasRole('Initiator') && $this->financial_type === 'volunteer';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('inactive', false);
    }

    // Permission checking
    public function hasPermission($permkey)
    {
        if (!$this->role) {
            return false;
        }
        return $this->role->permissions()->where('permkey', $permkey)->exists();
    }

    public function hasAnyPermission($permkeys)
    {
        if (!$this->role) {
            return false;
        }
        return $this->role->permissions()->whereIn('permkey', $permkeys)->exists();
    }

    public function canAccessModule($modulePermKey)
    {
        if (!$this->role) {
            return false;
        }
        return $this->role->permissions()->where('permkey', $modulePermKey)->exists();
    }

    public function scopeInactive($query)
    {
        return $query->where('inactive', true);
    }

    public function hasRole($roleName)
    {
        if (!$this->role) {
            return false;
        }
        if (is_array($roleName)) {
            foreach ($roleName as $name) {
                if ($this->hasRole($name)) {
                    return true;
                }
            }
            return false;
        }
        if ($roleName === 'Educator') {
            return $this->role->rolename === 'Educator' || str_starts_with($this->role->rolename, 'Educator - ');
        }
        return $this->role->rolename === $roleName;
    }

    /**
     * Full run of the platform. Note the app seeds no 'Superadmin' role today;
     * it is kept here so an install that has one is covered.
     */
    public function isMasterAdmin(): bool
    {
        return $this->hasRole(['Superadmin', 'Admin']);
    }

    /**
     * May run the payment-side registration actions — Razorpay sync, payment
     * reminders, bulk sync and export.
     *
     * School and Educator roles are deliberately excluded: on a registration
     * they approve or reject, nothing else.
     */
    public function canRunRegistrationOps(): bool
    {
        return $this->hasRole(['Superadmin', 'Admin', 'Accountant']);
    }

    public function getAssignedSchoolIds()
    {
        $pivotSchoolIds = $this->schools()->pluck('school.school_id')->toArray();
        $initiatedSchoolIds = School::where('initiator_id', $this->sysuser_id)->pluck('school_id')->toArray();
        return array_unique(array_merge($pivotSchoolIds, $initiatedSchoolIds));
    }

    /**
     * School ids a school-side user is confined to, or null when the user is
     * not confined to particular schools (Admin, Accountant, ...).
     *
     * Returns [0] — a school id that cannot exist — when such a user has no
     * school assigned, so callers filtering on it get an empty result rather
     * than the whole table.
     */
    public function restrictedSchoolIds(): ?array
    {
        if (!$this->hasRole('School') && !$this->hasRole('Educator') && !$this->hasRole('Initiator')) {
            return null;
        }

        $schoolIds = $this->getAssignedSchoolIds();

        return empty($schoolIds) ? [0] : array_values($schoolIds);
    }

    public function getAssignedSchool()
    {
        $schoolIds = $this->getAssignedSchoolIds();
        return !empty($schoolIds) ? School::find($schoolIds[0]) : null;
    }

    public function getAssignedStudentIds()
    {
        $pivotStudentIds = $this->students()->pluck('student.student_id')->toArray();
        $schoolIds = $this->getAssignedSchoolIds();
        $schoolStudentIds = Student::whereIn('school_id', $schoolIds)->pluck('student_id')->toArray();
        return array_unique(array_merge($pivotStudentIds, $schoolStudentIds));
    }

    public function unreadBlogCount()
    {
        $query = Blog::query()->whereNotExists(function ($q) {
            $q->select(DB::raw(1))
                ->from('user_reads')
                ->whereColumn('user_reads.readable_id', 'blogs.id')
                ->where('user_reads.readable_type', 'Blog')
                ->where('user_reads.sysuser_id', $this->sysuser_id);
        });

        if ($this->hasPermission(10703)) { // Can Review (Admin/Initiator)
            if ($this->hasRole('Admin') || (!$this->hasRole('School') && !$this->hasRole('Initiator'))) {
                 $query->where('status', 'Pending');
            } else {
                $studentIds = $this->getAssignedStudentIds();
                $query->whereIn('student_id', $studentIds)
                           ->where('status', 'Pending');
            }
            return $query->count();
        }

        if (!$this->hasPermission(10701)) return 0;

        if ($this->hasPermission(10702)) { // Student
            $student = $this->students()->first();
            if (!$student) {
                // Fallback to identify student similarly to BlogController
                $student = Student::where('email', $this->email)->first() 
                        ?? Student::where('lmsuser', $this->username)->first()
                        ?? Student::where('fname', 'like', $this->username . '%')->first();
            }
            
            if ($student) {
                $query->where('student_id', $student->student_id)
                      ->whereIn('status', ['Approved', 'Rejected']);
            } else {
                return 0;
            }
        } else {
            $query->where('status', 'Approved');
        }

        return $query->count();
    }

    public function unreadEventCount()
    {
        if (!$this->hasPermission(10401)) return 0;

        $query = Event::query()->whereNotExists(function ($q) {
            $q->select(DB::raw(1))
                ->from('user_reads')
                ->whereColumn('user_reads.readable_id', 'event.event_id')
                ->where('user_reads.readable_type', 'Event')
                ->where('user_reads.sysuser_id', $this->sysuser_id);
        });

        if ($this->hasRole('School') || $this->hasRole('Initiator') || $this->hasRole('Student')) {
            $user = $this;
            $query->where(function($q) use ($user) {
                // 1. Events for assigned schools
                $schoolIds = $user->getAssignedSchoolIds();
                if (!empty($schoolIds)) {
                    $q->whereIn('school_id', $schoolIds);
                }

                // 2. Area-wise events
                if ($user->hasRole('Student')) {
                    $student = Student::where('email', $user->email)->first() 
                            ?? Student::where('lmsuser', $user->username)->first();
                    if ($student) {
                        $q->orWhere(function($subq) use ($student) {
                            $subq->where('state_id', $student->state_id);
                            if ($student->dist_id) $subq->where(function($dq) use ($student) { $dq->whereNull('dist_id')->orWhere('dist_id', $student->dist_id); });
                            if ($student->takula_id) $subq->where(function($tq) use ($student) { $tq->whereNull('taluka_id')->orWhere('taluka_id', $student->takula_id); });
                        });
                    }
                }
                
                $assignedSchools = School::whereIn('school_id', $schoolIds)->get();
                if ($assignedSchools->isNotEmpty()) {
                    $talukaIds = $assignedSchools->pluck('takula_id')->filter()->unique();
                    if ($talukaIds->isNotEmpty()) {
                        $q->orWhereIn('taluka_id', $talukaIds);
                        
                        $distIds = Taluko::whereIn('takula_id', $talukaIds)->pluck('dist_id')->unique();
                        if ($distIds->isNotEmpty()) {
                            $q->orWhereIn('dist_id', $distIds);
                            
                            $stateIds = District::whereIn('dist_id', $distIds)->pluck('state_id')->unique();
                            if ($stateIds->isNotEmpty()) {
                                $q->orWhereIn('state_id', $stateIds);
                            }
                        }
                    }
                }
            });
        }

        return $query->count();
    }

    public function unreadAnnouncementCount()
    {
        if (!$this->hasPermission(10222)) return 0;

        $query = StudentAnnouncement::query()->whereNotExists(function ($q) {
            $q->select(DB::raw(1))
                ->from('user_reads')
                ->whereColumn('user_reads.readable_id', 'student_announcements.id')
                ->where('user_reads.readable_type', 'Announcement')
                ->where('user_reads.sysuser_id', $this->sysuser_id);
        });

        // This is tricky because announcements are sent via queue.
        // For simplified version, let's just count all announcements that are relevant.
        // If it's a student, check their specific queue.
        if ($this->hasRole('Student')) {
            $studentIds = $this->getAssignedStudentIds();
            $query->whereIn('id', function($q) use ($studentIds) {
                $q->select('announcement_id')
                  ->from('student_announcement_queue')
                  ->whereIn('student_id', $studentIds);
            });
        }

        return $query->count();
    }

    public function unreadTicketCount()
    {
        if (!$this->hasPermission(10801)) return 0;

        $query = Ticket::query()->whereNotExists(function ($q) {
            $q->select(DB::raw(1))
                ->from('user_reads')
                ->whereColumn('user_reads.readable_id', 'tickets.ticket_id')
                ->where('user_reads.readable_type', 'Ticket')
                ->where('user_reads.sysuser_id', $this->sysuser_id);
        });
        
        // Filter based on access (same logic as TicketController)
        if ($this->hasRole('School')) {
            $schoolIds = $this->getAssignedSchoolIds();
            $query->whereIn('school_id', $schoolIds);
        } elseif ($this->hasRole('Initiator')) {
            $assignedSchoolIds = $this->getAssignedSchoolIds();
            $query->whereIn('school_id', $assignedSchoolIds);
        }
        // Admin sees all

        return $query->forUser($this)->count();
    }

    public function unreadTotalCount()
    {
        return $this->unreadBlogCount() + 
               $this->unreadEventCount() + 
               $this->unreadAnnouncementCount() + 
               $this->unreadTicketCount();
    }

    public function scopeByRole($query, $roleId)
    {
        return $query->where('role_id', $roleId);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Check if this role requires T&C
            $initiatorRole = Role::where('rolename', 'Initiator')->first();
            if ($initiatorRole && $user->role_id == $initiatorRole->role_id) { 
                $template = AgreementTemplate::getActiveBySlug('initiator_agreement');
                if ($template) {
                    $user->tc_content = $template->content;
                }
            }
        });
    }
}
