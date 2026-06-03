<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks for admin.
     */
    public function before(User $user, $ability)
    {
        // Admin has all permissions
        if ($user->role === 'admin') {
            return true;
        }
    }

    /**
     * Determine whether the user can view any tickets.
     */
    public function viewAny(User $user)
    {
        // All authenticated users can view tickets
        return in_array($user->role, ['admin', 'it_staff', 'it', 'user']);
    }

    /**
     * Determine whether the user can view the ticket.
     */
    public function view(User $user, Ticket $ticket)
    {
        // IT Staff/IT dapat melihat SEMUA ticket (read-only)
        if (in_array($user->role, ['it_staff', 'it'])) {
            return true; // Bisa melihat semua ticket
        }

        // Regular user can only view their own tickets
        return $ticket->user_id === $user->id;
    }

    /**
     * Determine whether the user can create tickets.
     */
    public function create(User $user)
    {
        // IT Staff/IT dapat membuat ticket
        return in_array($user->role, ['user', 'admin', 'it_staff', 'it']);
    }

    /**
     * Determine whether the user can update the ticket.
     */
    public function update(User $user, Ticket $ticket)
    {
        // IT Staff/IT TIDAK BISA update (read-only)
        if (in_array($user->role, ['it_staff', 'it'])) {
            return false; // Read-only, tidak bisa update
        }

        // Ticket owner can update if ticket is still open or in_progress
        if ($user->id === $ticket->user_id) {
            return in_array($ticket->status, ['open', 'in_progress']);
        }

        return false;
    }

    /**
     * Determine whether the user can assign the ticket.
     */
    public function assign(User $user, Ticket $ticket)
    {
        // Hanya admin yang bisa assign
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update status.
     */
    public function updateStatus(User $user, Ticket $ticket)
    {
        // Hanya admin yang bisa update status
        // IT Staff/IT TIDAK BISA update status
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can reopen the ticket.
     */
    public function reopen(User $user, Ticket $ticket)
    {
        // Hanya admin yang bisa reopen
        // IT Staff/IT TIDAK BISA reopen
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can take/self-assign the ticket.
     */
    public function take(User $user, Ticket $ticket)
    {
        // Hanya admin yang bisa take
        // IT Staff/IT TIDAK BISA take
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the ticket.
     */
    public function delete(User $user, Ticket $ticket)
    {
        // Only admin can delete tickets
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can add response to ticket.
     */
    public function addResponse(User $user, Ticket $ticket)
    {
        // Admin can add response
        if ($user->role === 'admin') {
            return true;
        }

        // IT Staff/IT TIDAK BISA add response (read-only)
        if (in_array($user->role, ['it_staff', 'it'])) {
            return false;
        }

        // User can add response to their own tickets
        if ($user->id === $ticket->user_id && !in_array($ticket->status, ['resolved', 'closed'])) {
            return true;
        }

        return false;
    }
}
