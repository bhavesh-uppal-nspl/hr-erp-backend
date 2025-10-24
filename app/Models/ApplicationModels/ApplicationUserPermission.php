<?php

namespace App\Models\ApplicationModels;

use Illuminate\Database\Eloquent\Model;

class ApplicationUserPermission extends Model
{
    protected $table = 'application_user_permissions';
    protected $primaryKey = 'application_user_permission_id';
    public $timestamps = true; 
    protected $fillable = [
        'application_user_id',
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


     public function User()
    {
        return $this->hasMany(ApplicationUsers::class, 'application_user_id', 'application_user_id');
    }

     public function ModuleAction()
    {
        return $this->hasMany(ApplicationModuleAction::class, 'application_module_action_id', 'application_module_action_id');
    }
    
      public function PermissionAuditLogs()
    {
        return $this->belongsTo(ApplicationUserPermissionAuditLogs::class, 'application_user_permission_id', 'application_user_permission_id');
    }
}
