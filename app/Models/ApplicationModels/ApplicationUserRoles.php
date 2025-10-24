<?php

namespace App\Models\ApplicationModels;

use App\Models\GeneralModel\GeneralUserRoles;
use Illuminate\Database\Eloquent\Model;

class ApplicationUserRoles extends Model
{
    protected $table = 'application_user_roles';
    protected $primaryKey = 'application_user_role_id';
    public $timestamps = true;
    protected $fillable = [
        'user_role_name',
        'description',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function RoleAssignment()
    {
        return $this->belongsTo(ApplicationUserRoleAssignment::class, 'application_user_role_id', 'application_user_role_id');
    }

    public function GeneralRoles()
    {
        return $this->hasOne(GeneralUserRoles::class, 'general_user_role_id', 'general_user_role_id');
    }

    public function applicationUserRolePermissions()
{
    return $this->hasMany(ApplicationUserRolePermisiion::class, 'application_user_role_id');
}

public function permissions()
{
    return $this->hasMany(ApplicationUserRolePermisiion::class, 'application_user_role_id');
}



}
