<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('monthly_budgets', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->integer('month'); // 1-12
            $table->decimal('omsaetning_salg_total', 12, 2)->default(0);
            $table->decimal('udgift_variable_kapacitet', 12, 2)->default(0);
            $table->decimal('maal_baseret_paa_udgift', 12, 2)->default(0);
            $table->decimal('delmaal', 12, 2)->default(0);
            $table->timestamps();

            // Ensure unique combination of year and month
            $table->unique(['year', 'month']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('monthly_budgets');
    }
};