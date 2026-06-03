<?php
namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketResponse;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketResponseSeeder extends Seeder
{
    public function run()
    {
        DB::table('ticket_responses')->delete();

        $tickets = Ticket::all();
        $users = User::all();
        $itStaff = User::where('role', 'it_staff')->first();

        foreach ($tickets as $ticket) {
            // Response dari user pembuat ticket
            TicketResponse::create([
                'ticket_id' => $ticket->id,
                'user_id' => $ticket->user_id,
                'message' => $this->getUserMessageBasedOnCategory($ticket->category->name),
                'created_at' => $ticket->created_at,
            ]);

            // Jika ticket sudah diassign, tambahkan response dari IT
            if ($ticket->assigned_to) {
                TicketResponse::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $ticket->assigned_to,
                    'message' => $this->getITResponseBasedOnStatus($ticket->status, $ticket->priority),
                    'is_internal' => false,
                    'created_at' => $ticket->created_at->addMinutes(30),
                ]);

                // Internal note untuk IT team
                if (in_array($ticket->status, ['in_progress', 'resolved'])) {
                    TicketResponse::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => $ticket->assigned_to,
                        'message' => $this->getInternalNote($ticket->category->name),
                        'is_internal' => true,
                        'created_at' => $ticket->created_at->addHours(1),
                    ]);
                }
            }

            // Tambahkan response closing jika status resolved/closed
            if (in_array($ticket->status, ['resolved', 'closed'])) {
                TicketResponse::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $ticket->assigned_to ?: $itStaff->id,
                    'message' => 'Masalah sudah diselesaikan. Silakan konfirmasi jika masih ada kendala.',
                    'is_internal' => false,
                    'created_at' => $ticket->resolved_at ?: $ticket->created_at->addDays(1),
                ]);
            }
        }

        $this->command->info('Ticket responses seeded successfully!');
    }

    private function getUserMessageBasedOnCategory($category)
    {
        $messages = [
            'Hardware' => 'Perangkat tidak berfungsi dengan normal. Sudah dicoba restart tetapi tetap sama.',
            'Software' => 'Aplikasi error/tidak bisa dibuka. Pesan error: ...',
            'Jaringan' => 'Koneksi internet terputus-putus atau sangat lambat.',
            'Printer' => 'Tidak bisa print dokumen. Printer tidak terdeteksi.',
            'Email' => 'Tidak bisa login/send email. Butuh bantuan segera.',
            'Sistem HIS' => 'Sistem lambat/error saat akses data pasien.',
            'Akun & Password' => 'Lupa password/akun terkunci.',
            'Lainnya' => 'Mohon bantuan untuk masalah ini.',
        ];

        return $messages[$category] ?? 'Mohon bantuan untuk masalah ini.';
    }

    private function getITResponseBasedOnStatus($status, $priority)
    {
        if ($status === 'in_progress') {
            return "Ticket sedang dalam penanganan. Prioritas: " . $this->getPriorityText($priority);
        }

        if ($status === 'resolved') {
            return "Masalah telah diperbaiki. Silakan verifikasi.";
        }

        return "Terima kasih telah melaporkan masalah. Tim IT akan segera menindaklanjuti.";
    }

    private function getInternalNote($category)
    {
        $notes = [
            'Hardware' => 'Perlu pengecekan fisik dan kemungkinan penggantian perangkat.',
            'Software' => 'Butuh update/reinstall aplikasi. Cek compatibility dengan OS.',
            'Jaringan' => 'Monitor jaringan dan cek access point. Kemungkinan perlu tambahan AP.',
            'Printer' => 'Cek koneksi, driver, dan status printer. Kemungkinan perlu ganti toner.',
            'Email' => 'Cek server email dan configuration. Reset password jika diperlukan.',
            'Sistem HIS' => 'Monitor performance database. Cek log error aplikasi.',
            'Akun & Password' => 'Reset password dan verifikasi hak akses user.',
            'Lainnya' => 'Butuh analisis lebih lanjut untuk masalah ini.',
        ];

        return $notes[$category] ?? 'Butuh investigasi lebih lanjut.';
    }

    private function getPriorityText($priority)
    {
        $priorities = [
            'low' => 'Rendah',
            'medium' => 'Sedang', 
            'high' => 'Tinggi',
            'urgent' => 'Darurat'
        ];

        return $priorities[$priority] ?? 'Sedang';
    }
}
?>