<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGaleriesTable extends Migration {
    public function up() {
        Schema::create('galery', function (Blueprint $table) {
            $table->id();
            $table->foreignId('undangan_id')->constrained('undangan');
            $table->string('image');
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('galery');
    }
}
