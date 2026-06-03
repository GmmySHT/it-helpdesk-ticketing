<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Show dashboard — choose view by role and provide relevant data.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Common stats
        $totalTickets = Ticket::count();
        $openTickets = Ticket::whereIn('status', ['open', 'in_progress'])->count();
        $resolvedTickets = Ticket::where('status', 'resolved')->count();
        $closedTickets = Ticket::where('status', 'closed')->count();

        // Hitung persentase perubahan untuk statistik utama
        $totalChange = $this->calculateTicketChange('total');
        $openChange = $this->calculateTicketChange('open');
        $resolvedChange = $this->calculateTicketChange('resolved');
        $closedChange = $this->calculateTicketChange('closed');

        // Last 6 months tickets (for chart)
        $months = [];
        $ticketCountsByMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $months[] = $m->format('M Y');
            $ticketCountsByMonth[] = Ticket::whereYear('created_at', $m->year)
                ->whereMonth('created_at', $m->month)
                ->count();
        }

        $statusCounts = Ticket::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $recentTickets = Ticket::with(['user', 'category'])->latest()->take(6)->get();
        $recentUsers = User::latest()->take(6)->get();
        $categoryCounts = Category::withCount('tickets')->get();

        // ==================== DATA UNTUK SEMUA ROLE ====================
        // Overdue tickets
        $overdueTickets = Ticket::whereNotNull('sla_due_at')
            ->where('sla_due_at', '<', now())
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        // SLA Compliance Rate
        $totalResolved = Ticket::where('status', 'resolved')->count();
        $onTimeResolved = Ticket::where('status', 'resolved')
            ->where(function($q) {
                $q->whereNull('sla_due_at')
                    ->orWhereRaw('resolved_at <= sla_due_at');
            })->count();
        $slaComplianceRate = $totalResolved > 0 ? round(($onTimeResolved / $totalResolved) * 100) : 100;

        // Average Resolution Time
        $avgResolutionTime = Ticket::whereNotNull('resolved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
            ->value('avg_hours') ?? 0;
        $avgResolutionTime = round($avgResolutionTime, 1);

        // Reopen Rate
        $totalClosed = Ticket::whereIn('status', ['resolved', 'closed'])->count();
        $totalReopened = Ticket::where('reopen_count', '>', 0)->count();
        $reopenRate = $totalClosed > 0 ? round(($totalReopened / $totalClosed) * 100) : 0;

        // IT Staff Performance
        $itStaffPerformance = User::whereIn('role', ['it_staff', 'it'])
            ->withCount(['assignedTickets as resolved_count' => function($q) {
                $q->where('status', 'resolved');
            }])
            ->withCount(['assignedTickets as active_count' => function($q) {
                $q->whereNotIn('status', ['resolved', 'closed']);
            }])
            ->get()
            ->map(function($staff) {
                $total = $staff->resolved_count + $staff->active_count;
                $staff->completion_rate = $total > 0 ? round(($staff->resolved_count / $total) * 100) : 0;
                return $staff;
            });

        // Data shared to all views
        $data = compact(
            'totalTickets', 'openTickets', 'resolvedTickets', 'closedTickets',
            'totalChange', 'openChange', 'resolvedChange', 'closedChange',
            'months', 'ticketCountsByMonth', 'statusCounts',
            'recentTickets', 'recentUsers', 'categoryCounts',
            'overdueTickets', 'slaComplianceRate', 'avgResolutionTime',
            'reopenRate', 'itStaffPerformance'
        );

        // ==================== ADMIN DASHBOARD ====================
        if ($user->role === 'admin') {
            $usersCount = User::count();
            $itStaffCount = User::whereIn('role', ['it_staff', 'it'])->count();
            $ticketsUnassigned = Ticket::whereNull('assigned_to')->count();

            $usersChange = $this->calculateUserChange();
            $itStaffChange = $this->calculateITStaffChange();
            $unassignedChange = $this->calculateUnassignedChange();

            $adminExtras = compact(
                'usersCount', 'itStaffCount', 'ticketsUnassigned',
                'usersChange', 'itStaffChange', 'unassignedChange'
            );

            return view('dashboard.admin', array_merge($data, $adminExtras));
        }

        // ==================== IT DASHBOARD ====================
        if (in_array($user->role, ['it_staff', 'it'])) {
            // KPI untuk tim IT
            $inQueueCount = Ticket::whereNull('assigned_to')->count();
            $myAssignedCount = Ticket::where('assigned_to', $user->id)->count();
            $inProgressCount = Ticket::where('status', 'in_progress')->count();
            $resolvedThisMonth = Ticket::where('status', 'resolved')
                ->whereYear('resolved_at', now()->year)
                ->whereMonth('resolved_at', now()->month)
                ->count();

            $inQueueChange = $this->calculateInQueueChange();
            $myAssignedChange = $this->calculateMyAssignedChange($user->id);
            $inProgressChange = $this->calculateInProgressChange();
            $resolvedMonthChange = $this->calculateResolvedMonthChange();

            $myActiveTickets = Ticket::with(['user', 'category'])
                ->where('assigned_to', $user->id)
                ->whereIn('status', ['open', 'in_progress'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $myResolvedTickets = Ticket::with(['user', 'category'])
                ->where('assigned_to', $user->id)
                ->whereIn('status', ['resolved', 'closed'])
                ->orderBy('resolved_at', 'desc')
                ->limit(10)
                ->get();

            $myActiveTicketsCount = Ticket::where('assigned_to', $user->id)
                ->whereIn('status', ['open', 'in_progress'])
                ->count();

            $myResolvedTicketsCount = Ticket::where('assigned_to', $user->id)
                ->whereIn('status', ['resolved', 'closed'])
                ->count();

            // average resolution time (hours)
            $resolvedSet = Ticket::whereNotNull('resolved_at')->get(['created_at', 'resolved_at']);
            $totalHours = 0;
            $cnt = 0;
            foreach ($resolvedSet as $r) {
                if ($r->resolved_at) {
                    $totalHours += $r->created_at->diffInHours($r->resolved_at);
                    $cnt++;
                }
            }
            $avgResolutionHours = $cnt ? round($totalHours / $cnt, 1) : null;

            // tickets sample for inbox & my tickets
            $ticketsInbox = Ticket::with(['user', 'category'])
                ->whereNull('assigned_to')
                ->orderBy('created_at', 'desc')->limit(10)->get();

            $ticketsMy = Ticket::with(['user', 'category'])
                ->where('assigned_to', $user->id)
                ->orderBy('created_at', 'desc')->limit(10)->get();

            // trend last 14 days
            $days = [];
            $counts = [];
            for ($i = 13; $i >= 0; $i--) {
                $d = now()->subDays($i);
                $days[] = $d->format('d M');
                $counts[] = Ticket::whereDate('created_at', $d->toDateString())->count();
            }

            $itData = compact(
                'inQueueCount', 'myAssignedCount', 'inProgressCount', 'resolvedThisMonth',
                'avgResolutionHours', 'ticketsInbox', 'ticketsMy', 'days', 'counts',
                'myActiveTickets', 'myResolvedTickets', 'myActiveTicketsCount', 'myResolvedTicketsCount'
            );

            return view('dashboard.it', array_merge($data, $itData));
        }

        // ==================== REGULAR USER DASHBOARD ====================
        $myTicketsCount = Ticket::where('user_id', $user->id)->count();
        $myOpenCount = Ticket::where('user_id', $user->id)->whereIn('status', ['open', 'in_progress'])->count();
        $recentMyTickets = Ticket::with('category')->where('user_id', $user->id)->latest()->take(6)->get();

        $myTicketsChange = $this->calculateMyTicketsChange($user->id);
        $myOpenChange = $this->calculateMyOpenChange($user->id);

        $userData = compact('myTicketsCount', 'myOpenCount', 'recentMyTickets', 'myTicketsChange', 'myOpenChange');

        return view('dashboard.user', array_merge($data, $userData));
    }

    /**
     * Display notifications page for authenticated user
     */
    public function notifications(Request $request)
    {
        try {
            $user = $request->user();
            $notifications = $user->notifications()
                ->orderBy('created_at', 'desc')
                ->paginate(15);
            return view('dashboard.notifications', compact('notifications'));
        } catch (\Exception $e) {
            \Log::error('Error loading notifications page: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Gagal memuat halaman notifikasi');
        }
    }

    /**
     * Get notifications for dropdown (JSON)
     */
    public function getNotifications(Request $request)
    {
        try {
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
            return response()->json(['success' => true, 'notifications' => $notifications]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get notification count (JSON)
     */
    public function getNotificationCount(Request $request)
    {
        try {
            $user = $request->user();
            $count = $user->unreadNotifications()->count();
            return response()->json(['success' => true, 'count' => $count]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'count' => 0], 500);
        }
    }

    /**
     * Mark notification as read (JSON)
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            $user = $request->user();
            $notification = $user->notifications()->where('id', $id)->first();
            if ($notification) {
                $notification->markAsRead();
                return response()->json(['success' => true]);
            }
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark all notifications as read (JSON)
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $user = $request->user();
            $user->unreadNotifications->markAsRead();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ==================== HELPER METHODS ====================

    private function calculateTicketChange($type = 'total', $period = 'month')
    {
        $now = Carbon::now();
        if ($period === 'week') {
            $currentStart = $now->copy()->startOfWeek();
            $lastStart = $now->copy()->subWeek()->startOfWeek();
            $lastEnd = $now->copy()->subWeek()->endOfWeek();
            $currentQuery = Ticket::whereBetween('created_at', [$currentStart, $now]);
            $lastQuery = Ticket::whereBetween('created_at', [$lastStart, $lastEnd]);
        } else {
            $currentMonth = $now->month;
            $currentYear = $now->year;
            $lastMonth = $now->copy()->subMonth()->month;
            $lastYear = $now->copy()->subMonth()->year;
            $currentQuery = Ticket::whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear);
            $lastQuery = Ticket::whereMonth('created_at', $lastMonth)->whereYear('created_at', $lastYear);
        }
        switch($type) {
            case 'open':
                $currentQuery->whereIn('status', ['open', 'in_progress']);
                $lastQuery->whereIn('status', ['open', 'in_progress']);
                break;
            case 'resolved':
                $currentQuery->where('status', 'resolved');
                $lastQuery->where('status', 'resolved');
                break;
            case 'closed':
                $currentQuery->where('status', 'closed');
                $lastQuery->where('status', 'closed');
                break;
        }
        $currentCount = $currentQuery->count();
        $lastCount = $lastQuery->count();
        return $this->calculatePercentageChange($currentCount, $lastCount);
    }

    private function calculateUserChange()
    {
        $now = Carbon::now();
        $currentCount = User::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $lastCount = User::whereMonth('created_at', $now->copy()->subMonth()->month)->whereYear('created_at', $now->copy()->subMonth()->year)->count();
        return $this->calculatePercentageChange($currentCount, $lastCount);
    }

    private function calculateITStaffChange()
    {
        $now = Carbon::now();
        $currentCount = User::whereIn('role', ['it_staff', 'it'])->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $lastCount = User::whereIn('role', ['it_staff', 'it'])->whereMonth('created_at', $now->copy()->subMonth()->month)->whereYear('created_at', $now->copy()->subMonth()->year)->count();
        return $this->calculatePercentageChange($currentCount, $lastCount);
    }

    private function calculateUnassignedChange()
    {
        $now = Carbon::now();
        $currentCount = Ticket::whereNull('assigned_to')->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $lastCount = Ticket::whereNull('assigned_to')->whereMonth('created_at', $now->copy()->subMonth()->month)->whereYear('created_at', $now->copy()->subMonth()->year)->count();
        return $this->calculatePercentageChange($currentCount, $lastCount);
    }

    private function calculateInQueueChange()
    {
        $now = Carbon::now();
        $currentCount = Ticket::whereNull('assigned_to')->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $lastCount = Ticket::whereNull('assigned_to')->whereMonth('created_at', $now->copy()->subMonth()->month)->whereYear('created_at', $now->copy()->subMonth()->year)->count();
        return $this->calculatePercentageChange($currentCount, $lastCount);
    }

    private function calculateMyAssignedChange($userId)
    {
        $now = Carbon::now();
        $currentCount = Ticket::where('assigned_to', $userId)->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $lastCount = Ticket::where('assigned_to', $userId)->whereMonth('created_at', $now->copy()->subMonth()->month)->whereYear('created_at', $now->copy()->subMonth()->year)->count();
        return $this->calculatePercentageChange($currentCount, $lastCount);
    }

    private function calculateInProgressChange()
    {
        $now = Carbon::now();
        $currentCount = Ticket::where('status', 'in_progress')->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $lastCount = Ticket::where('status', 'in_progress')->whereMonth('created_at', $now->copy()->subMonth()->month)->whereYear('created_at', $now->copy()->subMonth()->year)->count();
        return $this->calculatePercentageChange($currentCount, $lastCount);
    }

    private function calculateResolvedMonthChange()
    {
        $now = Carbon::now();
        $currentCount = Ticket::where('status', 'resolved')->whereYear('resolved_at', $now->year)->whereMonth('resolved_at', $now->month)->count();
        $lastCount = Ticket::where('status', 'resolved')->whereYear('resolved_at', $now->copy()->subMonth()->year)->whereMonth('resolved_at', $now->copy()->subMonth()->month)->count();
        return $this->calculatePercentageChange($currentCount, $lastCount);
    }

    private function calculateMyTicketsChange($userId)
    {
        $now = Carbon::now();
        $currentCount = Ticket::where('user_id', $userId)->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $lastCount = Ticket::where('user_id', $userId)->whereMonth('created_at', $now->copy()->subMonth()->month)->whereYear('created_at', $now->copy()->subMonth()->year)->count();
        return $this->calculatePercentageChange($currentCount, $lastCount);
    }

    private function calculateMyOpenChange($userId)
    {
        $now = Carbon::now();
        $currentCount = Ticket::where('user_id', $userId)->whereIn('status', ['open', 'in_progress'])->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $lastCount = Ticket::where('user_id', $userId)->whereIn('status', ['open', 'in_progress'])->whereMonth('created_at', $now->copy()->subMonth()->month)->whereYear('created_at', $now->copy()->subMonth()->year)->count();
        return $this->calculatePercentageChange($currentCount, $lastCount);
    }

    private function calculatePercentageChange($current, $previous)
    {
        if ($previous > 0) {
            $change = (($current - $previous) / $previous) * 100;
        } else {
            $change = $current > 0 ? 100 : 0;
        }
        return [
            'value' => round($change, 1),
            'trend' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable'),
            'current' => $current,
            'previous' => $previous
        ];
    }
}
