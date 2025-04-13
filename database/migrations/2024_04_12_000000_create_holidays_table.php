<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->integer('year');
            $table->integer('total_days')->default(25); // Default 25 days per year
            $table->decimal('used_days', 5, 2)->default(0);
            $table->decimal('remaining_days', 5, 2)->default(25);
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('holidays');
    }
}; 