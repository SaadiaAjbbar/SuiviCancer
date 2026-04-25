<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {

        Schema::create('rendez_vous', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date'); // Men l-ahsan dateTime bach t-7eddi l-sa3a
            $table->string('status')->default('En attente');
            $table->foreignId('etat_general_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade'); // Zidi hada
            $table->timestamps();
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
