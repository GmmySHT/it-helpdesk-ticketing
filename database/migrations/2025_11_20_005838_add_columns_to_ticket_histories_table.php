<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tambah kolom yang hilang sesuai model
        Schema::table('ticket_histories', function (Blueprint $table) {
            if (! Schema::hasColumn('ticket_histories', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('ticket_id');
            }
            if (! Schema::hasColumn('ticket_histories', 'action')) {
                $table->string('action')->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('ticket_histories', 'notes')) {
                $table->text('notes')->nullable()->after('action');
            }
            if (! Schema::hasColumn('ticket_histories', 'meta')) {
                $table->json('meta')->nullable()->after('notes');
            }
        });

        // Tambah foreign key (lakukan terpisah supaya lebih aman saat ada index/constraint)
        Schema::table('ticket_histories', function (Blueprint $table) {
            // Pastikan index sebelum menambah foreign key
            if (Schema::hasColumn('ticket_histories', 'ticket_id')) {
                $table->unsignedBigInteger('ticket_id')->change(); // memastikan tipe cocok (butuh doctrine untuk change(); kalau DB sudah BIGINT maka abaikan)
            }
            // menambahkan FK jika belum ada - Laravel tidak mudah cek nama fk, kita coba tambahkan; jika error FK sudah ada maka rollback akan menampilkan pesan
            try {
                $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            } catch (\Exception $e) {
                // ignore if FK cannot be added (already exists)
            }

            if (Schema::hasColumn('ticket_histories', 'user_id')) {
                try {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                } catch (\Exception $e) {
                    // ignore
                }
            }
        });
    }

    public function down()
    {
        Schema::table('ticket_histories', function (Blueprint $table) {
            // drop foreigns if exist
            try { $table->dropForeign(['ticket_id']); } catch (\Exception $e) {}
            try { $table->dropForeign(['user_id']); } catch (\Exception $e) {}

            if (Schema::hasColumn('ticket_histories', 'meta')) {
                $table->dropColumn('meta');
            }
            if (Schema::hasColumn('ticket_histories', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('ticket_histories', 'action')) {
                $table->dropColumn('action');
            }
            if (Schema::hasColumn('ticket_histories', 'user_id')) {
                $table->dropColumn('user_id');
            }
        });
    }
};
