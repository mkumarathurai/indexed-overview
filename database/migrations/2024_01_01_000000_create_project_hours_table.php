<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('project_hours', function (Blueprint $table) {
            $table->id();
            $table->string('project_key');
            $table->string('period'); // Format: YYYY-MM
            $table->decimal('monthly_hours', 10, 2)->default(0);
            $table->decimal('invoice_ready_hours', 10, 2)->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            
            $table->unique(['project_key', 'period']);
        });
    }
};
