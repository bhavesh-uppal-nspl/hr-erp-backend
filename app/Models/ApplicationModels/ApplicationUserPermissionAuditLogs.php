<?php

namespace App\Models\ApplicationModels;

use Illuminate\Database\Eloquent\Model;

class ApplicationUserPermissionAuditLogs extends Model
{
    protected $table = 'application_user_permission_audit_logs';
    protected $primaryKey = 'application_user_permission_audit_log_id';
    public $timestamps = true; 

    protected $fillable = [
        'application_user_permission_id',
        'application_user_id',
        'application_module_id',
        'module_name_snapshot',
        'application_module_action_id',
        'module_action_name_snapshot',
        'user_name_snapshot',
        'previous_permission_status',
        'new_permission_status',
        'change_source',
        'modified_by_user_id',
        'changed_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'previous_permission_status' => 'string',
        'new_permission_status' => 'string',     
        'change_source' => 'string',              
        'changed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


     public function User()
    {
        return $this->hasMany(ApplicationUsers::class, 'application_user_id', 'application_user_id');
    }

     public function ModuleAction()
    {
        return $this->hasMany(ApplicationModuleAction::class, 'application_module_action_id', 'application_module_action_id');
    }

      public function Module()
    {
        return $this->hasMany(ApplicationModules::class, 'application_module_id', 'application_module_id');
    }

       public function UserPermission()
    {
        return $this->hasMany(ApplicationUserPermission::class, 'application_user_permission_id', 'application_user_permission_id');
    }
}
