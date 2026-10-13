<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('service_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->json('items'); // Los ítems cobrados
            $table->decimal('total_materials', 10, 2)->default(0);
            $table->decimal('labor_price', 10, 2)->default(0);
            $table->text('labor_desc')->nullable();
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->timestamp('service_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_histories');
    }
};
