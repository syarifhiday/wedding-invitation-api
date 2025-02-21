<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcarasTable extends Migration {
    public function up() {
        Schema::create('acara', function (Blueprint $table) {
            $table->id();
            $table->foreignId('undangan_id')->constrained('undangan');
            $table->string('title');
            $table->text('desc');
            $table->dateTime('date');
            $table->string('icon');
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('acara');
    }
}
