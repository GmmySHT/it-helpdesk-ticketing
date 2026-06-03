<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TicketResolved extends Notification
{
    use Queueable;

    protected $ticket;
    protected $resolvedBy;

    public function __construct(Ticket $ticket, $resolvedBy)
    {
        $this->ticket = $ticket;
        $this->resolvedBy = $resolvedBy;
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
            'action' => 'resolved',
            'message' => "Ticket #{$this->ticket->ticket_number} telah diselesaikan oleh {$this->resolvedBy->name}",
            'resolved_by' => $this->resolvedBy->name,
            'url' => route('tickets.show', $this->ticket->id),
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Ticket Selesai: #{$this->ticket->ticket_number}")
            ->greeting("Halo {$notifiable->name}!")
            ->line("Ticket Anda telah diselesaikan.")
            ->line("**No. Ticket:** {$this->ticket->ticket_number}")
            ->line("**Judul:** {$this->ticket->title}")
            ->action('Lihat Ticket', url("/tickets/{$this->ticket->id}"))
            ->line('Terima kasih telah menggunakan sistem ticketing.');
    }
}
