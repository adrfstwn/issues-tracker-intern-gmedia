<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issues extends Model
{
    use HasFactory;

    protected $table = 'issues';

    protected $fillable = [
        'project_slug', 
        'issues_json',
        'issues_id'
    ];

    protected $casts = [
        'issues_json' => 'json',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_slug', 'project_slug');
    }

    public function openProject()
    {
        return $this->belongsTo(OpenProject::class, 'issues_id', 'issues_id');
    }

    public function events()
    {
        return $this->hasMany(Events::class, 'issues_id');
    }

    public function stackTrace()
    {
        return $this->hasMany(StackTrace::class, 'issues_id');
    }

    // public function assigneeName()
    // {
    //     return $this->openProject ? $this->openProject->assignee_name : 'unassigned';
    // }

    // public function assigneeId()
    // {
    //     return $this->openProject ? $this->openProject->assignee_id : null;
    // }

    // protected $appends = ['assignee_name', 'assignee_id'];
}
