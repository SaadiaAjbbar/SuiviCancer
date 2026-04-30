<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('etat_generals', function (Blueprint $table) {
            $table->id();
            $table->text('description');
            $table->foreignId('consultation_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('reponse_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
