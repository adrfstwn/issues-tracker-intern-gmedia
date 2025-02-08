<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('users_open_project', function (Blueprint $table) {
            $table->id();
            $table->integer('memberships_id')->unique();
            $table->string('openproject_name')->nullable();
            $table->string('user_href');
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users_open_project');
    }
};
