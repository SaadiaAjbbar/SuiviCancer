<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('symptome_consultation', function (Blueprint $table) {
            $table->foreignId('consultation_id')->constrained();
            $table->foreignId('symptome_id')->constrained();
          //$table->foreignId('consultation_id')->constrained()->onDelete('cascade');

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
