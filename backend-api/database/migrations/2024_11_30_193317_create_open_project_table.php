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
         Schema::create('open_project', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('issues_id')->unique();
            $table->unsignedBigInteger('work_package_id')->nullable();
            $table->integer('lock_version')->nullable();
            $table->string('assignee_id');
            $table->string('assignee_name')->default('unassigned');
            $table->unsignedBigInteger('project_id');
            $table->string('project_name')->default('root project');;
            $table->json('data');
            $table->timestamps();

            $table->foreign('issues_id')->references('issues_id')->on('issues')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('open_project');
    }
};
