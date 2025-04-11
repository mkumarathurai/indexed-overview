<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tempo_hours', function (Blueprint $table) {
            $table->id();
            $table->string('project_key');
            $table->string('name')->nullable();
            $table->string('period');
            $table->integer('period_hours');
            $table->integer('invoice_ready_hours');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['project_key', 'period']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tempo_hours');
    }
}; 