<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TicketAssigned extends Notification
{
    use Queueable;

    protected $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => $this->ticket->title,
            'action' => 'assigned',
            'message' => "Ticket #{$this->ticket->ticket_number} telah ditugaskan kepada Anda",
            'assigned_by' => auth()->user()->name ?? 'System',
            'priority' => $this->ticket->priority,
            'url' => route('tickets.show', $this->ticket->id),
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Ticket Ditugaskan: #{$this->ticket->ticket_number}")
            ->greeting("Halo {$notifiable->name}!")
            ->line("Sebuah ticket telah ditugaskan kepada Anda.")
            ->line("**No. Ticket:** {$this->ticket->ticket_number}")
            ->line("**Judul:** {$this->ticket->title}")
            ->line("**Prioritas:** " . ucfirst($this->ticket->priority))
            ->action('Lihat Ticket', url("/tickets/{$this->ticket->id}"))
            ->line('Harap segera ditindaklanjuti.');
    }
}
