<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUndangansTable extends Migration {
    public function up() {
        Schema::create('undangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->uuid('template_id');
            $table->foreign('template_id')->references('id')->on('templates')->onDelete('cascade');
            $table->string('man_nickname');
            $table->string('woman_nickname');
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('undangan');
    }
}
