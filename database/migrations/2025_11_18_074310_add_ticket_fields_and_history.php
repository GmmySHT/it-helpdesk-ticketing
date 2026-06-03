<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {

            // Tambah kolom biasa
            $table->unsignedBigInteger('assigned_by')->nullable()->after('assigned_to');
            $table->timestamp('assigned_at')->nullable()->after('assigned_by');
            $table->string('source')->nullable()->after('status');
            $table->text('resolution_notes')->nullable()->after('resolved_at');
        });

        /**
         * Ubah ENUM status tanpa doctrine/dbal:
         * HARUS drop column lalu buat ulang
         */
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('status'); // drop dulu
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->enum('status', ['open','in_queue','in_progress','resolved','closed'])
                  ->default('open')
                  ->after('assigned_at'); // tambahkan di posisi yang kamu mau
        });

        // Buat tabel history
        Schema::create('ticket_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action'); // assigned, status_changed, priority_changed, response_added
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('ticket_id')
                  ->references('id')->on('tickets')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['assigned_by','assigned_at','source','resolution_notes']);
            $table->dropColumn('status');
            $table->enum('status', ['open'])->default('open'); // fallback
        });

        Schema::dropIfExists('ticket_histories');
    }
};
