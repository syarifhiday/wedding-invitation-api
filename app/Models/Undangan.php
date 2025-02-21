<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Undangan extends Model {
    use HasFactory;
    protected $fillable = ['user_id', 'template_id', 'man_nickname', 'woman_nickname'];

    public function template() {
        return $this->belongsTo(Template::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
