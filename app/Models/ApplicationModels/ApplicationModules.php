<?php

namespace App\Models\ApplicationModels;

use Illuminate\Database\Eloquent\Model;

class ApplicationModules extends Model
{
    protected $table = 'application_modules';
    protected $primaryKey = 'application_module_id';
    public $timestamps = true;
    protected $fillable = [
        'module_name',
        'description',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function ModuleAction()
    {
        return $this->hasMany(ApplicationModuleAction::class, 'application_module_id', 'application_module_id');
    }

       public function PermissionAuditLogs()
    {
        return $this->belongsTo(ApplicationUserPermissionAuditLogs::class, 'application_module_id', 'application_module_id');
    }
}
