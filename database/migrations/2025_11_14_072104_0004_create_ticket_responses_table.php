<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ticket_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id'); // TANPA foreign key dulu
            $table->unsignedBigInteger('user_id'); // TANPA foreign key dulu
            $table->text('message');
            $table->string('attachment')->nullable();
            $table->boolean('is_internal')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ticket_responses');
    }
};
?>