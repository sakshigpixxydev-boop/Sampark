<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\SysUser;
use App\Models\UserRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\BlogSubmittedNotification;

class BlogController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasPermission(10702) && !$user->hasPermission(10703)) { // Student
            $student = $this->getAuthenticatedStudent();
            $studentId = $student ? $student->student_id : null;
            $blogs = Blog::where('student_id', $studentId)->orderBy('created_at', 'desc')->paginate(10);
        } elseif ($user->hasPermission(10703)) { // Initiator or Admin
            if ($user->hasRole('Admin') || (!$user->hasRole('School') && !$user->hasRole('Initiator'))) {
                 $blogs = Blog::orderBy('created_at', 'desc')->paginate(10);
            } else {
                $studentIds = $user->getAssignedStudentIds();
                $blogs = Blog::whereIn('student_id', $studentIds)
                            ->whereIn('status', ['Pending', 'Approved', 'Rejected'])
                            ->orderBy('created_at', 'desc')->paginate(10);
            }
        } else {
            $blogs = Blog::where('id', 0)->paginate(10); // Empty
        }

        return view('blogs.index', compact('blogs'));
    }

    public function create()
    {
        if (!Auth::user()->hasPermission(10702)) {
            return redirect()->route('blogs.index')->with('error', 'Unauthorized access.');
        }

        return view('blogs.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission(10702)) {
            return redirect()->route('blogs.index')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $student = $this->getAuthenticatedStudent();
        if (!$student) {
            return redirect()->route('blogs.index')->with('error', 'Student record not found.');
        }

        $blog = Blog::create([
            'student_id' => $student->student_id,
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'status' => 'Pending',
        ]);

        // Notify assigned initiator and all admins
        $school = $student->school;
        $usersToNotify = collect();

        if ($school && $school->initiator_id) {
            $initiator = SysUser::where('sysuser_id', $school->initiator_id)->active()->first();
            if ($initiator) {
                $usersToNotify->push($initiator);
            }
        }

        // Also notify all active admins
        $admins = SysUser::whereHas('role', function($q) {
            $q->where('rolename', 'Admin');
        })->active()->get();
        
        foreach ($admins as $admin) {
            $usersToNotify->push($admin);
        }

        // Remove duplicates if initiator is also an admin
        $usersToNotify = $usersToNotify->unique('sysuser_id');

        foreach ($usersToNotify as $user) {
            $user->notify(new BlogSubmittedNotification($blog));
        }

        return redirect()->route('blogs.index')->with('success', 'Blog submitted for approval.');
    }

    public function show(Blog $blog)
    {
        $this->authorizeAccess($blog);
        
        // Mark as read
        UserRead::firstOrCreate([
            'sysuser_id' => Auth::id(),
            'readable_id' => $blog->id,
            'readable_type' => 'Blog',
        ]);

        return view('blogs.show', compact('blog'));
    }

    public function edit(Blog $blog)
    {
        if (!Auth::user()->hasPermission(10702)) {
            return redirect()->route('blogs.index')->with('error', 'Unauthorized access.');
        }

        $student = $this->getAuthenticatedStudent();
        $studentId = $student ? $student->student_id : null;
        if ($blog->student_id !== $studentId) {
            return redirect()->route('blogs.index')->with('error', 'Unauthorized access.');
        }

        if ($blog->status === 'Approved') {
            return redirect()->route('blogs.index')->with('error', 'Cannot edit approved blogs.');
        }

        return view('blogs.edit', compact('blog'));
    }

    public function update(Request $request, Blog $blog)
    {
        if (!Auth::user()->hasPermission(10702)) {
            return redirect()->route('blogs.index')->with('error', 'Unauthorized access.');
        }

        $student = $this->getAuthenticatedStudent();
        $studentId = $student ? $student->student_id : null;
        if ($blog->student_id !== $studentId) {
            return redirect()->route('blogs.index')->with('error', 'Unauthorized access.');
        }

        if ($blog->status === 'Approved') {
            return redirect()->route('blogs.index')->with('error', 'Cannot edit approved blogs.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $blog->update([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'status' => 'Pending',
        ]);

        return redirect()->route('blogs.index')->with('success', 'Blog updated and is pending approval.');
    }

    public function review(Blog $blog)
    {
        if (!Auth::user()->hasPermission(10703)) {
            return redirect()->route('blogs.index')->with('error', 'Unauthorized.');
        }

        return view('blogs.review', compact('blog'));
    }

    public function processReview(Request $request, Blog $blog)
    {
        if (!Auth::user()->hasPermission(10703)) {
            return redirect()->route('blogs.index')->with('error', 'Unauthorized.');
        }

        $request->validate([
            'status' => 'required|in:Approved,Rejected',
            'review_comment' => 'nullable|string',
        ]);

        $blog->update([
            'status' => $request->status,
            'review_comment' => $request->review_comment,
            'reviewer_id' => Auth::id(),
        ]);

        return redirect()->route('blogs.index')->with('success', 'Blog ' . strtolower($request->status) . ' successfully.');
    }

    private function authorizeAccess(Blog $blog)
    {
        $user = Auth::user();
        if ($user->hasPermission(10703)) {
            if ($user->hasRole('Admin') || (!$user->hasRole('School') && !$user->hasRole('Initiator'))) return;
            $studentIds = $user->getAssignedStudentIds();
            if (!in_array($blog->student_id, $studentIds)) {
                abort(403, 'Unauthorized');
            }
        } else {
            $student = $this->getAuthenticatedStudent();
            $studentId = $student ? $student->student_id : null;
            if ($blog->student_id !== $studentId) {
                abort(403, 'Unauthorized');
            }
        }
    }

    /**
     * Helper to get the student record associated with the authenticated user.
     */
    private function getAuthenticatedStudent()
    {
        $user = Auth::user();
        if (!$user) return null;

        // Try standard pivot relationship first
        $student = $user->students()->first();

        // Fallback for students where pivot record is missing
        if (!$student && $user->hasPermission(10702)) {
            // Try matching by email
            $student = \App\Models\Student::where('email', $user->email)->first();

            // Try matching by username (often matches lmsuser)
            if (!$student) {
                $student = \App\Models\Student::where('lmsuser', $user->username)->first();
            }

            // Fuzzy match: Try matching if username is part of fname (e.g. 'student' matches 'Student1')
            if (!$student) {
                $student = \App\Models\Student::where('fname', 'like', $user->username . '%')->first();
            }

            // Fuzzy match: Try matching by fname/lname if they match partly
            if (!$student) {
                $student = \App\Models\Student::where('fname', 'like', $user->fname . '%')
                    ->where('lname', 'like', $user->lname . '%')
                    ->first();
            }
        }

        return $student;
    }
}
