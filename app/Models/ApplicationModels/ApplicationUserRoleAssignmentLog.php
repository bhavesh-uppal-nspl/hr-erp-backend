<?php

namespace App\Models\ApplicationModels;

use Illuminate\Database\Eloquent\Model;

class ApplicationUserRoleAssignmentLog extends Model
{
    protected $table = 'application_user_role_assignment_logs';

    protected $primaryKey = 'application_user_role_assignment_log_id';

    public $timestamps = true; // enables created_at and updated_at

    protected $fillable = [
        'application_user_id',
        'previous_role_id',
        'previous_role_name',
        'new_role_id',
        'new_role_name',
        'changed_by_user_id',
        'change_reason',
        'changed_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

       public function User()
    {
        return $this->hasMany(ApplicationUsers::class, 'application_user_id', 'application_user_id');
    }
}

