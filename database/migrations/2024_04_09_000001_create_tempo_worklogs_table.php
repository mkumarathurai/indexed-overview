<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tempo_worklogs', function (Blueprint $table) {
            $table->id();
            $table->string('tempo_worklog_id')->unique();
            $table->string('issue_key');
            $table->string('project_key');
            $table->timestamp('started_at');
            $table->integer('time_spent_seconds');
            $table->integer('billable_seconds');
            $table->string('author_account_id');
            $table->text('description')->nullable();
            $table->boolean('is_invoice_ready')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index('project_key');
            $table->index('issue_key');
            $table->index('started_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tempo_worklogs');
    }
}; 