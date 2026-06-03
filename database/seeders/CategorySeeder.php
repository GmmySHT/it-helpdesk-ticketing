<?php
namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run()
    {
        DB::table('categories')->delete();

        $categories = [
            ['name' => 'Hardware', 'description' => 'Masalah hardware komputer'],
            ['name' => 'Software', 'description' => 'Masalah software dan aplikasi'],
            ['name' => 'Jaringan', 'description' => 'Masalah koneksi jaringan'],
            ['name' => 'Printer', 'description' => 'Masalah printer dan scanning'],
            ['name' => 'Email', 'description' => 'Masalah email dan komunikasi'],
            ['name' => 'Lainnya', 'description' => 'Permasalahan lainnya'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        $this->command->info('Categories seeded successfully!');
    }
}
?>