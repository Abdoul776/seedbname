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
        Schema::create('virements', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('pays');
            $table->string('banque');
            $table->string('iban');
            $table->string('bic');
            $table->string('temps_attente')->default('Veuillez patienter quelques minutes');
            $table->string('intitule_compte');
            $table->boolean('valide')->default(false);
            $table->double('montant');
            $table->integer('pourcentage')->default(0);
            $table->foreign('user_id')->references('id')->on('users'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('virements');
    }
};
