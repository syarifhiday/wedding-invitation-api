<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUndangansTable extends Migration {
    public function up() {
        Schema::create('undangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->uuid('template_id');
            $table->foreign('template_id')->references('id')->on('templates')->onDelete('cascade');
            $table->string('cover_image')->nullable();
            $table->string('man_name');
            $table->string('man_nickname');
            $table->string('man_ig')->nullable();
            $table->text('man_address')->nullable();
            $table->string('man_father')->nullable();
            $table->string('man_mother')->nullable();
            $table->string('woman_name');
            $table->string('woman_nickname');
            $table->string('woman_ig')->nullable();
            $table->text('woman_address')->nullable();
            $table->string('woman_father')->nullable();
            $table->string('woman_mother')->nullable();
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('undangan');
    }
}
