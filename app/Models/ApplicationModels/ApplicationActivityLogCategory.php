<?php

namespace App\Models\ApplicationModels;
use Illuminate\Database\Eloquent\Model;
class ApplicationActivityLogCategory extends Model
{
    protected $table = 'activity_log_categories';

    protected $primaryKey = 'application_activity_log_category_id';

    public $timestamps = true;

    protected $fillable = [
        'activity_log_category_name',
        'created_at',
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
