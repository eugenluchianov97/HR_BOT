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
        Schema::create('vacancies', function (Blueprint $table) {
            $table->id();
            $table->text('ref_key')->unique();
            $table->text('job_с_id')->nullable();
            $table->text('name_ru')->nullable();
            $table->text('name_ro')->nullable();
            $table->text('payment_min')->nullable();
            $table->text('payment_max')->nullable();
            $table->text('order')->nullable();
            $table->text('location_ro')->nullable();
            $table->text('location_ru')->nullable();
            $table->text('district_ro')->nullable();
            $table->text('district_ru')->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacancies');
    }
};
