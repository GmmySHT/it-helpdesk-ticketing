<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TicketReopened extends Notification
{
    use Queueable;

    protected $ticket;
    protected $reopenedBy;

    public function __construct(Ticket $ticket, $reopenedBy)
    {
        $this->ticket = $ticket;
        $this->reopenedBy = $reopenedBy;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable)
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => $this->ticket->title,
            'message' => "Ticket #{$this->ticket->ticket_number} dibuka kembali oleh {$this->reopenedBy->name}",
            'reopened_by' => $this->reopenedBy->name,
            'reopened_at' => now()->toDateTimeString(),
            'reason' => $this->ticket->reopen_reason,
            'reopen_count' => $this->ticket->reopen_count
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Ticket Reopened: #{$this->ticket->ticket_number}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Ticket #{$this->ticket->ticket_number} has been reopened by {$this->reopenedBy->name}.")
            ->line("**Title:** {$this->ticket->title}")
            ->line("**Reason:** {$this->ticket->reopen_reason}")
            ->line("**Reopen Count:** {$this->ticket->reopen_count}")
            ->action('View Ticket', url("/tickets/{$this->ticket->id}"))
            ->line('Please review and address the ticket again.');
    }
}
