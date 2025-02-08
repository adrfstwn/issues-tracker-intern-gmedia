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
        Schema::create('stack_trace', function (Blueprint $table) {
            $table->id();
            $table->string('stack_trace_id')->unique()->after('id');
            $table->unsignedBigInteger('issues_id');
            $table->json('stack_trace_json');
            $table->timestamps();

            $table->foreign('issues_id')->references('issues_id')->on('issues')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stack_trace');
    }
};
