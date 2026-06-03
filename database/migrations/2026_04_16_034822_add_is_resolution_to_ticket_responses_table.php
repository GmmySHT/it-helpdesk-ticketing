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
        Schema::table('ticket_responses', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_responses', 'is_resolution')) {
                $table->boolean('is_resolution')->default(false)->after('is_internal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_responses', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_responses', 'is_resolution')) {
                $table->dropColumn('is_resolution');
            }
        });
    }
};
