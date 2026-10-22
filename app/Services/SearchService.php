<?php

namespace App\Services;

use App\Models\Student;
use App\Models\School;
use App\Models\SysUser;
use Illuminate\Support\Facades\Auth;

class SearchService
{
    /**
     * Perform a global search across students, schools, and users
     *
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function globalSearch(string $query, int $limit = 10): array
    {
        $results = [
            'students' => [],
            'schools' => [],
            'users' => [],
        ];

        if (strlen($query) < 2) {
            return $results;
        }

        $user = Auth::user();
        $role = $user->role->rolename ?? '';

        // Search students
        $results['students'] = $this->searchStudents($query, $role, $user, $limit);

        // Search schools
        $results['schools'] = $this->searchSchools($query, $role, $user, $limit);

        // Search users (only for admin and initiator roles)
        if (in_array($role, ['Admin', 'Initiator'])) {
            $results['users'] = $this->searchUsers($query, $limit);
        }

        return $results;
    }

    /**
     * Search students based on role permissions
     */
    private function searchStudents(string $query, string $role, $user, int $limit): array
    {
        $studentsQuery = Student::query()
            ->with(['school', 'studentType', 'standard'])
            ->where(function ($q) use ($query) {
                $q->where('fname', 'LIKE', "%{$query}%")
                    ->orWhere('mname', 'LIKE', "%{$query}%")
                    ->orWhere('lname', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%")
                    ->orWhere('contact1', 'LIKE', "%{$query}%");
            });

        // Apply role-based filtering
        if ($role === 'School') {
            $studentsQuery->where('school_id', $user->school_id);
        } elseif ($role === 'Initiator') {
            $assignedStudentIds = $user->students()->pluck('student.student_id')->toArray();
            $studentsQuery->whereIn('student_id', $assignedStudentIds);
        }

        return $studentsQuery->limit($limit)->get()->map(function ($student) use ($query) {
            return [
                'id' => $student->student_id,
                'title' => $student->fname . ' ' . $student->mname . ' ' . $student->lname,
                'subtitle' => $student->email,
                'meta' => $student->school->schoolname ?? 'No School',
                'url' => route('students.edit', $student->student_id),
                'type' => 'student',
                'highlight' => $this->highlightMatch($student->fname . ' ' . $student->mname . ' ' . $student->lname, $query),
            ];
        })->toArray();
    }

    /**
     * Search schools based on role permissions
     */
    private function searchSchools(string $query, string $role, $user, int $limit): array
    {
        $schoolsQuery = School::query()
            ->with(['schoolType', 'educationalBoard'])
            ->where(function ($q) use ($query) {
                $q->where('schoolname', 'LIKE', "%{$query}%")
                    ->orWhere('adminname', 'LIKE', "%{$query}%")
                    ->orWhere('admincontact', 'LIKE', "%{$query}%");
            });

        // Apply role-based filtering
        if ($role === 'School') {
            $schoolsQuery->where('school_id', $user->school_id);
        } elseif ($role === 'Initiator') {
            $assignedSchoolIds = $user->schools()->pluck('school.school_id')->toArray();
            $schoolsQuery->whereIn('school_id', $assignedSchoolIds);
        }

        return $schoolsQuery->limit($limit)->get()->map(function ($school) use ($query) {
            return [
                'id' => $school->school_id,
                'title' => $school->schoolname,
                'subtitle' => $school->adminname . ' - ' . $school->admincontact,
                'meta' => $school->schoolType->schooltype ?? 'Unknown Type',
                'url' => route('schools.edit', $school->school_id),
                'type' => 'school',
                'highlight' => $this->highlightMatch($school->schoolname, $query),
            ];
        })->toArray();
    }

    /**
     * Search users
     */
    private function searchUsers(string $query, int $limit): array
    {
        return SysUser::query()
            ->with('role')
            ->where(function ($q) use ($query) {
                $q->where('fname', 'LIKE', "%{$query}%")
                    ->orWhere('lname', 'LIKE', "%{$query}%")
                    ->orWhere('username', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->limit($limit)
            ->get()
            ->map(function ($user) use ($query) {
                return [
                    'id' => $user->sysuser_id,
                    'title' => $user->fname . ' ' . ($user->lname ?? ''),
                    'subtitle' => $user->username,
                    'meta' => $user->role->rolename ?? 'No Role',
                    'url' => route('users.edit', $user->sysuser_id),
                    'type' => 'user',
                    'highlight' => $this->highlightMatch($user->fname . ' ' . ($user->lname ?? ''), $query),
                ];
            })->toArray();
    }

    /**
     * Highlight matching text
     */
    private function highlightMatch(string $text, string $query): string
    {
        return preg_replace('/(' . preg_quote($query, '/') . ')/i', '<mark class="bg-yellow-200">$1</mark>', $text);
    }
}
