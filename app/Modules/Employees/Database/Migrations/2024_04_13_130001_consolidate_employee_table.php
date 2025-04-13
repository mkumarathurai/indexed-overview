<?php

namespace App\Modules\Employees\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->nullable()->index();
                $table->string('title')->nullable();
                $table->string('jira_account_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->enum('type', ['internal', 'external'])->default('internal');
                $table->boolean('status')->default(true);
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->string('department')->nullable();
                $table->string('work_phone')->nullable();
                $table->string('private_phone')->nullable();
                $table->date('birthday')->nullable();
                $table->string('avatar')->nullable();
                $table->text('notes')->nullable();
                $table->string('external_id')->nullable();
                $table->string('external_url')->nullable();
                $table->string('external_source')->nullable();
                $table->string('external_group')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
            return;
        }

        Schema::table('employees', function (Blueprint $table) {
            // Make email nullable if needed
            $table->string('email')->nullable()->change();
            
            // Handle email indices
            $indexExists = collect(DB::select("SHOW INDEXES FROM employees WHERE Key_name = 'employees_email_unique'"))->isNotEmpty();
            $regularIndexExists = collect(DB::select("SHOW INDEXES FROM employees WHERE Key_name = 'employees_email_index'"))->isNotEmpty();
            
            if ($indexExists) {
                $table->dropUnique(['email']);
            }
            
            if (!$regularIndexExists) {
                $table->index('email');
            }

            // Add missing columns
            if (!Schema::hasColumn('employees', 'jira_account_id')) {
                $table->string('jira_account_id')->nullable();
                $table->index('jira_account_id');
            }

            if (!Schema::hasColumn('employees', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable();
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            }

            if (!Schema::hasColumn('employees', 'deleted_at')) {
                $table->softDeletes();
            }

            // Update type field
            if (Schema::hasColumn('employees', 'active')) {
                $table->renameColumn('active', 'status');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
};
