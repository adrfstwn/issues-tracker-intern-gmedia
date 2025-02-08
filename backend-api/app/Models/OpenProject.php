<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OpenProject extends Model
{
    use HasFactory;
    protected $table = 'open_project';
    protected $fillable = [
        'data', 
        'issues_id',
        'assignee_id',
        'assignee_name',
        'project_id', 
        'project_name', 
        'work_package_id',
        'lock_version',
    ];
    protected $casts = [
        'data' => 'json',
    ];

    public function issue()
    {
        return $this->belongsTo(Issues::class, 'issues_id');
    }
}