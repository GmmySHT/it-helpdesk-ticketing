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
     * Display report page with role-based filtering.
     */
    public function index(Request $request)
    {
        $user      = $request->user();
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate   = $request->get('end_date',   now()->format('Y-m-d'));
        $categoryId = $request->get('category_id');
        $status     = $request->get('status');
        $userId     = $request->get('user_id');
        $assignedTo = $request->get('assigned_to');

        // ── Ticket query ───────────────────────────────────────
        $ticketQuery = Ticket::with(['user', 'category', 'assignedTo'])
            ->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate   . ' 23:59:59',
            ]);

        // ── History query ──────────────────────────────────────
        $historyQuery = TicketHistory::with(['ticket', 'user'])
            ->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate   . ' 23:59:59',
            ]);

        // ── Role-based scoping ─────────────────────────────────
        $this->applyRoleScope($user, $ticketQuery, $historyQuery);

        // ── Optional filters ───────────────────────────────────
        if ($categoryId) {
            $ticketQuery->where('category_id', $categoryId);
            $historyQuery->whereHas('ticket', fn ($q) => $q->where('category_id', $categoryId));
        }

        if ($status) {
            $ticketQuery->where('status', $status);
            $historyQuery->whereHas('ticket', fn ($q) => $q->where('status', $status));
        }

        if ($userId && $user->role === 'admin') {
            $ticketQuery->where('user_id', $userId);
            $historyQuery->where('user_id', $userId);
        }

        if ($assignedTo && $user->role === 'admin') {
            $ticketQuery->where('assigned_to', $assignedTo);
            $historyQuery->whereHas('ticket', fn ($q) => $q->where('assigned_to', $assignedTo));
        }

        // ── Paginate ───────────────────────────────────────────
        $tickets   = (clone $ticketQuery)->orderByDesc('created_at')->paginate(20);
        $histories = (clone $historyQuery)->orderByDesc('created_at')->paginate(20, ['*'], 'history_page');

        // ── Summary with delta ─────────────────────────────────
        $summary = $this->getSummaryStatistics(
            $user, $startDate, $endDate,
            $categoryId, $status, $userId, $assignedTo
        );

        // ── Dropdown data ──────────────────────────────────────
        $categories  = Category::orderBy('name')->get();
        $users       = $user->role === 'admin' ? User::orderBy('name')->get() : collect();
        $itStaff     = $user->role === 'admin'
            ? User::whereIn('role', ['it_staff', 'it'])->orderBy('name')->get()
            : collect();

        $statusOptions = [
            'open'        => 'Open',
            'in_queue'    => 'In Queue',
            'in_progress' => 'In Progress',
            'resolved'    => 'Resolved',
            'closed'      => 'Closed',
        ];

        return view('reports.index', compact(
            'tickets', 'histories', 'summary',
            'categories', 'users', 'itStaff', 'statusOptions',
            'startDate', 'endDate',
            'categoryId', 'status', 'userId', 'assignedTo'
        ));
    }

    // ══════════════════════════════════════════════════════════
    //  ANALYTICS  (JSON endpoint for Chart.js)
    // ══════════════════════════════════════════════════════════

    /**
     * Return chart data.
     *
     * Monthly trend always covers the last 6 full calendar months
     * regardless of the date-filter in the view — that way the
     * trend line makes visual sense even when the user narrows
     * the table to a single week.
     */
    public function analytics(Request $request)
    {
        $user = $request->user();

        // ── 6-month window (always fixed to last 6 months) ────
        $chartEnd   = now()->endOfMonth();
        $chartStart = now()->subMonths(5)->startOfMonth();

        // ── Monthly data ───────────────────────────────────────
        $monthlyQuery = Ticket::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month_key'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status IN ("open","in_queue","in_progress") THEN 1 ELSE 0 END) as open'),
            DB::raw('SUM(CASE WHEN status = "resolved" THEN 1 ELSE 0 END) as resolved'),
            DB::raw('SUM(CASE WHEN status = "closed"   THEN 1 ELSE 0 END) as closed')
        )
        ->whereBetween('created_at', [$chartStart, $chartEnd]);

        $this->applyRoleScope($user, $monthlyQuery);

        $rawMonthly = $monthlyQuery
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->get();

        // Build a complete 6-month series (fill gaps with zeros)
        $monthlyData = collect();
        for ($i = 5; $i >= 0; $i--) {
            $key  = now()->subMonths($i)->format('Y-m');
            $label = Carbon::createFromFormat('Y-m', $key)->translatedFormat('M Y');
            $row  = $rawMonthly->firstWhere('month_key', $key);
            $monthlyData->push([
                'month'    => $label,
                'total'    => $row?->total    ?? 0,
                'open'     => $row?->open     ?? 0,
                'resolved' => $row?->resolved ?? 0,
                'closed'   => $row?->closed   ?? 0,
            ]);
        }

        // ── Category distribution (respects view filters) ─────
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate   = $request->get('end_date',   now()->format('Y-m-d'));

        $categoryQuery = Ticket::select(
            'categories.name',
            DB::raw('COUNT(tickets.id) as count')
        )
        ->join('categories', 'tickets.category_id', '=', 'categories.id')
        ->whereBetween('tickets.created_at', [
            $startDate . ' 00:00:00',
            $endDate   . ' 23:59:59',
        ]);

        $this->applyRoleScope($user, $categoryQuery);

        $categoryData = $categoryQuery
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'monthly_data'  => $monthlyData,
            'category_data' => $categoryData,
        ]);
    }

    // ══════════════════════════════════════════════════════════
    //  EXPORT stubs
    // ══════════════════════════════════════════════════════════

    public function exportPdf(Request $request)
    {
        // TODO: implement with barryvdh/laravel-dompdf
        return response()->json(['message' => 'PDF export coming soon']);
    }

    public function exportExcel(Request $request)
    {
        // TODO: implement with Maatwebsite/Laravel-Excel
        return response()->json(['message' => 'Excel export coming soon']);
    }

    // ══════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════

    /**
     * Apply role-based WHERE constraints to one or more query builders.
     * Pass $ticketQuery only, or both $ticketQuery + $historyQuery.
     */
    private function applyRoleScope($user, &$ticketQuery, &$historyQuery = null): void
    {
        if (in_array($user->role, ['it_staff', 'it'])) {
            $ticketQuery->where('assigned_to', $user->id);
            if ($historyQuery) {
                $historyQuery->where('user_id', $user->id);
            }
        } elseif ($user->role === 'user') {
            $ticketQuery->where('user_id', $user->id);
            if ($historyQuery) {
                $historyQuery->whereHas('ticket', fn ($q) => $q->where('user_id', $user->id));
            }
        }
        // admin: no additional constraints
    }

    /**
     * Summary statistics for the current period AND the previous
     * equal-length period (used for delta indicators).
     */
    private function getSummaryStatistics(
        $user,
        string $startDate,
        string $endDate,
        ?string $categoryId = null,
        ?string $status     = null,
        ?string $userId     = null,
        ?string $assignedTo = null
    ): array {
        // ── Current period ─────────────────────────────────────
        $current = $this->buildSummaryQuery(
            $user, $startDate, $endDate,
            $categoryId, $status, $userId, $assignedTo
        );

        // ── Previous period (same length, immediately before) ──
        $start  = Carbon::parse($startDate);
        $end    = Carbon::parse($endDate);
        $length = $start->diffInDays($end) + 1;         // e.g. 31 days

        $prevEnd   = $start->copy()->subDay()->format('Y-m-d');
        $prevStart = Carbon::parse($prevEnd)->subDays($length - 1)->format('Y-m-d');

        $previous = $this->buildSummaryQuery(
            $user, $prevStart, $prevEnd,
            $categoryId, $status, $userId, $assignedTo
        );

        // ── Average resolution time (current period) ───────────
        $resolvedSet = (clone $current['base'])
            ->where('status', 'resolved')
            ->whereNotNull('resolved_at')
            ->get(['created_at', 'resolved_at']);

        $totalHours = 0;
        $cnt        = 0;
        foreach ($resolvedSet as $ticket) {
            $totalHours += $ticket->created_at->diffInHours($ticket->resolved_at);
            $cnt++;
        }
        $avgResolutionHours = $cnt > 0 ? round($totalHours / $cnt, 1) : 0;

        // ── Helper: percentage delta ───────────────────────────
        $delta = function (int $now, int $prev): ?float {
            if ($prev === 0) return null;
            return round((($now - $prev) / $prev) * 100, 1);
        };

        $totalNow      = $current['total'];
        $openNow       = $current['open'];
        $resolvedNow   = $current['resolved'];
        $closedNow     = $current['closed'];

        $totalPrev     = $previous['total'];
        $openPrev      = $previous['open'];
        $resolvedPrev  = $previous['resolved'];
        $closedPrev    = $previous['closed'];

        return [
            // Core counts
            'total_tickets'      => $totalNow,
            'open_tickets'       => $openNow,
            'resolved_tickets'   => $resolvedNow,
            'closed_tickets'     => $closedNow,
            'avg_resolution_time' => $avgResolutionHours,

            // Resolution rate
            'resolution_rate' => $totalNow > 0
                ? round(($resolvedNow + $closedNow) / $totalNow * 100, 1)
                : 0,

            // Delta vs previous period (null = no previous data)
            'total_delta'    => $delta($totalNow,    $totalPrev),
            'open_delta'     => $openNow - $openPrev,   // absolute diff for open tickets
            'resolved_delta' => $delta($resolvedNow, $resolvedPrev),

            // Previous period raw (useful for tooltips if needed)
            'prev_total'    => $totalPrev,
            'prev_open'     => $openPrev,
            'prev_resolved' => $resolvedPrev,
        ];
    }

    /**
     * Build the base query and return counts + the base builder.
     */
    private function buildSummaryQuery(
        $user,
        string $startDate,
        string $endDate,
        ?string $categoryId,
        ?string $status,
        ?string $userId,
        ?string $assignedTo
    ): array {
        $q = Ticket::whereBetween('created_at', [
            $startDate . ' 00:00:00',
            $endDate   . ' 23:59:59',
        ]);

        $this->applyRoleScope($user, $q);

        if ($categoryId) $q->where('category_id', $categoryId);
        if ($status)     $q->where('status',      $status);

        if ($userId     && $user->role === 'admin') $q->where('user_id',     $userId);
        if ($assignedTo && $user->role === 'admin') $q->where('assigned_to', $assignedTo);

        $total    = (clone $q)->count();
        $open     = (clone $q)->whereIn('status', ['open', 'in_queue', 'in_progress'])->count();
        $resolved = (clone $q)->where('status', 'resolved')->count();
        $closed   = (clone $q)->where('status', 'closed')->count();

        return compact('total', 'open', 'resolved', 'closed') + ['base' => $q];
    }
}
