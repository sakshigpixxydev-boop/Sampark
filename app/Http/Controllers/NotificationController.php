<?php

namespace App\Http\Controllers;

use App\Models\UserRead;
use App\Models\Blog;
use App\Models\Event;
use App\Models\StudentAnnouncement;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class NotificationController extends Controller
{
    /**
     * Display a grid of all unread notifications.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasPermission(11101)) {
            abort(403);
        }

        $notificationsList = [];

        try {
            // 1. Blogs Unread
            if ($user->hasPermission(10701) || $user->hasPermission(10703)) {
                $blogQuery = Blog::query()->whereNotExists(function ($query) use ($user) {
                    $query->select(DB::raw(1))
                        ->from('user_reads')
                        ->whereColumn('user_reads.readable_id', 'blogs.id')
                        ->where('user_reads.readable_type', 'Blog')
                        ->where('user_reads.sysuser_id', $user->sysuser_id);
                });

                if ($user->hasPermission(10703)) { // Admin/Initiator Reviewer
                    if (!$user->hasRole('Admin')) {
                        $assignedStudentIds = $user->getAssignedStudentIds();
                        $blogQuery->whereIn('student_id', $assignedStudentIds)->where('status', 'Pending');
                    } else {
                        $blogQuery->where('status', 'Pending');
                    }
                } else { // Student/General (Read)
                    $blogQuery->where('status', 'Approved');
                    if ($user->hasRole('Student')) {
                        $studentIds = $user->getAssignedStudentIds();
                        $blogQuery->whereIn('student_id', $studentIds);
                    }
                }
                
                foreach ($blogQuery->get() as $blog) {
                    $notificationsList[] = [
                        'type' => 'Blog',
                        'title' => $blog->title,
                        'date' => $blog->created_at,
                        'link' => route('blogs.show', $blog->id),
                        'id' => $blog->id,
                    ];
                }
            }

            // 2. Events Unread
            if ($user->hasPermission(10401)) {
                $eventQuery = Event::query()->whereNotExists(function ($query) use ($user) {
                    $query->select(DB::raw(1))
                        ->from('user_reads')
                        ->whereColumn('user_reads.readable_id', 'event.event_id')
                        ->where('user_reads.readable_type', 'Event')
                        ->where('user_reads.sysuser_id', $user->sysuser_id);
                });

                if (!$user->hasRole('Admin')) {
                    $schoolIds = $user->getAssignedSchoolIds();
                    if (!empty($schoolIds)) {
                        $eventQuery->whereIn('school_id', $schoolIds);
                    }
                }
                
                foreach ($eventQuery->get() as $event) {
                    $notificationsList[] = [
                        'type' => 'Event',
                        'title' => $event->title,
                        'date' => $event->event_date,
                        'link' => route('events.show', $event->event_id),
                        'id' => $event->event_id,
                    ];
                }
            }

            // 3. Announcements Unread
            if ($user->hasPermission(10222)) {
                $annQuery = StudentAnnouncement::query()->whereNotExists(function ($query) use ($user) {
                    $query->select(DB::raw(1))
                        ->from('user_reads')
                        ->whereColumn('user_reads.readable_id', 'student_announcements.id')
                        ->where('user_reads.readable_type', 'Announcement')
                        ->where('user_reads.sysuser_id', $user->sysuser_id);
                });

                if ($user->hasRole('Student')) {
                    $studentIds = $user->getAssignedStudentIds();
                    $annQuery->whereIn('id', function($q) use ($studentIds) {
                        $q->select('announcement_id')
                        ->from('student_announcement_queue')
                        ->whereIn('student_id', $studentIds);
                    });
                }
                
                foreach ($annQuery->get() as $ann) {
                    $notificationsList[] = [
                        'type' => 'Announcement',
                        'title' => $ann->subject ?: 'New Announcement',
                        'date' => $ann->created_at,
                        'link' => route('students.announcements.show', $ann->id),
                        'id' => $ann->id,
                    ];
                }
            }

            // 4. Tickets Unread
            if ($user->hasPermission(10801)) {
                $ticketQuery = Ticket::query()->whereNotExists(function ($query) use ($user) {
                    $query->select(DB::raw(1))
                        ->from('user_reads')
                        ->whereColumn('user_reads.readable_id', 'tickets.ticket_id')
                        ->where('user_reads.readable_type', 'Ticket')
                        ->where('user_reads.sysuser_id', $user->sysuser_id);
                })->forUser($user);
                
                foreach ($ticketQuery->get() as $ticket) {
                    $notificationsList[] = [
                        'type' => 'Ticket',
                        'title' => $ticket->title,
                        'date' => $ticket->created_at,
                        'link' => route('tickets.show', $ticket->ticket_id),
                        'id' => $ticket->ticket_id,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to fetch notifications: " . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Unable to load notifications at this time.');
        }

        // Convert to collection and sort
        $allNotifications = collect($notificationsList)->sortByDesc(function($item) {
            return $item['date'];
        });

        // Handle "Mark All Read" action
        if ($request->has('mark_all')) {
            if (!$user->hasPermission(11103)) {
                abort(403);
            }
            
            try {
                $data = [];
                $now = now();
                foreach ($allNotifications as $notif) {
                    $data[] = [
                        'sysuser_id' => $user->sysuser_id,
                        'readable_id' => $notif['id'],
                        'readable_type' => $notif['type'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                
                if (!empty($data)) {
                    DB::transaction(function() use ($data) {
                        UserRead::insert($data);
                    });
                }
                return redirect()->route('notifications.index')->with('success', 'All notifications have been marked as read.');
            } catch (\Exception $e) {
                Log::error("Failed to mark all notifications as read: " . $e->getMessage());
                return back()->with('error', 'Something went wrong while marking notifications as read.');
            }
        }

        // Calculate counts for the entire set before pagination
        $typeCounts = $allNotifications->groupBy('type')->map->count();
        $totalUnread = $allNotifications->count();

        // Manual Pagination for the collection
        $currentPage = Paginator::resolveCurrentPage() ?: 1;
        $perPage = 15;
        $notifications = new LengthAwarePaginator(
            $allNotifications->forPage($currentPage, $perPage),
            $totalUnread,
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath()]
        );

        return view('notifications.index', compact('notifications', 'typeCounts', 'totalUnread'));
    }

    /**
     * Endpoint to mark a specific notification as read via AJAX.
     */
    public function markAsRead(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
                'type' => 'required|string',
            ]);

            UserRead::firstOrCreate([
                'sysuser_id' => Auth::id(),
                'readable_id' => $request->id,
                'readable_type' => $request->type,
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error("AJAX Mark As Read Failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to mark as read.'], 500);
        }
    }
}
