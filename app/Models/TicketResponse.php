<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketResponse extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'attachment',
        'is_internal',
        'is_resolution' // PASTIKAN INI ADA
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'is_resolution' => 'boolean'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if response is a resolution.
     */
    public function getIsResolutionResponseAttribute()
    {
        return $this->is_resolution;
    }
}
