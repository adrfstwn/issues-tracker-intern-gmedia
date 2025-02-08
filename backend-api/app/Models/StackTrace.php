<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StackTrace extends Model
{
    use HasFactory;

    protected $table = 'stack_trace';
    protected $fillable = ['stack_trace_id','issues_id', 'stack_trace_json'];
    protected $casts = [
        'stack_trace_json' => 'json',
    ];

    public function issue()
    {
        return $this->belongsTo(Issues::class, 'issues_id');
    }
}
