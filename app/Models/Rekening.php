<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rekening extends Model {
    use HasFactory;
    protected $fillable = ['undangan_id', 'account_name', 'account_number', 'bank'];
}
