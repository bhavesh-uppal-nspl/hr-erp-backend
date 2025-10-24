<?php

namespace App\Models\OrganizationModel;

use App\Models\ApplicationModels\ApplicationUsers;
use App\Models\GeneralModel\GeneralSystemRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class OrganizationUserRoles extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'organization_users';

    // Define the primary key
    protected $primaryKey = 'organization_user_role_id';

    // Define the fillable fields to protect against mass-assignment vulnerabilities
    protected $fillable = [
        'organization_id',
        'application_user_role_id',
        'assigned_by',
        'application_user_id',
        'organization_entity_id',
        'assigned_at',
        'is_active'
        
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function OrganizationUser()
    {
        return $this->hasMany(OrganizationUser::class, 'organization_user_id', 'organization_user_id');
    }

    public function ApplicationUser()
    {
        return $this->hasMany(ApplicationUsers::class, 'organization_user_id', 'organization_user_id');
    }


}
