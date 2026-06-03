<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->delete();

        $users = [
            [
                'name' => 'Admin IT',
                'email' => 'admin@rsintanhusada.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'department' => 'IT Department',
                'phone' => '081234567890',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'IT Support',
                'email' => 'itsupport@rsintanhusada.com',
                'password' => Hash::make('password123'),
                'role' => 'it_staff',
                'department' => 'IT Department',
                'phone' => '081234567891',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Dr. Sari',
                'email' => 'dokter@rsintanhusada.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'department' => 'Poli Umum',
                'phone' => '081234567892',
                'email_verified_at' => now(),
            ]
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        $this->command->info('Users seeded successfully!');
    }
}
?>