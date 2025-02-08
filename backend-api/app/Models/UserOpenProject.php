<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserOpenProject extends Model
{
    protected $table = 'users_open_project';

    protected $primaryKey = 'id';

    protected $fillable = [
        'memberships_id',
        'openproject_name',
        'user_href',
        'name',
    ];

    public function projects()
    {
        return $this->belongsTo(Project::class, 'openproject_name','openproject_name');
    }

}
