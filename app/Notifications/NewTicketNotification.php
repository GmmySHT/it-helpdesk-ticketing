<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewTicketNotification extends Notification
{
    use Queueable;

    protected $ticket;
    protected $action;

    public function __construct(Ticket $ticket, $action = 'created')
    {
        $this->ticket = $ticket;
        $this->action = $action;
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
            'action' => $this->action,
            'message' => "Ticket baru #{$this->ticket->ticket_number} telah dibuat oleh {$this->ticket->user->name}",
            'created_by' => $this->ticket->user->name,
            'priority' => $this->ticket->priority,
            'category' => $this->ticket->category->name ?? '-',
            'url' => route('tickets.show', $this->ticket->id),
            'created_at' => now()->toDateTimeString(),
        ];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject("Ticket Baru: #{$this->ticket->ticket_number}")
            ->greeting("Halo {$notifiable->name}!")
            ->line("Ticket baru telah dibuat di sistem ticketing.")
            ->line("**No. Ticket:** {$this->ticket->ticket_number}")
            ->line("**Judul:** {$this->ticket->title}")
            ->line("**Prioritas:** " . ucfirst($this->ticket->priority))
            ->line("**Kategori:** " . ($this->ticket->category->name ?? '-'))
            ->line("**Dibuat oleh:** {$this->ticket->user->name}")
            ->action('Lihat Ticket', url("/tickets/{$this->ticket->id}"))
            ->line('Terima kasih telah menggunakan sistem ticketing.');

        return $mail;
    }
}
