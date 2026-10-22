<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\DateHelper;

/**
 * Listing of the two school registration channels: students the school uploaded
 * in a bulk import, and students who registered themselves through the school's
 * registration link. The summary counts live on the admin dashboard.
 *
 * Everything here is scoped through SysUser::restrictedSchoolIds(), so a school
 * user sees their own school and an admin sees every school.
 */
class SchoolRegistrationOverviewController extends Controller
{
    /** A student registered through the school's public link. */
    private const SOURCE_LINK = 'link';

    /** A student the school uploaded as part of a bulk import batch. */
    private const SOURCE_BULK = 'bulk';

    public function index(Request $request)
    {
        $query = $this->registrationQuery()->with(['school', 'standard', 'uploadBatch']);

        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $query->where(function ($q) use ($term) {
                $q->where('fname', 'like', $term)
                    ->orWhere('mname', 'like', $term)
                    ->orWhere('lname', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('contact_no', 'like', $term)
                    ->orWhere('enrolment_no', 'like', $term);
            });
        }

        if ($request->source === self::SOURCE_LINK) {
            $query->where('registered_via_school_link', true);
        } elseif ($request->source === self::SOURCE_BULK) {
            $query->where('registered_via_school_link', false)->whereNotNull('batch_id');
        }

        // Only link registrations carry a school payment; filtering on it
        // narrows the list to that channel by definition.
        if ($request->filled('payment_status')) {
            $query->where('registered_via_school_link', true)
                ->where('school_payment_status', $request->payment_status);
        }

        if ($request->filled('approval_status')) {
            $query->where('school_approval_status', $request->approval_status);
        }

        // The school filter is only offered to users who can see every school.
        if ($request->filled('school_id') && Auth::user()->restrictedSchoolIds() === null) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('date_from')) {
            $dateFrom = DateHelper::convertDateFormat($request->date_from);
            if ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }
        }

        if ($request->filled('date_to')) {
            $dateTo = DateHelper::convertDateFormat($request->date_to);
            if ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            }
        }

        $registrations = $query->orderByDesc('student_id')->paginate(20)->withQueryString();

        $schools = Auth::user()->restrictedSchoolIds() === null
            ? School::active()->final()->orderBy('schoolname')->get()
            : collect();

        return view('school.registrations.index', compact('registrations', 'schools'));
    }

    /**
     * Students who arrived through either school registration channel, limited
     * to the schools the current user may see.
     */
    private function registrationQuery(): Builder
    {
        $schoolIds = Auth::user()->restrictedSchoolIds();

        // This listing is the school channel by definition, so it ignores the
        // header's cohort switch rather than rendering empty under "Regular".
        return Student::query()
            ->withoutGlobalScope(Student::SCOPE_VIEW_COHORT)
            ->where(function ($q) {
                $q->where('registered_via_school_link', true)
                    ->orWhereNotNull('batch_id');
            })
            ->when($schoolIds !== null, fn ($q) => $q->whereIn('school_id', $schoolIds));
    }

}
