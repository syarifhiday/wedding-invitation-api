<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUndangansTable extends Migration {
    public function up() {
        Schema::create('undangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('template_id')->constrained('templates');
            $table->string('man_nickname');
            $table->string('woman_nickname');
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('undangan');
    }
}
