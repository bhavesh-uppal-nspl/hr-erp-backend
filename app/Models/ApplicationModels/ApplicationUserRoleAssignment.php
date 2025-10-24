<?php

namespace App\Models\ApplicationModels;

use Illuminate\Database\Eloquent\Model;

class ApplicationUserRoleAssignment extends Model
{
    protected $table = 'application_user_role_assignments';
    protected $primaryKey = 'application_user_role_assignment_id';
    public $timestamps = true; 

    protected $fillable = [
        'application_user_id',
        'application_user_role_id',
        'is_active',
        'created_at',
        'updated_at',
    ];

  

      public function ApplicationUser()
    {
        return $this->hasOne(ApplicationUsers::class, 'application_user_id', 'application_user_id');
    }

       public function ApplicationUserRoles()
    {
        return $this->hasOne(ApplicationUserRoles::class, 'application_user_role_id', 'application_user_role_id');
    }

    
}
