<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('is_active')->default(true); // true = Activo, false = Suspendido
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
