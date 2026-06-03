<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketResponse;
use App\Notifications\TicketAssigned;
use App\Notifications\TicketResolved;
use App\Notifications\TicketTaken;
use App\Notifications\TicketReopened;
use App\Notifications\NewTicketNotification;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function __construct()
    {
        // You can apply middleware here if desired
    }

    /**
     * Display a listing of tickets (role-aware).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $q = $request->get('q');
        $filter = $request->get('filter');
        $hasReopen = $request->get('has_reopen');

        $query = Ticket::with(['user', 'category', 'assignedTo']);

        // Pengurutan: prioritas -> SLA deadline -> created_at
        $query->orderByRaw("
            CASE priority
                WHEN 'urgent' THEN 1
                WHEN 'high' THEN 2
                WHEN 'medium' THEN 3
                WHEN 'low' THEN 4
                ELSE 5
            END ASC
        ");
        $query->orderBy('sla_due_at', 'asc');
        $query->orderBy('created_at', 'asc');

        // search
        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('ticket_number', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%")
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$q}%"));
            });
        }

        // filter by reopen status
        if ($hasReopen === 'yes') {
            $query->where('reopen_count', '>', 0);
        } elseif ($hasReopen === 'no') {
            $query->where('reopen_count', 0);
        }

        // role-specific
        if ($user->role === 'admin') {
            // Admin melihat semua ticket
        } elseif (in_array($user->role, ['it_staff', 'it'])) {
            if ($filter === 'my' || !$filter) {
                $query->where('assigned_to', $user->id)
                    ->whereNotIn('status', ['resolved', 'closed']);
            } elseif ($filter === 'resolved') {
                $query->where('assigned_to', $user->id)
                    ->whereIn('status', ['resolved', 'closed']);
            } else {
                $query->where('assigned_to', $user->id);
            }
        } else {
            $query->where('user_id', $user->id);
        }

        $tickets = $query->paginate(15)->appends($request->except('page'));

        return view('tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create(Request $request)
    {
        $categories = Category::all();

        $itStaff = [];
        if ($request->user()->role === 'admin') {
            $itStaff = User::whereIn('role', ['admin', 'it_staff', 'it'])->get();
        }

        return view('tickets.create', compact('categories', 'itStaff'));
    }

    /**
     * Store a newly created ticket.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $baseRules = [
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
        ];

        if ($user->role === 'admin') {
            $adminRules = [
                'assigned_to' => 'nullable|exists:users,id',
                'priority' => 'nullable|in:low,medium,high,urgent',
                'status' => 'nullable|in:open,in_queue,in_progress,resolved,closed',
                'resolved_at' => 'nullable|date',
                'sla_due_at' => 'nullable|date',
            ];
            $rules = array_merge($baseRules, $adminRules);
        } else {
            $rules = $baseRules;
        }

        $validated = $request->validate($rules);

        return DB::transaction(function () use ($validated, $user, $request) {
            $data = [
                'ticket_number' => Ticket::generateTicketNumber(),
                'title' => $validated['title'],
                'description' => $validated['description'],
                'user_id' => $user->id,
                'category_id' => $validated['category_id'],
                'priority' => 'medium',
                'status' => 'open', // Default status OPEN
            ];

            if ($user->role === 'admin') {
                if (!empty($validated['priority'])) {
                    $data['priority'] = $validated['priority'];
                }

                // Jika admin assign langsung ke IT Staff
                if (!empty($validated['assigned_to'])) {
                    $data['assigned_to'] = $validated['assigned_to'];
                    $data['assigned_by'] = $user->id;
                    $data['assigned_at'] = now();
                    $data['status'] = 'in_queue'; // Masuk antrian IT
                } else {
                    // Jika tidak diassign, status tetap open
                    $data['status'] = $validated['status'] ?? 'open';
                }

                if (!empty($validated['resolved_at'])) {
                    $data['resolved_at'] = $validated['resolved_at'];
                }
                if (!empty($validated['sla_due_at'])) {
                    $data['sla_due_at'] = $validated['sla_due_at'];
                }
            }

            $ticket = Ticket::create($data);

            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'action' => 'created',
                'notes' => ($user->role === 'admin') ? 'Created by admin' : 'Created by user'
            ]);

            // Kirim notifikasi ticket baru
            $adminsAndIT = User::whereIn('role', ['admin', 'it_staff', 'it'])->get();
            foreach ($adminsAndIT as $recipient) {
                if ($recipient->id !== $user->id) {
                    $recipient->notify(new NewTicketNotification($ticket, 'created'));
                }
            }

            return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket berhasil dibuat.');
        });
    }

    /**
     * Display the specified ticket.
     */
    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load(['user', 'category', 'responses.user', 'histories', 'assignedTo']);

        $itStaff = [];
        if (auth()->user()->role === 'admin') {
            $itStaff = User::whereIn('role', ['admin', 'it_staff', 'it'])->get();
        }

        return view('tickets.show', compact('ticket', 'itStaff'));
    }

    /**
     * Show the form for editing the ticket.
     */
    public function edit(Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $categories = Category::all();

        $itStaff = [];
        if (auth()->user()->role === 'admin') {
            $itStaff = User::whereIn('role', ['admin', 'it_staff', 'it'])->get();
        }

        return view('tickets.edit', compact('ticket', 'categories', 'itStaff'));
    }

    /**
     * Update the ticket basic (role-aware).
     */
    public function update(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $user = $request->user();

        $baseRules = [
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
        ];

        if ($user->role === 'admin') {
            $adminRules = [
                'assigned_to' => 'nullable|exists:users,id',
                'priority' => 'nullable|in:low,medium,high,urgent',
                'status' => 'nullable|in:open,in_queue,in_progress,resolved,closed',
                'resolved_at' => 'nullable|date',
                'sla_due_at' => 'nullable|date',
            ];
            $rules = array_merge($baseRules, $adminRules);
        } else {
            $rules = $baseRules;
        }

        $validated = $request->validate($rules);

        return DB::transaction(function () use ($validated, $ticket, $user) {
            $ticket->title = $validated['title'];
            $ticket->category_id = $validated['category_id'];
            $ticket->description = $validated['description'];
            $ticket->save();

            if ($user->role === 'admin') {
                $changes = [];

                if (array_key_exists('assigned_to', $validated)) {
                    $oldAssign = $ticket->assigned_to;
                    $ticket->assigned_to = $validated['assigned_to'] ?: null;
                    if ($validated['assigned_to']) {
                        $ticket->assigned_by = $user->id;
                        $ticket->assigned_at = now();
                        // Jika diassign dari open, masuk antrian
                        if ($ticket->status === 'open') {
                            $ticket->status = 'in_queue';
                        }
                    } else {
                        $ticket->assigned_by = null;
                        $ticket->assigned_at = null;
                    }
                    $changes['assigned_to'] = ['old' => $oldAssign, 'new' => $ticket->assigned_to];
                }

                if (array_key_exists('priority', $validated) && $validated['priority']) {
                    $oldPriority = $ticket->priority;
                    $ticket->priority = $validated['priority'];
                    $changes['priority'] = ['old' => $oldPriority, 'new' => $ticket->priority];
                }

                if (array_key_exists('status', $validated) && $validated['status']) {
                    $oldStatus = $ticket->status;
                    $ticket->status = $validated['status'];
                    if ($validated['status'] === 'resolved') {
                        $ticket->resolved_at = now();
                    }
                    $changes['status'] = ['old' => $oldStatus, 'new' => $ticket->status];
                }

                if (array_key_exists('resolved_at', $validated) && $validated['resolved_at']) {
                    $ticket->resolved_at = $validated['resolved_at'];
                }

                if (array_key_exists('sla_due_at', $validated)) {
                    $ticket->sla_due_at = $validated['sla_due_at'];
                    $changes['sla_due_at'] = ['old' => $ticket->getOriginal('sla_due_at'), 'new' => $validated['sla_due_at']];
                }

                $ticket->save();

                TicketHistory::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'action' => 'updated_by_admin',
                    'notes' => 'Admin updated ticket fields',
                    'meta' => json_encode($changes)
                ]);
            } else {
                TicketHistory::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'action' => 'updated',
                    'notes' => 'User updated title/description/category',
                ]);
            }

            return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket diperbarui.');
        });
    }

    /**
     * Remove the ticket (only admin typically).
     */
    public function destroy(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();

        return redirect()->route('tickets.index')->with('success', 'Ticket dihapus.');
    }

    /**
     * Assign ticket to IT or Admin (admin only) - Masukkan ke antrian IT
     */
    public function assign(Request $request, Ticket $ticket)
    {
        $this->authorize('assign', $ticket);

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'sla_due_at' => 'nullable|date',
            'notify' => 'nullable|boolean'
        ]);

        $assignee = User::find($validated['assigned_to']);
        if (!in_array($assignee->role, ['admin', 'it_staff', 'it'])) {
            return back()->with('error', 'Ticket hanya bisa di-assign ke admin atau IT Staff.');
        }

        $oldAssign = $ticket->assigned_to;
        $oldStatus = $ticket->status;
        $oldPriority = $ticket->priority;
        $oldSlaDue = $ticket->sla_due_at;

        $ticket->assigned_to = $validated['assigned_to'];
        $ticket->assigned_by = auth()->id();
        $ticket->assigned_at = now();

        // Perubahan status: open -> in_queue (masuk antrian)
        if ($ticket->status === 'open') {
            $ticket->status = 'in_queue';
        }

        if (!empty($validated['priority'])) {
            $ticket->priority = $validated['priority'];
        }

        if (!empty($validated['sla_due_at'])) {
            $ticket->sla_due_at = $validated['sla_due_at'];
        }

        $ticket->save();

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'action' => 'assigned',
            'notes' => "Assigned to " . $assignee->name . " (role: {$assignee->role}) - Ticket dalam antrian",
            'meta' => json_encode([
                'old_assigned' => $oldAssign,
                'new_assigned' => $validated['assigned_to'],
                'new_assigned_role' => $assignee->role,
                'old_status' => $oldStatus,
                'new_status' => 'in_queue',
                'old_priority' => $oldPriority,
                'new_priority' => $ticket->priority,
                'old_sla_due' => $oldSlaDue,
                'new_sla_due' => $ticket->sla_due_at
            ])
        ]);

        $notify = $request->boolean('notify', true);
        if ($notify) {
            $assignee->notify(new TicketAssigned($ticket));
        }

        if ($ticket->user_id !== $assignee->id) {
            $ticket->user->notify(new \App\Notifications\TicketStatusUpdate($ticket, 'assigned', $assignee->name));
        }

        return back()->with('success', "Ticket berhasil ditugaskan ke {$assignee->name} (dalam antrian).");
    }

    /**
     * IT or Admin self-assign (take) - Ambil ticket dari antrian
     */
    public function take(Ticket $ticket)
    {
        $this->authorize('take', $ticket);

        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'it_staff', 'it'])) {
            return back()->with('error', 'Hanya admin atau IT Staff yang bisa mengambil ticket.');
        }

        // Hanya bisa mengambil ticket yang statusnya in_queue atau open
        if (!in_array($ticket->status, ['open', 'in_queue'])) {
            return back()->with('error', 'Ticket ini tidak dapat diambil karena sudah dalam proses atau selesai.');
        }

        $oldAssign = $ticket->assigned_to;
        $oldStatus = $ticket->status;

        $ticket->update([
            'assigned_to' => Auth::id(),
            'assigned_by' => Auth::id(),
            'assigned_at' => now(),
            'status' => 'in_progress' // Mulai dikerjakan
        ]);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'action' => 'taken',
            'notes' => "Taken by " . $user->name . " (role: {$user->role}) - Mulai mengerjakan ticket",
            'meta' => json_encode([
                'old_assigned' => $oldAssign,
                'new_assigned' => Auth::id(),
                'new_assigned_role' => $user->role,
                'old_status' => $oldStatus,
                'new_status' => 'in_progress'
            ])
        ]);

        $otherAdmins = User::where('role', 'admin')
            ->where('id', '!=', Auth::id())
            ->get();
        foreach ($otherAdmins as $admin) {
            $admin->notify(new TicketTaken($ticket, Auth::user()));
        }

        if ($ticket->user_id !== Auth::id()) {
            $ticket->user->notify(new \App\Notifications\TicketStatusUpdate($ticket, 'taken', $user->name));
        }

        return back()->with('success', "Ticket berhasil diambil dari antrian dan sedang Anda kerjakan.");
    }

    /**
     * Update status (in_queue, in_progress, resolved, closed, etc.)
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $this->authorize('updateStatus', $ticket);

        $rules = [
            'status' => 'required|in:open,in_queue,in_progress,resolved,closed',
        ];

        if ($request->status === 'resolved') {
            $rules['resolution_notes'] = 'required|string|min:10';
            $rules['resolution_attachments.*'] = 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,txt';
        }

        $request->validate($rules);

        if (in_array($ticket->status, ['resolved', 'closed']) && $request->status !== 'closed') {
            return back()->with('error', 'Ticket yang sudah selesai tidak dapat diubah statusnya.');
        }

        $oldStatus = $ticket->status;
        $updateData = ['status' => $request->status];

        if ($request->status === 'resolved') {
            $updateData['resolved_at'] = now();
            $updateData['resolved_by'] = Auth::id();
            $updateData['resolution_notes'] = $request->resolution_notes;

            if ($request->hasFile('resolution_attachments')) {
                $attachments = [];
                foreach ($request->file('resolution_attachments') as $file) {
                    $path = $file->store('ticket_resolutions/' . $ticket->id, 'public');
                    $attachments[] = [
                        'path' => $path,
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime' => $file->getMimeType(),
                        'uploaded_at' => now()->toDateTimeString()
                    ];
                }
                $updateData['resolution_attachments'] = $attachments;
            }

            if (class_exists(\App\Models\TicketResponse::class)) {
                \App\Models\TicketResponse::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => Auth::id(),
                    'message' => "✅ **TICKET RESOLVED**\n\n**Solusi yang diberikan:**\n" . $request->resolution_notes,
                    'is_internal' => false,
                    'attachment' => null
                ]);
            }
        }

        $ticket->update($updateData);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'action' => 'status_changed',
            'notes' => "Status berubah dari " . ucfirst(str_replace('_', ' ', $oldStatus)) . " menjadi " . ucfirst(str_replace('_', ' ', $request->status)),
            'meta' => json_encode([
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'has_resolution' => $request->status === 'resolved'
            ])
        ]);

        if ($request->status === 'resolved') {
            $ticket->user->notify(new TicketResolved($ticket, Auth::user()));
            $admins = User::where('role', 'admin')->where('id', '!=', Auth::id())->get();
            foreach ($admins as $admin) {
                $admin->notify(new TicketResolved($ticket, Auth::user()));
            }
        } elseif ($request->status === 'in_progress') {
            $ticket->user->notify(new \App\Notifications\TicketStatusUpdate($ticket, 'in_progress', Auth::user()->name));
        } elseif ($request->status === 'in_queue') {
            $ticket->user->notify(new \App\Notifications\TicketStatusUpdate($ticket, 'in_queue', Auth::user()->name));
        } elseif ($request->status === 'closed') {
            $ticket->user->notify(new \App\Notifications\TicketStatusUpdate($ticket, 'closed', Auth::user()->name));
        }

        $message = $request->status === 'resolved'
            ? 'Ticket telah diselesaikan. Solusi telah dicatat.'
            : 'Status ticket berhasil diperbarui.';

        return back()->with('success', $message);
    }

    /**
     * Reopen ticket yang sudah resolved/closed
     */
    public function reopen(Request $request, Ticket $ticket)
    {
        $this->authorize('reopen', $ticket);

        $request->validate([
            'reopen_reason' => 'required|string|min:10',
        ]);

        if (!in_array($ticket->status, ['resolved', 'closed'])) {
            return back()->with('error', 'Hanya ticket yang sudah resolved/closed yang bisa dibuka kembali.');
        }

        $oldStatus = $ticket->status;
        $oldResolvedAt = $ticket->resolved_at;
        $oldResolvedBy = $ticket->resolved_by;

        $ticket->update([
            'status' => 'open', // Kembali ke open
            'resolved_at' => null,
            'resolved_by' => null,
            'reopened_at' => now(),
            'reopened_by' => Auth::id(),
            'reopen_reason' => $request->reopen_reason,
            'reopen_count' => ($ticket->reopen_count ?? 0) + 1
        ]);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'action' => 'reopened',
            'notes' => "Ticket dibuka kembali oleh " . Auth::user()->name,
            'meta' => json_encode([
                'old_status' => $oldStatus,
                'new_status' => 'open',
                'reason' => $request->reopen_reason,
                'old_resolved_at' => $oldResolvedAt,
                'old_resolved_by' => $oldResolvedBy
            ])
        ]);

        if ($ticket->assigned_to) {
            $assignee = User::find($ticket->assigned_to);
            if ($assignee) {
                $assignee->notify(new TicketReopened($ticket, Auth::user()));
            }
        }

        $admins = User::where('role', 'admin')->where('id', '!=', Auth::id())->get();
        foreach ($admins as $admin) {
            $admin->notify(new TicketReopened($ticket, Auth::user()));
        }

        if ($ticket->user_id !== Auth::id()) {
            $ticket->user->notify(new \App\Notifications\TicketStatusUpdate($ticket, 'reopened', Auth::user()->name, $request->reopen_reason));
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket berhasil dibuka kembali untuk revisi.');
    }

    /**
     * Search tickets used by header search.
     */
    public function search(Request $request)
    {
        $q = $request->get('q');
        $tickets = Ticket::with('user', 'category')
            ->where('ticket_number', 'like', "%{$q}%")
            ->orWhere('title', 'like', "%{$q}%")
            ->orWhereHas('user', function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%");
            })->orderBy('created_at', 'desc')->paginate(15);

        return view('tickets.index', compact('tickets'));
    }

    /**
     * Notifications endpoint for header - returns recent ticket histories as JSON.
     */
    public function notifications()
    {
        $recent = \App\Models\TicketHistory::with(['ticket', 'user'])
            ->latest()
            ->take(8)
            ->get()
            ->map(function ($h) {
                return [
                    'id' => $h->id,
                    'ticket_id' => $h->ticket_id,
                    'ticket_number' => optional($h->ticket)->ticket_number,
                    'ticket_title' => optional($h->ticket)->title,
                    'action' => $h->action,
                    'notes' => $h->notes,
                    'meta' => $h->meta,
                    'user_name' => optional($h->user)->name,
                    'created_at' => $h->created_at->toDateTimeString(),
                    'time_ago' => $h->created_at->diffForHumans(),
                ];
            });

        $count = $recent->count();

        return response()->json([
            'count' => $count,
            'items' => $recent,
        ]);
    }

    /**
     * Show logged-in user's notifications
     */
    public function userNotifications()
    {
        $user = auth()->user();

        $notifications = $user->notifications()->take(10)->get();
        $unread = $user->unreadNotifications->count();

        return response()->json([
            'unread' => $unread,
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markNotificationRead($id)
    {
        $user = auth()->user();
        $notification = $user->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get all notifications for user
     */
    public function allNotifications()
    {
        $user = auth()->user();
        return response()->json($user->notifications()->take(50)->get());
    }

    /**
     * Get notifications for authenticated user
     */
    public function getNotifications(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->toDateTimeString(),
                    'time_ago' => $notification->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }

    /**
     * Get notification count
     */
    public function getNotificationCount(Request $request)
    {
        $user = $request->user();
        $count = $user->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Notification page
     */
    public function notificationPage()
    {
        $user = auth()->user();
        $notifications = $user->notifications()->paginate(20);

        return view('tickets.notifications', compact('notifications'));
    }

    // ==================== IT STAFF/IT METHODS ====================

    /**
     * IT Staff/IT - Melihat semua ticket (read-only)
     */
    public function itAllTickets(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['it_staff', 'it'])) {
            abort(403, 'Unauthorized');
        }

        $q = $request->get('q');
        $status = $request->get('status');
        $priority = $request->get('priority');
        $hasReopen = $request->get('has_reopen');

        $query = Ticket::with(['user', 'category', 'assignedTo']);

        $query->orderByRaw("
            CASE priority
                WHEN 'urgent' THEN 1
                WHEN 'high' THEN 2
                WHEN 'medium' THEN 3
                WHEN 'low' THEN 4
                ELSE 5
            END ASC
        ");
        $query->orderBy('sla_due_at', 'asc');
        $query->orderBy('created_at', 'asc');

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('ticket_number', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%")
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$q}%"));
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($priority) {
            $query->where('priority', $priority);
        }

        if ($hasReopen === 'yes') {
            $query->where('reopen_count', '>', 0);
        } elseif ($hasReopen === 'no') {
            $query->where('reopen_count', 0);
        }

        $tickets = $query->paginate(15)->appends($request->except('page'));

        return view('tickets.it-all-tickets', compact('tickets'));
    }

    /**
     * IT Staff/IT - Ticket yang ditugaskan kepada mereka (untuk dikelola)
     */
    public function itMyTickets(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['it_staff', 'it'])) {
            abort(403, 'Unauthorized');
        }

        $q = $request->get('q');
        $status = $request->get('status');
        $priority = $request->get('priority');
        $tab = $request->get('tab', 'active');

        // ==================== ACTIVE TICKETS (in_queue, in_progress) ====================
        $activeQuery = Ticket::with(['user', 'category', 'assignedTo'])
            ->where('assigned_to', $user->id)
            ->whereIn('status', ['in_queue', 'in_progress']);

        $activeQuery->orderByRaw("
            CASE priority
                WHEN 'urgent' THEN 1
                WHEN 'high' THEN 2
                WHEN 'medium' THEN 3
                WHEN 'low' THEN 4
                ELSE 5
            END ASC
        ");
        $activeQuery->orderBy('sla_due_at', 'asc');
        $activeQuery->orderBy('created_at', 'asc');

        if ($q) {
            $activeQuery->where(function ($sub) use ($q) {
                $sub->where('ticket_number', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%");
            });
        }

        if ($status && in_array($status, ['in_queue', 'in_progress'])) {
            $activeQuery->where('status', $status);
        }

        if ($priority) {
            $activeQuery->where('priority', $priority);
        }

        // ==================== RESOLVED TICKETS (resolved, closed) ====================
        $resolvedQuery = Ticket::with(['user', 'category', 'assignedTo'])
            ->where('assigned_to', $user->id)
            ->whereIn('status', ['resolved', 'closed']);

        $resolvedQuery->orderBy('resolved_at', 'desc');

        if ($q) {
            $resolvedQuery->where(function ($sub) use ($q) {
                $sub->where('ticket_number', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%");
            });
        }

        if ($status && in_array($status, ['resolved', 'closed'])) {
            $resolvedQuery->where('status', $status);
        }

        if ($priority) {
            $resolvedQuery->where('priority', $priority);
        }

        // ==================== PAGINATION ====================
        $activeTickets = $activeQuery->paginate(15, ['*'], 'active_page')->appends($request->except('active_page'));
        $resolvedTickets = $resolvedQuery->paginate(15, ['*'], 'resolved_page')->appends($request->except('resolved_page'));

        $activeTicketsCount = $activeQuery->count();
        $resolvedTicketsCount = $resolvedQuery->count();

        // Untuk view, kirimkan sesuai tab yang aktif
        if ($tab === 'active') {
            $tickets = $activeTickets;
        } else {
            $tickets = $resolvedTickets;
        }

        return view('tickets.it-my-tickets', compact(
            'activeTickets',
            'resolvedTickets',
            'tickets',
            'activeTicketsCount',
            'resolvedTicketsCount'
        ));
    }

    /**
     * IT Staff/IT - Lihat detail ticket
     */
    public function itShow(Ticket $ticket)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['it_staff', 'it'])) {
            abort(403, 'Unauthorized');
        }

        $ticket->load(['user', 'category', 'responses.user', 'histories', 'assignedTo']);

        return view('tickets.show', compact('ticket'));
    }

    /**
     * IT Staff/IT - Update status (ambil dari antrian atau selesaikan)
     */
    public function itUpdateStatus(Request $request, Ticket $ticket)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['it_staff', 'it'])) {
            abort(403, 'Unauthorized');
        }

        if ($ticket->assigned_to !== $user->id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengubah status ticket ini.');
        }

        $request->validate([
            'status' => 'required|in:in_queue,in_progress,resolved,closed',
        ]);

        if ($request->status === 'resolved') {
            $request->validate([
                'resolution_notes' => 'required|string|min:10',
            ]);
        }

        $oldStatus = $ticket->status;
        $ticket->status = $request->status;

        if ($request->status === 'resolved') {
            $ticket->resolved_at = now();
            $ticket->resolved_by = $user->id;
            $ticket->resolution_notes = $request->resolution_notes;
        }

        $ticket->save();

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'action' => 'status_changed',
            'notes' => "Status berubah dari " . ucfirst(str_replace('_', ' ', $oldStatus)) . " menjadi " . ucfirst(str_replace('_', ' ', $request->status)),
        ]);

        // Kirim notifikasi
        if ($request->status === 'resolved') {
            $ticket->user->notify(new TicketResolved($ticket, $user));
            $admins = User::where('role', 'admin')->where('id', '!=', $user->id)->get();
            foreach ($admins as $admin) {
                $admin->notify(new TicketResolved($ticket, $user));
            }
        } elseif ($request->status === 'in_progress') {
            $ticket->user->notify(new \App\Notifications\TicketStatusUpdate($ticket, 'in_progress', $user->name));
        }

        return back()->with('success', 'Status ticket berhasil diperbarui.');
    }

    /**
     * IT Staff/IT - Take/self-assign ticket (ambil dari antrian)
     */
    public function itTake(Ticket $ticket)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['it_staff', 'it'])) {
            abort(403, 'Unauthorized');
        }

        // Hanya bisa mengambil ticket yang statusnya in_queue (dalam antrian)
        if ($ticket->status !== 'in_queue') {
            return back()->with('error', 'Ticket ini tidak dapat diambil karena sudah dalam proses atau bukan antrian.');
        }

        if ($ticket->assigned_to !== null && $ticket->assigned_to !== $user->id) {
            return back()->with('error', 'Ticket sudah diassign ke staff lain.');
        }

        $oldAssign = $ticket->assigned_to;
        $oldStatus = $ticket->status;

        $ticket->update([
            'assigned_to' => $user->id,
            'assigned_by' => $user->id,
            'assigned_at' => now(),
            'status' => 'in_progress'
        ]);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'action' => 'taken',
            'notes' => "Ticket diambil oleh " . $user->name . " dari antrian",
            'meta' => json_encode([
                'old_assigned' => $oldAssign,
                'new_assigned' => $user->id,
                'new_assigned_role' => $user->role,
                'old_status' => $oldStatus,
                'new_status' => 'in_progress'
            ])
        ]);

        if ($ticket->user_id !== $user->id) {
            $ticket->user->notify(new \App\Notifications\TicketStatusUpdate($ticket, 'taken', $user->name));
        }

        $admins = User::where('role', 'admin')->where('id', '!=', $user->id)->get();
        foreach ($admins as $admin) {
            $admin->notify(new TicketTaken($ticket, $user));
        }

        return redirect()->route('it.tickets.show', $ticket)
            ->with('success', 'Ticket berhasil diambil dari antrian dan sedang Anda kerjakan.');
    }

    /**
     * IT Staff/IT - Reopen ticket yang sudah resolved/closed
     */
    public function itReopen(Request $request, Ticket $ticket)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['it_staff', 'it'])) {
            abort(403, 'Unauthorized');
        }

        if ($ticket->assigned_to !== $user->id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk membuka kembali ticket ini.');
        }

        $request->validate([
            'reopen_reason' => 'required|string|min:10',
        ]);

        $oldStatus = $ticket->status;
        $oldResolvedAt = $ticket->resolved_at;
        $oldResolvedBy = $ticket->resolved_by;

        $ticket->update([
            'status' => 'in_queue', // Kembali ke antrian, bukan open langsung
            'resolved_at' => null,
            'resolved_by' => null,
            'reopened_at' => now(),
            'reopened_by' => $user->id,
            'reopen_reason' => $request->reopen_reason,
            'reopen_count' => ($ticket->reopen_count ?? 0) + 1
        ]);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'action' => 'reopened',
            'notes' => "Ticket dibuka kembali oleh " . $user->name,
            'meta' => json_encode([
                'old_status' => $oldStatus,
                'new_status' => 'in_queue',
                'reason' => $request->reopen_reason,
                'old_resolved_at' => $oldResolvedAt,
                'old_resolved_by' => $oldResolvedBy
            ])
        ]);

        if ($ticket->user_id !== $user->id) {
            $ticket->user->notify(new \App\Notifications\TicketStatusUpdate($ticket, 'reopened', $user->name, $request->reopen_reason));
        }

        $admins = User::where('role', 'admin')->where('id', '!=', $user->id)->get();
        foreach ($admins as $admin) {
            $admin->notify(new TicketReopened($ticket, $user));
        }

        return redirect()->route('it.tickets.show', $ticket)
            ->with('success', 'Ticket berhasil dibuka kembali dan masuk ke antrian.');
    }
}
