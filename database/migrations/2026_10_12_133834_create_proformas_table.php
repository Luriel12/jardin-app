<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('proformas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->json('items'); // Guarda todas las filas de la tabla de materiales
            $table->decimal('total_materials', 10, 2)->default(0);
            $table->decimal('labor_price', 10, 2)->default(0);
            $table->text('labor_desc')->nullable();
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('proformas');
    }
};
