<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TicketStatusUpdate extends Notification
{
    use Queueable;

    protected $ticket;
    protected $status;
    protected $updatedBy;
    protected $reason;

    public function __construct(Ticket $ticket, $status, $updatedBy, $reason = null)
    {
        $this->ticket = $ticket;
        $this->status = $status;
        $this->updatedBy = $updatedBy;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        $statusMessages = [
            'assigned' => "Ticket #{$this->ticket->ticket_number} telah ditugaskan ke {$this->updatedBy}",
            'taken' => "Ticket #{$this->ticket->ticket_number} telah diambil oleh {$this->updatedBy}",
            'resolved' => "Ticket #{$this->ticket->ticket_number} telah diselesaikan oleh {$this->updatedBy}",
            'in_progress' => "Ticket #{$this->ticket->ticket_number} sedang diproses oleh {$this->updatedBy}",
            'closed' => "Ticket #{$this->ticket->ticket_number} telah ditutup oleh {$this->updatedBy}",
            'reopened' => "Ticket #{$this->ticket->ticket_number} dibuka kembali oleh {$this->updatedBy}" . ($this->reason ? " dengan alasan: {$this->reason}" : ""),
        ];

        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => $this->ticket->title,
            'status' => $this->status,
            'message' => $statusMessages[$this->status] ?? "Status ticket #{$this->ticket->ticket_number} telah diperbarui",
            'updated_by' => $this->updatedBy,
            'reason' => $this->reason,
            'url' => route('tickets.show', $this->ticket->id),
            'updated_at' => now()->toDateTimeString(),
        ];
    }

    public function toMail($notifiable)
    {
        $statusText = [
            'assigned' => 'Ditugaskan',
            'taken' => 'Diambil',
            'resolved' => 'Diselesaikan',
            'in_progress' => 'Sedang Diproses',
            'closed' => 'Ditutup',
            'reopened' => 'Dibuka Kembali',
        ];

        $mail = (new MailMessage)
            ->subject("Ticket {$statusText[$this->status]}: #{$this->ticket->ticket_number}")
            ->greeting("Halo {$notifiable->name}!")
            ->line("Ticket #{$this->ticket->ticket_number} telah {$statusText[$this->status]} oleh {$this->updatedBy}.")
            ->line("**Judul:** {$this->ticket->title}");

        // Perbaikan: gunakan if statement biasa
        if ($this->reason) {
            $mail->line("**Alasan:** {$this->reason}");
        }

        $mail->action('Lihat Ticket', url("/tickets/{$this->ticket->id}"))
             ->line('Terima kasih telah menggunakan sistem ticketing.');

        return $mail;
    }
}
