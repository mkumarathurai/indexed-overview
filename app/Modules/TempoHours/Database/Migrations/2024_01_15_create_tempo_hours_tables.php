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
            $table->string('account_id')->nullable();
            $table->string('project_key');
            $table->string('project_name')->nullable();
            $table->integer('period_hours');
            $table->integer('invoice_ready_hours');
            $table->string('period'); // Format: YYYY-MM
            $table->timestamp('last_synced_at');
            $table->timestamps();
            
            $table->unique(['project_key', 'period']);
        });

        Schema::create('tempo_worklogs', function (Blueprint $table) {
            $table->id();
            $table->string('tempo_worklog_id');
            $table->string('project_key');
            $table->string('issue_key');
            $table->text('description')->nullable();
            $table->integer('time_spent_seconds');
            $table->dateTime('started_at');
            $table->string('author_account_id');
            $table->boolean('is_invoice_ready')->default(false);
            $table->timestamps();

            $table->unique('tempo_worklog_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tempo_worklogs');
        Schema::dropIfExists('tempo_hours');
    }
};
