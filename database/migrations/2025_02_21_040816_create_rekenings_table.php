<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRekeningsTable extends Migration {
    public function up() {
        Schema::create('rekening', function (Blueprint $table) {
            $table->id();
            $table->foreignId('undangan_id')->constrained('undangan');
            $table->string('account_name');
            $table->string('account_number');
            $table->string('bank');
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('rekening');
    }
}
