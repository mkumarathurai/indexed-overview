<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month');
            $table->decimal('omsaetning_salg_total', 10, 2)->default(0);
            $table->decimal('udgift_variable_kapacitet', 10, 2)->default(0);
            $table->decimal('maal_baseret_paa_udgift', 10, 2)->default(0);
            $table->decimal('delmaal', 10, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['year', 'month']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('budgets');
    }
};
