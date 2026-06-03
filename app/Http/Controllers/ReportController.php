<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display report page with role-based filtering
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $categoryId = $request->get('category_id');
        $status = $request->get('status');
        $userId = $request->get('user_id');
        $assignedTo = $request->get('assigned_to');

        // Base query for tickets
        $ticketQuery = Ticket::with(['user', 'category', 'assignedTo'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        // Base query for history
        $historyQuery = TicketHistory::with(['ticket', 'user'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        // Role-based filtering
        if ($user->role === 'admin') {
            // Admin sees everything - no additional filters by default
        } elseif (in_array($user->role, ['it_staff', 'it'])) {
            // IT staff sees tickets assigned to them or history they created
            $ticketQuery->where('assigned_to', $user->id);
            $historyQuery->where('user_id', $user->id);
        } else {
            // Regular user sees only their own tickets
            $ticketQuery->where('user_id', $user->id);
            $historyQuery->whereHas('ticket', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }

        // Apply filters
        if ($categoryId) {
            $ticketQuery->where('category_id', $categoryId);
            $historyQuery->whereHas('ticket', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            });
        }

        if ($status) {
            $ticketQuery->where('status', $status);
            $historyQuery->whereHas('ticket', function ($query) use ($status) {
                $query->where('status', $status);
            });
        }

        if ($userId && $user->role === 'admin') {
            $ticketQuery->where('user_id', $userId);
            $historyQuery->where('user_id', $userId);
        }

        if ($assignedTo && $user->role === 'admin') {
            $ticketQuery->where('assigned_to', $assignedTo);
        }

        // Get data
        $tickets = $ticketQuery->orderBy('created_at', 'desc')->paginate(20);
        $histories = $historyQuery->orderBy('created_at', 'desc')->paginate(20, ['*'], 'history_page');

        // Summary statistics
        $summary = $this->getSummaryStatistics($user, $startDate, $endDate);

        // Filter options for dropdowns (admin only)
        $categories = Category::all();
        $users = $user->role === 'admin' ? User::all() : collect();
        $itStaff = $user->role === 'admin' ? User::whereIn('role', ['it_staff', 'it'])->get() : collect();
        
        // Status options
        $statusOptions = [
            'open' => 'Open',
            'in_queue' => 'In Queue',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
            'closed' => 'Closed'
        ];

        return view('reports.index', compact(
            'tickets',
            'histories',
            'summary',
            'categories',
            'users',
            'itStaff',
            'statusOptions',
            'startDate',
            'endDate',
            'categoryId',
            'status',
            'userId',
            'assignedTo'
        ));
    }

    /**
     * Export report to PDF
     */
    public function exportPdf(Request $request)
    {
        // Similar logic as index but for PDF export
        // You can use dompdf or similar package
        // This is a placeholder
        return response()->json(['message' => 'PDF export feature coming soon']);
    }

    /**
     * Export report to Excel
     */
    public function exportExcel(Request $request)
    {
        // Similar logic as index but for Excel export
        // You can use Maatwebsite/Laravel-Excel package
        // This is a placeholder
        return response()->json(['message' => 'Excel export feature coming soon']);
    }

    /**
     * Get summary statistics
     */
    private function getSummaryStatistics($user, $startDate, $endDate)
    {
        $query = Ticket::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        // Role-based filtering
        if ($user->role === 'it_staff' || $user->role === 'it') {
            $query->where('assigned_to', $user->id);
        } elseif ($user->role === 'user') {
            $query->where('user_id', $user->id);
        }

        $totalTickets = $query->count();
        $openTickets = (clone $query)->whereIn('status', ['open', 'in_queue', 'in_progress'])->count();
        $resolvedTickets = (clone $query)->where('status', 'resolved')->count();
        $closedTickets = (clone $query)->where('status', 'closed')->count();
        
        // Average resolution time
        $resolvedQuery = (clone $query)->where('status', 'resolved')->whereNotNull('resolved_at');
        $resolvedSet = $resolvedQuery->get(['created_at', 'resolved_at']);
        
        $totalHours = 0;
        $cnt = 0;
        foreach ($resolvedSet as $ticket) {
            if ($ticket->resolved_at) {
                $totalHours += $ticket->created_at->diffInHours($ticket->resolved_at);
                $cnt++;
            }
        }
        $avgResolutionTime = $cnt > 0 ? round($totalHours / $cnt, 1) : 0;

        return [
            'total_tickets' => $totalTickets,
            'open_tickets' => $openTickets,
            'resolved_tickets' => $resolvedTickets,
            'closed_tickets' => $closedTickets,
            'resolution_rate' => $totalTickets > 0 ? round(($resolvedTickets + $closedTickets) / $totalTickets * 100, 1) : 0,
            'avg_resolution_time' => $avgResolutionTime
        ];
    }

    /**
     * Get detailed analytics data for charts
     */
    public function analytics(Request $request)
    {
        $user = $request->user();
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Monthly ticket data
        $monthlyData = Ticket::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status IN ("open", "in_queue", "in_progress") THEN 1 ELSE 0 END) as open'),
            DB::raw('SUM(CASE WHEN status = "resolved" THEN 1 ELSE 0 END) as resolved'),
            DB::raw('SUM(CASE WHEN status = "closed" THEN 1 ELSE 0 END) as closed')
        )
        ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        // Role-based filtering
        if ($user->role === 'it_staff' || $user->role === 'it') {
            $monthlyData->where('assigned_to', $user->id);
        } elseif ($user->role === 'user') {
            $monthlyData->where('user_id', $user->id);
        }

        $monthlyData = $monthlyData->groupBy('month')
            ->orderBy('month')
            ->get();

        // Category distribution
        $categoryData = Ticket::select(
            'categories.name',
            DB::raw('COUNT(tickets.id) as count')
        )
        ->join('categories', 'tickets.category_id', '=', 'categories.id')
        ->whereBetween('tickets.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        // Role-based filtering
        if ($user->role === 'it_staff' || $user->role === 'it') {
            $categoryData->where('tickets.assigned_to', $user->id);
        } elseif ($user->role === 'user') {
            $categoryData->where('tickets.user_id', $user->id);
        }

        $categoryData = $categoryData->groupBy('categories.name')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'monthly_data' => $monthlyData,
            'category_data' => $categoryData
        ]);
    }
}