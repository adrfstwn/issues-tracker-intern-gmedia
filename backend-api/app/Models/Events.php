<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    use HasFactory;

    protected $table = 'events';
    protected $fillable = ['events_id','issues_id', 'events_json'];
    protected $casts = [
        'events_json' => 'json',
    ];

    public function issue()
    {
        return $this->belongsTo(Issues::class, 'issues_id');
    }
}
