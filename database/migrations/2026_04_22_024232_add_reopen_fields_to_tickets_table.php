<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('reopened_at')->nullable();
            $table->unsignedBigInteger('reopened_by')->nullable();
            $table->text('reopen_reason')->nullable();
            $table->integer('reopen_count')->default(0);

            $table->foreign('reopened_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['reopened_by']);
            $table->dropColumn(['reopened_at', 'reopened_by', 'reopen_reason', 'reopen_count']);
        });
    }
};
