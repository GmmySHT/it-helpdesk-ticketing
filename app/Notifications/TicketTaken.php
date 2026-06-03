<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TicketTaken extends Notification
{
    use Queueable;

    protected $ticket;
    protected $takenBy;

    public function __construct(Ticket $ticket, $takenBy)
    {
        $this->ticket = $ticket;
        $this->takenBy = $takenBy;
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
            'action' => 'taken',
            'message' => "Ticket #{$this->ticket->ticket_number} telah diambil oleh {$this->takenBy->name}",
            'taken_by' => $this->takenBy->name,
            'url' => route('tickets.show', $this->ticket->id),
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Ticket Diambil: #{$this->ticket->ticket_number}")
            ->greeting("Halo {$notifiable->name}!")
            ->line("Ticket #{$this->ticket->ticket_number} telah diambil oleh {$this->takenBy->name}.")
            ->line("**Judul:** {$this->ticket->title}")
            ->action('Lihat Ticket', url("/tickets/{$this->ticket->id}"))
            ->line('Ticket sedang dalam proses penanganan.');
    }
}
