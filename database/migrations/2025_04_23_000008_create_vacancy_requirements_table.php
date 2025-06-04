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
        Schema::create('vacancy_requirements', function (Blueprint $table) {
            $table->id();

            $table->unsignedBiginteger('requirement_id')->unsigned();
            $table->unsignedBiginteger('vacancy_id')->unsigned();

            $table->text('additional_info')->nullable();
            $table->text('necessarily')->nullable();

            $table->foreign('vacancy_id')->references('id')->on('vacancies')->onDelete('cascade');
            $table->foreign('requirement_id')->references('id')->on('requirements')->onDelete('cascade');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacancies_requirements');
    }
};
