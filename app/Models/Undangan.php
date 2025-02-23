<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Undangan extends Model {
    use HasFactory;

    protected $table = 'undangan';

    protected $fillable = ['user_id', 'template_id', 'cover_image', 'man_name', 'man_nickname', 'man_ig', 'man_address', 'man_father', 'man_mother', 'woman_name', 'woman_nickname', 'woman_ig', 'woman_address', 'woman_father', 'woman_mother'];

    public function template() {
        return $this->belongsTo(Template::class, 'template_id');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function acara() {
        return $this->hasMany(Acara::class);
    }

    public function story() {
        return $this->hasMany(Story::class);
    }

    public function rekening() {
        return $this->hasMany(Rekening::class);
    }

    public function galery() {
        return $this->hasMany(Galery::class);
    }
}
