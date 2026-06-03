<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// Hapus baris ini: use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable; // Hapus HasApiTokens dari sini

    /**
     * Nama tabel yang sesuai.
     */
    protected $table = 'users';

    /**
     * Kolom yang boleh diisi (mass assignment).
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'email_verified_at',
        'password',
        'role',
        'department',
        'phone',
        'remember_token',
    ];

    /**
     * Kolom yang akan disembunyikan dalam array/JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts untuk kolom tertentu.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Nilai default untuk atribut tertentu.
     */
    protected $attributes = [
        'role' => 'user',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Tickets yang dibuat oleh user ini
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    /**
     * Tickets yang ditugaskan ke user ini (untuk IT Staff)
     */
    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    /**
     * Tickets yang ditugaskan oleh user ini
     */
    public function assignedByTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_by');
    }

    /**
     * Tickets yang diselesaikan oleh user ini
     */
    public function resolvedTickets()
    {
        return $this->hasMany(Ticket::class, 'resolved_by');
    }

    /**
     * Tickets yang dibuka kembali oleh user ini
     */
    public function reopenedTickets()
    {
        return $this->hasMany(Ticket::class, 'reopened_by');
    }

    /**
     * History ticket oleh user ini
     */
    public function ticketHistories()
    {
        return $this->hasMany(TicketHistory::class, 'user_id');
    }

    /**
     * Response ticket oleh user ini
     */
    public function ticketResponses()
    {
        return $this->hasMany(TicketResponse::class, 'user_id');
    }

    // ==================== ROLE CHECK METHODS ====================

    /**
     * Mengecek apakah user memiliki role tertentu.
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Helper untuk admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Helper untuk user biasa
     */
    public function isUser()
    {
        return $this->role === 'user';
    }

    /**
     * Helper untuk tim IT (it_staff)
     */
    public function isIT()
    {
        return $this->role === 'it_staff';
    }

    /**
     * Helper untuk IT Staff
     */
    public function isItStaff()
    {
        return $this->role === 'it_staff';
    }
}
