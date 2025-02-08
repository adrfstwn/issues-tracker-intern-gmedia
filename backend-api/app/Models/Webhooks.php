<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Webhooks extends Model
{
    use HasFactory;
    protected $table = 'webhooks';
    protected $fillable = ['payload'];
    protected $casts = [
        'payload' => 'json',
    ];
}
