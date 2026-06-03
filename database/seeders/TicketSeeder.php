<?php
namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketSeeder extends Seeder
{
    public function run()
    {
        DB::table('tickets')->delete();

        $users = User::all();
        $categories = Category::all();
        $itStaff = User::where('role', 'it_staff')->first();

        $tickets = [
            [
                'ticket_number' => Ticket::generateTicketNumber(),
                'title' => 'Printer tidak bisa print di ruangan IGD',
                'description' => 'Printer Epson L3150 di ruangan IGD tidak bisa print dari semua komputer. Sudah dicoba restart printer dan komputer tetap tidak bisa.',
                'user_id' => $users->where('department', 'IGD')->first()->id,
                'category_id' => $categories->where('name', 'Printer')->first()->id,
                'assigned_to' => $itStaff->id,
                'priority' => 'high',
                'status' => 'in_progress',
                'created_at' => now()->subDays(2),
            ],
            [
                'ticket_number' => Ticket::generateTicketNumber(),
                'title' => 'Koneksi WiFi lemah di poli umum',
                'description' => 'Koneksi WiFi sangat lemah dan sering putus di area poli umum. Sangat mengganggu saat akses data pasien.',
                'user_id' => $users->where('department', 'Poli Umum')->first()->id,
                'category_id' => $categories->where('name', 'Jaringan')->first()->id,
                'assigned_to' => $itStaff->id,
                'priority' => 'high',
                'status' => 'open',
                'created_at' => now()->subDays(1),
            ],
            [
                'ticket_number' => Ticket::generateTicketNumber(),
                'title' => 'Software HIS lambat loading',
                'description' => 'Aplikasi HIS sangat lambat saat membuka data pasien, terutama pada jam sibuk 08:00-11:00.',
                'user_id' => $users->where('department', 'Poli Umum')->first()->id,
                'category_id' => $categories->where('name', 'Sistem HIS')->first()->id,
                'assigned_to' => null,
                'priority' => 'medium',
                'status' => 'open',
                'created_at' => now()->subHours(6),
            ],
            [
                'ticket_number' => Ticket::generateTicketNumber(),
                'title' => 'Monitor komputer admin rusak',
                'description' => 'Monitor komputer di meja admin bagian pendaftaran muncul garis-garis dan berkedip.',
                'user_id' => $users->where('department', 'Poli Umum')->first()->id,
                'category_id' => $categories->where('name', 'Hardware')->first()->id,
                'assigned_to' => $itStaff->id,
                'priority' => 'medium',
                'status' => 'resolved',
                'resolved_at' => now()->subHours(2),
                'created_at' => now()->subDays(3),
            ],
            [
                'ticket_number' => Ticket::generateTicketNumber(),
                'title' => 'Email tidak bisa login',
                'description' => 'Tidak bisa login ke email rsintanhusada.com, password sudah dicoba reset tetap tidak bisa.',
                'user_id' => $users->where('department', 'IT Department')->first()->id,
                'category_id' => $categories->where('name', 'Email')->first()->id,
                'assigned_to' => $itStaff->id,
                'priority' => 'urgent',
                'status' => 'in_progress',
                'created_at' => now()->subHours(3),
            ],
            [
                'ticket_number' => Ticket::generateTicketNumber(),
                'title' => 'Minta install software baru',
                'description' => 'Butuh install software Adobe Acrobat Reader untuk buka file PDF manual peralatan medis.',
                'user_id' => $users->where('department', 'IGD')->first()->id,
                'category_id' => $categories->where('name', 'Software')->first()->id,
                'assigned_to' => null,
                'priority' => 'low',
                'status' => 'open',
                'created_at' => now()->subDays(1),
            ],
            [
                'ticket_number' => Ticket::generateTicketNumber(),
                'title' => 'Lupa password login komputer',
                'description' => 'Lupa password untuk login ke komputer di ruang dokter. Butuh reset password.',
                'user_id' => $users->where('department', 'Poli Umum')->first()->id,
                'category_id' => $categories->where('name', 'Akun & Password')->first()->id,
                'assigned_to' => $itStaff->id,
                'priority' => 'medium',
                'status' => 'resolved',
                'resolved_at' => now()->subHours(1),
                'created_at' => now()->subDays(1),
            ],
            [
                'ticket_number' => Ticket::generateTicketNumber(),
                'title' => 'Scanner tidak terdeteksi',
                'description' => 'Scanner Canon di ruang administrasi tidak terdeteksi oleh komputer. Sudah dicoba cabut dan pasang kabel USB.',
                'user_id' => $users->where('role', 'user')->first()->id,
                'category_id' => $categories->where('name', 'Hardware')->first()->id,
                'assigned_to' => $itStaff->id,
                'priority' => 'medium',
                'status' => 'closed',
                'resolved_at' => now()->subDays(1),
                'created_at' => now()->subDays(4),
            ]
        ];

        foreach ($tickets as $ticket) {
            Ticket::create($ticket);
        }

        $this->command->info('Tickets seeded successfully!');
    }
}
?>