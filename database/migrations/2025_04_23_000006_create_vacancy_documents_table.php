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
        Schema::create('vacancy_documents', function (Blueprint $table) {
            $table->id();

            $table->unsignedBiginteger('document_id')->unsigned();
            $table->unsignedBiginteger('vacancy_id')->unsigned();

            $table->text('additional_info')->nullable();

            $table->boolean('required')->nullable()->default(false);

            $table->foreign('vacancy_id')->references('id')->on('vacancies')->onDelete('cascade');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacancies_documents');
    }
};
