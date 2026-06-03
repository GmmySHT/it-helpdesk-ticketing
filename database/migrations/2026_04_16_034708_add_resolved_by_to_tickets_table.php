<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Cek apakah kolom resolved_by sudah ada
            if (!Schema::hasColumn('tickets', 'resolved_by')) {
                $table->foreignId('resolved_by')
                    ->nullable()
                    ->after('resolved_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            // Cek apakah kolom resolution_notes sudah ada
            if (!Schema::hasColumn('tickets', 'resolution_notes')) {
                $table->text('resolution_notes')->nullable()->after('resolved_at');
            }

            // Cek apakah kolom resolution_attachments sudah ada
            if (!Schema::hasColumn('tickets', 'resolution_attachments')) {
                $table->json('resolution_attachments')->nullable()->after('resolution_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Hapus foreign key terlebih dahulu
            if (Schema::hasColumn('tickets', 'resolved_by')) {
                $table->dropForeign(['resolved_by']);
                $table->dropColumn('resolved_by');
            }

            if (Schema::hasColumn('tickets', 'resolution_notes')) {
                $table->dropColumn('resolution_notes');
            }

            if (Schema::hasColumn('tickets', 'resolution_attachments')) {
                $table->dropColumn('resolution_attachments');
            }
        });
    }
};
