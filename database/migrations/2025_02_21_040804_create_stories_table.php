<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoriesTable extends Migration {
    public function up() {
        Schema::create('story', function (Blueprint $table) {
            $table->id();
            $table->foreignId('undangan_id')->constrained('undangan');
            $table->string('title');
            $table->text('desc');
            $table->string('image');
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('story');
    }
}
