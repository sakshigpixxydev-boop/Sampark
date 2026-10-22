<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\School;
use App\Models\SysUser;
use App\Services\ExternalEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->hasPermission(10801)) {
            abort(403);
        }

        $query = Ticket::with(['school', 'creator', 'assignee']);

        // Role-based filtering via scope
        $query->forUser(Auth::user());
        // Admin sees all (no extra filter)

        // Additional Filters from request
        if ($request->filled('ticket_category_id')) {
            $query->where('ticket_category_id', $request->ticket_category_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        $tickets = $query->latest()->paginate(15);
        $categories = TicketCategory::all();
        
        $schoolsQuery = School::query();
        if (Auth::user()->hasRole('School') || Auth::user()->hasRole('Initiator')) {
            $assignedSchoolIds = Auth::user()->getAssignedSchoolIds();
            $schoolsQuery->whereIn('school_id', $assignedSchoolIds);
        }
        $schools = $schoolsQuery->orderBy('schoolname', 'asc')->get();

        return view('tickets.index', compact('tickets', 'schools', 'categories'));
    }

    public function create()
    {
        if (!Auth::user()->hasPermission(10802)) {
            abort(403);
        }

        $schoolsQuery = School::query();
        if (Auth::user()->hasRole('School') || Auth::user()->hasRole('Initiator')) {
            $assignedSchoolIds = Auth::user()->getAssignedSchoolIds();
            $schoolsQuery->whereIn('school_id', $assignedSchoolIds);
        }
        $schools = $schoolsQuery->orderBy('schoolname', 'asc')->get();
        $categories = TicketCategory::all();

        return view('tickets.create', compact('schools', 'categories'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission(10802)) {
            abort(403);
        }

        $validated = $request->validate([
            'school_id' => Auth::user()->hasRole('School') ? 'required|exists:school,school_id' : 'nullable|exists:school,school_id',
            'ticket_category_id' => 'required|exists:ticket_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:Low,Medium,High,Critical',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        $school = null;
        if (!empty($validated['school_id'])) {
            $school = School::findOrFail($validated['school_id']);
        }
        $category = TicketCategory::findOrFail($validated['ticket_category_id']);
        
        $ticket = new Ticket($validated);
        $ticket->ticket_type = $category->name; // For backward compatibility
        $ticket->created_by = Auth::user()->sysuser_id;
        $ticket->status = 'Open';

        // Assignment logic (All tickets now assigned to School's Initiator if school is specified)
        $ticket->assigned_to = $school ? $school->initiator_id : null;

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('tickets', 'public');
            $ticket->attachment = $path;
        }

        $ticket->save();

        // Send Email Notification (Wrap in try-catch to prevent failure from blocking redirect)
        try {
            $this->sendEmailNotification($ticket);
        } catch (\Exception $e) {
            Log::error("Ticket Email Notification Failed: " . $e->getMessage());
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket submitted successfully.');
    }

    private function sendEmailNotification(Ticket $ticket, $isUpdate = false)
    {
        $category = $ticket->category;
        if (!$category || empty($category->emails)) {
            return;
        }

        $emails = $category->emails;
        if (!is_array($emails)) {
            $emails = array_map('trim', explode(',', $emails));
        }
        $service = new ExternalEmailService();
        
        $viewLink = route('tickets.show', $ticket->ticket_id);
        $ticketIdStr = "#TKT-" . str_pad($ticket->ticket_id, 5, '0', STR_PAD_LEFT);
        
        if ($isUpdate) {
            $subject = "Ticket Status Updated: " . $ticketIdStr . " - " . $ticket->title;
            $title = "Ticket Status Update";
            $additionalInfo = "<p><strong>New Status:</strong> <span style='background-color: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 4px; font-weight: bold;'>" . $ticket->status . "</span></p>";
        } else {
            $subject = "New Ticket Raised: " . $ticketIdStr . " - " . $ticket->title;
            $title = "New Ticket Details";
            $additionalInfo = "";
        }
        
        $messageHtml = "
            <h2 style='color: #00bde0;'>" . $title . "</h2>
            <p><strong>Ticket ID:</strong> " . $ticketIdStr . "</p>
            " . $additionalInfo . "
            <p><strong>Category:</strong> " . $category->name . "</p>
            <p><strong>School:</strong> " . optional($ticket->school)->schoolname . "</p>
            <p><strong>Subject:</strong> " . $ticket->title . "</p>
            <p><strong>Description:</strong><br>" . $ticket->description . "</p>
            <p><strong>Created By:</strong> " . optional($ticket->creator)->fname . " " . optional($ticket->creator)->lname . "</p>
            <p style='margin-top: 20px;'>
                <a href='{$viewLink}' style='background-color: #00bde0; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>View Ticket Details</a>
            </p>
            <hr style='border: 0; border-top: 1px solid #eee; margin: 25px 0;'>
            <p style='font-size: 12px; color: #777;'>Click the button above to view more details and updates for this ticket. You must be logged into your session to view this ticket.</p>
        ";

        foreach ($emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $service->sendSmtpMail([$email], $subject, $messageHtml);
            }
        }
    }

    public function show(Ticket $ticket)
    {
        if (!Auth::user()->hasPermission(10801)) {
            abort(403);
        }

        // Access check
        if (Auth::user()->hasRole('School')) {
            $schoolIds = Auth::user()->getAssignedSchoolIds();
            if (!in_array($ticket->school_id, $schoolIds)) {
                abort(403);
            }
        } elseif (Auth::user()->hasRole('Initiator')) {
            $assignedSchoolIds = Auth::user()->getAssignedSchoolIds();
            if (!in_array($ticket->school_id, $assignedSchoolIds)) {
                abort(403);
            }
        }

        // Mark as read
        \App\Models\UserRead::firstOrCreate([
            'sysuser_id' => Auth::id(),
            'readable_id' => $ticket->ticket_id,
            'readable_type' => 'Ticket',
        ]);

        $ticket->load(['comments.user.role', 'school', 'creator', 'assignee']);

        $assignees = [];
        if (Auth::user()->hasPermission(10803)) {
            $assignees = SysUser::whereHas('role', function ($query) {
                $query->whereIn('rolename', ['Admin', 'Developer', 'Initiator']);
            })->active()->orderBy('fname')->get();
        }

        return view('tickets.show', compact('ticket', 'assignees'));
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        // Permission check for Manage status
        if (!Auth::user()->hasPermission(10803)) {
            abort(403);
        }

        // Logic check: Initiator with school access or Admin/Coordinator/Developer can update status
        $assignedSchoolIds = Auth::user()->getAssignedSchoolIds();
        $isInitiatorWithAccess = Auth::user()->hasRole('Initiator') && in_array($ticket->school_id, $assignedSchoolIds);
        $isAdminOrCoordinator = Auth::user()->hasRole('Admin') || (!Auth::user()->hasRole('School') && !Auth::user()->hasRole('Initiator'));

        if (!$isInitiatorWithAccess && !$isAdminOrCoordinator) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'nullable|in:Open,In Progress,Resolved,Closed,Reopen',
            'priority' => 'nullable|in:Low,Medium,High,Critical',
            'assigned_to' => 'nullable|exists:sysuser,sysuser_id',
        ]);

        $updateData = [];
        if ($request->filled('status')) {
            $updateData['status'] = $validated['status'];
        }
        if ($request->filled('priority')) {
            $updateData['priority'] = $validated['priority'];
        }
        if ($request->has('assigned_to')) {
            $updateData['assigned_to'] = $validated['assigned_to'];
        }

        if (!empty($updateData)) {
            $ticket->update($updateData);

            // Send Email Notification for update
            try {
                $this->sendEmailNotification($ticket, true);
            } catch (\Exception $e) {
                Log::error("Ticket Update Email Notification Failed: " . $e->getMessage());
            }
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket updated successfully.');
    }

    public function storeComment(Request $request, Ticket $ticket)
    {
        if (!Auth::user()->hasPermission(10801)) {
            abort(403);
        }

        // Access check
        if (Auth::user()->hasRole('School')) {
            $schoolIds = Auth::user()->getAssignedSchoolIds();
            if (!in_array($ticket->school_id, $schoolIds)) {
                abort(403);
            }
        } elseif (Auth::user()->hasRole('Initiator')) {
            $assignedSchoolIds = Auth::user()->getAssignedSchoolIds();
            if (!in_array($ticket->school_id, $assignedSchoolIds)) {
                abort(403);
            }
        }

        $validated = $request->validate([
            'comment' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,zip|max:5120',
        ]);

        $comment = new \App\Models\TicketComment();
        $comment->ticket_id = $ticket->ticket_id;
        $comment->user_id = Auth::user()->sysuser_id;
        $comment->comment = $validated['comment'];

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('tickets/comments', 'public');
            $comment->attachment = $path;
        }

        $comment->save();

        // Send comment email notification
        try {
            $this->sendCommentNotification($comment);
        } catch (\Exception $e) {
            Log::error("Ticket Comment Email Notification Failed: " . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Comment added successfully.');
    }

    private function sendCommentNotification(\App\Models\TicketComment $comment)
    {
        $ticket = $comment->ticket;
        $author = $comment->user;
        $service = new ExternalEmailService();
        
        $ticketIdStr = "#TKT-" . str_pad($ticket->ticket_id, 5, '0', STR_PAD_LEFT);
        $subject = "New Comment on Ticket: " . $ticketIdStr . " - " . $ticket->title;
        $viewLink = route('tickets.show', $ticket->ticket_id);
        
        $messageHtml = "
            <h2 style='color: #00bde0;'>New Comment Added</h2>
            <p><strong>Ticket ID:</strong> " . $ticketIdStr . "</p>
            <p><strong>Subject:</strong> " . $ticket->title . "</p>
            <p><strong>Commented By:</strong> " . $author->fname . " " . $author->lname . " (" . optional($author->role)->rolename . ")</p>
            <p><strong>Comment:</strong><br>" . $comment->comment . "</p>
            <p style='margin-top: 20px;'>
                <a href='{$viewLink}' style='background-color: #00bde0; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>View Ticket Details</a>
            </p>
            <hr style='border: 0; border-top: 1px solid #eee; margin: 25px 0;'>
            <p style='font-size: 12px; color: #777;'>Click the button above to view more details and updates for this ticket. You must be logged into your session to view this ticket.</p>
        ";

        $recipients = [];
        
        // Notify ticket creator (if the comment is NOT by the creator)
        if ($ticket->created_by !== $author->sysuser_id && $ticket->creator && $ticket->creator->email) {
            $recipients[] = $ticket->creator->email;
        }

        // Notify ticket assignee (if the comment is NOT by the assignee)
        if ($ticket->assigned_to && $ticket->assigned_to !== $author->sysuser_id && $ticket->assignee && $ticket->assignee->email) {
            $recipients[] = $ticket->assignee->email;
        }

        // Notify category notification list
        $category = $ticket->category;
        if ($category && !empty($category->emails)) {
            $categoryEmails = $category->emails;
            if (!is_array($categoryEmails)) {
                $categoryEmails = array_map('trim', explode(',', $categoryEmails));
            }
            foreach ($categoryEmails as $cEmail) {
                if ($cEmail !== $author->email) {
                    $recipients[] = $cEmail;
                }
            }
        }

        $recipients = array_unique(array_filter($recipients));

        foreach ($recipients as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $service->sendSmtpMail([$email], $subject, $messageHtml);
            }
        }
    }
}
