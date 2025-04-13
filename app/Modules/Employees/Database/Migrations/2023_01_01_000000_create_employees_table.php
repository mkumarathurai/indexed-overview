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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('title')->nullable();
            $table->string('department')->nullable();
            $table->string('work_phone')->nullable();
            $table->string('private_phone')->nullable();
            $table->date('birthday')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('avatar')->nullable();
            $table->text('notes')->nullable();
            $table->string('external_id')->nullable();
            $table->string('external_url')->nullable();
            $table->string('external_source')->nullable();
            $table->string('external_group')->nullable();
            $table->string('status')->default('active');
            $table->string('type')->default('internal');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};