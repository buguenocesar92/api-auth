<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoutesTable extends Migration
{
    public function up()
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique(); // Ruta única (ejemplo: /dashboard)
            $table->string('component_name'); // Nombre del componente (ejemplo: Dashboard)
            $table->boolean('requires_auth')->default(true); // Si requiere autenticación
            $table->unsignedBigInteger('permission_id')->nullable(); // Permiso asociado
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('routes');
    }
}
