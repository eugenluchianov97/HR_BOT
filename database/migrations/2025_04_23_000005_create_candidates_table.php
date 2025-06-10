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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id')->nullable();
            $table->string('lang')->default('ro');
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('date')->nullable();
            $table->string('IDNP')->nullable();
            $table->string('location')->nullable();
            $table->string('current_step')->nullable();
            $table->boolean('agree')->default(false);
            $table->boolean('access')->default(false);
            $table->boolean('sendData')->default(false);
            $table->boolean('sendDocs')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
