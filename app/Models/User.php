<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens; // This trait provides the createToken method
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens; // Use the HasApiTokens trait

    protected $fillable = ['name', 'email', 'phone_number', 'password', 'google_id', 'auth_provider'];

    protected $hidden = ['password', 'remember_token'];

    public function templates() {
        return $this->belongsToMany(Template::class, 'user_templates', 'user_id', 'template_id');
    }

    public function undangan() {
        return $this->hasMany(Undangan::class);
    }

}

