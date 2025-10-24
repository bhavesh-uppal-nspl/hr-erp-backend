<?php

namespace App\Models\OrganizationModel;

use App\Models\ApplicationModels\ApplicationUserRoles;
use App\Models\ApplicationModels\ApplicationUsers;
use App\Models\GeneralModel\GeneralSystemRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class OrganizationUserRoleAssignment extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'organization_user_role_assignments';

    // Define the primary key
    protected $primaryKey = 'organization_user_role_assignment_id';

    // Define the fillable fields to protect against mass-assignment vulnerabilities
    protected $fillable = [
        'organization_user_id',
        'application_user_role_id',
        'organization_id',
        'organization_entity_id',
        'assigned_by',
        'assigned_at'

    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function Applicationuser()
    {
        return $this->hasMany(ApplicationUsers::class, 'application_user_id', 'application_user_id');
    }

    public function OrganizationUser()
    {
        return $this->belongsTo(OrganizationUser::class, 'organization_user_id');
    }

    public function ApplicationUserRole()
    {
        return $this->belongsTo(ApplicationUserRoles::class, 'application_user_role_id');
    }


}
