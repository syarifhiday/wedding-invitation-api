<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Template extends Model {
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'id', 'title', 'description', 'type', 'file_path', 'price', 'flag_active'
    ];

    protected static function boot() {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString(); // Generate UUID otomatis
            }
        });
    }

    public function users() {
        return $this->belongsToMany(User::class, 'user_templates', 'template_id', 'user_id');
    }

    public function undangan() {
        return $this->hasMany(Undangan::class, 'template_id');
    }

}
