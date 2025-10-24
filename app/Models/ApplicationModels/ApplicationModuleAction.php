<?php

namespace App\Models\ApplicationModels;
use Illuminate\Database\Eloquent\Model;
class ApplicationModuleAction extends Model
{
    protected $table = 'application_module_actions';

    protected $primaryKey = 'application_module_action_id';

    public $timestamps = true;

    protected $fillable = [
        'application_module_id',
        'module_action_name',
        'module_action_code',
        'description',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'application_module_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
