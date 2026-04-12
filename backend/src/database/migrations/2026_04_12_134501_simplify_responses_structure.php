<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    // 1. M7i l-table pivot li khetbeqlina l-khidma
    Schema::dropIfExists('reponse_questionnaires');
    Schema::dropIfExists('reponse_questionnaire'); // checki smiya li knti derti

    Schema::create('reponse_questionnaires', function (Blueprint $table) {
        $table->id();
        $table->string('valeur'); // Hna fin ghadi t-koun l-jawaba (ex: "38.5" aw "Bien")
        $table->foreignId('question_id')->constrained()->onDelete('cascade');
        $table->foreignId('patient_id')->constrained()->onDelete('cascade');
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
