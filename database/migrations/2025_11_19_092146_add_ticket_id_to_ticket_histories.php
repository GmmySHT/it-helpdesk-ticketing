<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ticket_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_histories', 'ticket_id')) {
                $table->unsignedBigInteger('ticket_id')->nullable()->after('id');
                $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('ticket_histories', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_histories', 'ticket_id')) {
                $table->dropForeign(['ticket_id']);
                $table->dropColumn('ticket_id');
            }
        });
    }
};
