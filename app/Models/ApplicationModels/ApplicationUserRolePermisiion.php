<?php

namespace App\Models\ApplicationModels;
use Illuminate\Database\Eloquent\Model;

class ApplicationUserRolePermisiion extends Model
{
    protected $table = 'application_user_role_permissions';
    protected $primaryKey = 'application_user_role_permission_id';
    public $timestamps = true; 
    protected $fillable = [
        'application_user_role_id',
        'application_module_action_id',
        'permission_allowed',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'permission_allowed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

     public function UserRole()
    {
        return $this->hasMany(ApplicationUserRoles::class, 'application_user_role_id', 'application_user_role_id');
    }

     public function ModuleAction()
    {
        return $this->hasMany(ApplicationModuleAction::class, 'application_module_action_id', 'application_module_action_id');
    }


   // In ApplicationUserRolePermisiion.php
public function applicationUserRole()
{
    return $this->belongsTo(ApplicationUserRoles::class, 'application_user_role_id');
}

public function applicationModuleAction()
{
    return $this->belongsTo(ApplicationModuleAction::class, 'application_module_action_id');
}




}
