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
        Schema::create('candidate_documents', function (Blueprint $table) {
            $table->id();

            $table->unsignedBiginteger('candidate_id')->unsigned();

            $table->unsignedBiginteger('document_id')->unsigned();
            $table->unsignedBiginteger('vacancy_id')->unsigned();

            $table->text('src')->nullable();
            $table->text('type')->nullable();
            $table->boolean('required')->default(false)->nullable();

            $table->foreign('candidate_id')->references('id')->on('candidates')->onDelete('cascade');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('vacancy_id')->references('id')->on('vacancies')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_document_source');
    }
};
