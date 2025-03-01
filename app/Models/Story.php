<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Story extends Model {
    use HasFactory;
    protected $table = 'story';
    protected $fillable = ['undangan_id', 'title', 'desc', 'image'];
}
