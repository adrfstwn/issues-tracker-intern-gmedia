<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';
    protected $fillable = [
        'project_slug',
        'project_json',
        'openproject_id', 
        'openproject_name'
    ];
    protected $casts = [
        'project_json' => 'json',
    ];

    public function issues()
    {
        return $this->hasMany(Issues::class, 'project_slug','project_slug');
    }

    public function userOpenProject()
    {
        return $this->hasMany(UserOpenProject::class, 'openproject_name','openproject_name');
    }

}
