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
        Schema::create('vacancy_days', function (Blueprint $table) {
            $table->id();

            $table->unsignedBiginteger('day_id')->unsigned();
            $table->unsignedBiginteger('vacancy_id')->unsigned();

            $table->text('from')->nullable();
            $table->text('to')->nullable();

            $table->foreign('vacancy_id')->references('id')->on('vacancies')->onDelete('cascade');
            $table->foreign('day_id')->references('id')->on('days')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacancies_days');
    }
};
