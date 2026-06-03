<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            // tambahkan kolom baru
            if (!Schema::hasColumn('tickets', 'assigned_by')) {
                $table->unsignedBigInteger('assigned_by')->nullable()->after('assigned_to');
            }

            if (!Schema::hasColumn('tickets', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('assigned_by');
            }

            if (!Schema::hasColumn('tickets', 'sla_due_at')) {
                $table->timestamp('sla_due_at')->nullable()->after('assigned_at');
            }

            if (!Schema::hasColumn('tickets', 'source')) {
                $table->string('source')->nullable()->after('status');
            }

            if (!Schema::hasColumn('tickets', 'resolution_notes')) {
                $table->text('resolution_notes')->nullable()->after('resolved_at');
            }
        });

        // FK assigned_by
        Schema::table('tickets', function (Blueprint $table) {
            try {
                $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
            } catch (\Throwable $e) {}
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            try {
                $table->dropForeign(['assigned_by']);
            } catch (\Throwable $e) {}

            if (Schema::hasColumn('tickets', 'assigned_by')) $table->dropColumn('assigned_by');
            if (Schema::hasColumn('tickets', 'assigned_at')) $table->dropColumn('assigned_at');
            if (Schema::hasColumn('tickets', 'sla_due_at')) $table->dropColumn('sla_due_at');
            if (Schema::hasColumn('tickets', 'source')) $table->dropColumn('source');
            if (Schema::hasColumn('tickets', 'resolution_notes')) $table->dropColumn('resolution_notes');
        });
    }
};
