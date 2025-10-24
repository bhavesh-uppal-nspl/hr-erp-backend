<?php

namespace App\Models\ApplicationModels;
use Illuminate\Database\Eloquent\Model;
class ApplicationActivityLogs extends Model
{
    protected $table = 'application_activity_logs';

    protected $primaryKey = 'application_activity_log_id';

 public $timestamps = false;

    protected $fillable = [
        'application_activity_log_type_id',
        'performed_by_user_id',
        'affected_user_id',
        'organization_id',
        'client_id',
        'application_module_id',
        'application_module_action_id',
        'activity_data',
    ];

    public function Modules()
    {
        return $this->hasMany(ApplicationModules::class, 'application_module_id', 'application_module_id');
    }

    public function UserPermission()
    {
        return $this->belongsTo(ApplicationUserPermission::class, 'application_module_action_id', 'application_module_action_id');
    }

    public function RolePermission()
    {
        return $this->belongsTo(ApplicationUserRolePermisiion::class, 'application_module_action_id', 'application_module_action_id');
    }

    public function PermissionAuditLogs()
    {
        return $this->belongsTo(ApplicationUserPermissionAuditLogs::class, 'application_module_action_id', 'application_module_action_id');
    }
}
