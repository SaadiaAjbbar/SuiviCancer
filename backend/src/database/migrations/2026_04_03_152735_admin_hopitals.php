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
        
        Schema::create('admin_hopitals', function (Blueprint $table) {
            $table->id();
            // Lien vers l'User (One-to-One)
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            // Lien vers l'Hôpital
            $table->foreignId('hopital_id')->unique()->constrained('hopitals')->onDelete('cascade');
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
