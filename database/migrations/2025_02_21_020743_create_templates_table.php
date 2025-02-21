<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplatesTable extends Migration {
    public function up() {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('desc');
            $table->string('file');
            $table->string('type');
            $table->integer('price');
            $table->boolean('flag_active');
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('templates');
    }
}
