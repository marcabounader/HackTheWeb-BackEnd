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
        Schema::create('lab_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category');
        });

        Schema::create('lab_difficulties', function (Blueprint $table) {
            $table->id();
            $table->string('difficulty');
        });

        Schema::create('labs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('difficulty_id');
            $table->string('name')->unique();
            $table->string('objective');
            $table->string('launch_api')->unique();
            $table->integer('score');
            $table->foreign('category_id')->references('id')->on('lab_categories');
            $table->foreign('difficulty_id')->references('id')->on('lab_difficulties');

        });
        Schema::create('active_labs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('lab_id');
            $table->unique(['user_id', 'lab_id']);
            $table->string('flag');
            $table->string('project_name');
            $table->integer('port');
            $table->timestamp('launch_time')->useCurrent();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('lab_id')->references('id')->on('labs');

        });

        Schema::create('completed_labs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('lab_id');
            $table->timestamp('complete_time')->useCurrent();
            $table->unique(['user_id', 'lab_id']);
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('lab_id')->references('id')->on('labs');
        });

        Schema::create('badge_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category');
        });

        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("category_id");
            $table->string('name');
            $table->string('icon_url');
            $table->foreign('category_id')->references('id')->on('badge_categories');

        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('badge_id');
            $table->unique(['user_id', 'badge_id']);

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('badge_id')->references('id')->on('badges');

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
