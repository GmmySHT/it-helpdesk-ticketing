<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ticket_number',
        'title',
        'description',
        'user_id',
        'category_id',
        'assigned_to',
        'assigned_by',
        'assigned_at',
        'priority',
        'status',
        'resolved_at',
        'resolved_by', // Tambahkan ini
        'resolution_notes',
        'resolution_attachments',
        'sla_due_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'resolved_at' => 'datetime',
        'assigned_at' => 'datetime',
        'sla_due_at' => 'datetime',
        'resolution_attachments' => 'array',
    ];

    /**
     * Get the user that created the ticket.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that owns the ticket.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the IT staff assigned to the ticket.
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who resolved the ticket.
     */
    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Get the responses for the ticket.
     */
    public function responses()
    {
        return $this->hasMany(TicketResponse::class);
    }

    /**
     * Get the histories for the ticket.
     */
    public function histories()
    {
        return $this->hasMany(TicketHistory::class);
    }

    /**
     * Generate a unique ticket number.
     */
    public static function generateTicketNumber()
    {
        $date = now()->format('Ymd');
        $lastTicket = self::where('ticket_number', 'like', "TKT-{$date}-%")->latest()->first();

        $sequence = $lastTicket ? (int)substr($lastTicket->ticket_number, -4) + 1 : 1;

        return "TKT-{$date}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the priority in Indonesian.
     */
    public function getPriorityIndonesianAttribute()
    {
        $priorities = [
            'low' => 'Rendah',
            'medium' => 'Sedang',
            'high' => 'Tinggi',
            'urgent' => 'Darurat'
        ];

        return $priorities[$this->priority] ?? 'Sedang';
    }

    /**
     * Get the status in Indonesian.
     */
    public function getStatusIndonesianAttribute()
    {
        $statuses = [
            'open' => 'Terbuka',
            'in_queue' => 'Dalam Antrian',
            'in_progress' => 'Dalam Proses',
            'resolved' => 'Selesai',
            'closed' => 'Ditutup'
        ];

        return $statuses[$this->status] ?? 'Terbuka';
    }

    /**
     * Get the badge color for priority.
     */
    public function getPriorityBadgeAttribute()
    {
        $badges = [
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'urgent' => 'danger'
        ];

        return $badges[$this->priority] ?? 'secondary';
    }

    /**
     * Get the badge color for status.
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'open' => 'primary',
            'in_queue' => 'info',
            'in_progress' => 'warning',
            'resolved' => 'success',
            'closed' => 'secondary'
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    /**
     * Scope a query to only include open tickets.
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_queue', 'in_progress']);
    }

    /**
     * Scope a query to only include tickets assigned to me.
     */
    public function scopeAssignedToMe($query)
    {
        return $query->where('assigned_to', auth()->id());
    }

    /**
     * Scope a query to only include tickets by priority.
     */
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Check if ticket is open.
     */
    public function getIsOpenAttribute()
    {
        return in_array($this->status, ['open', 'in_queue', 'in_progress']);
    }

    /**
     * Check if ticket is assigned.
     */
    public function getIsAssignedAttribute()
    {
        return !is_null($this->assigned_to);
    }

    /**
     * Check if ticket is resolved.
     */
    public function getIsResolvedAttribute()
    {
        return $this->status === 'resolved';
    }

    /**
     * Get formatted resolution notes with metadata.
     */
    public function getFormattedResolutionAttribute()
    {
        if (!$this->resolution_notes) {
            return null;
        }

        return (object) [
            'notes' => $this->resolution_notes,
            'attachments' => $this->resolution_attachments,
            'resolved_at' => $this->resolved_at,
            'resolved_by' => $this->resolvedBy ? $this->resolvedBy->name : 'System',
            'has_attachments' => !empty($this->resolution_attachments)
        ];
    }

    /**
     * Check if ticket has resolution attachments.
     */
    public function getHasResolutionAttachmentsAttribute()
    {
        return !empty($this->resolution_attachments);
    }

    /**
     * Get resolution summary (short version).
     */
    public function getResolutionSummaryAttribute($length = 100)
    {
        if (!$this->resolution_notes) {
            return null;
        }

        return strlen($this->resolution_notes) > $length
            ? substr($this->resolution_notes, 0, $length) . '...'
            : $this->resolution_notes;
    }

    /**
     * Mark ticket as resolved with notes.
     */
    public function markAsResolved($notes, $attachments = null, $userId = null)
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => $userId ?? auth()->id(),
            'resolution_notes' => $notes,
            'resolution_attachments' => $attachments
        ]);

        // Create history entry
        TicketHistory::create([
            'ticket_id' => $this->id,
            'user_id' => $userId ?? auth()->id(),
            'action' => 'resolved',
            'notes' => 'Ticket resolved with notes',
            'meta' => json_encode([
                'resolution_notes' => substr($notes, 0, 200),
                'has_attachments' => !empty($attachments)
            ])
        ]);

        return true;
    }

    /**
     * Reopen a resolved ticket.
     */
    public function reopen()
    {
        $oldStatus = $this->status;

        $this->update([
            'status' => 'open',
            'resolved_at' => null,
            'resolved_by' => null,
            'resolution_notes' => null,
            'resolution_attachments' => null
        ]);

        TicketHistory::create([
            'ticket_id' => $this->id,
            'user_id' => auth()->id(),
            'action' => 'reopened',
            'notes' => "Ticket reopened from {$oldStatus} status",
            'meta' => json_encode(['old_status' => $oldStatus, 'new_status' => 'open'])
        ]);

        return true;
    }

    /**
     * Get response time in hours.
     */
    public function getResponseTimeAttribute()
    {
        if ($this->resolved_at && $this->created_at) {
            return $this->created_at->diffInHours($this->resolved_at);
        }
        return null;
    }

    /**
     * Get formatted response time.
     */
    public function getFormattedResponseTimeAttribute()
    {
        $hours = $this->response_time;
        if (!$hours) return '-';

        if ($hours < 24) {
            return $hours . ' jam';
        }

        $days = floor($hours / 24);
        $remainingHours = $hours % 24;

        if ($remainingHours > 0) {
            return $days . ' hari ' . $remainingHours . ' jam';
        }

        return $days . ' hari';
    }
}
