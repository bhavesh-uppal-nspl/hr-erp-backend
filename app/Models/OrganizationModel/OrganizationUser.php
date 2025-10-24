<?php

namespace App\Models\OrganizationModel;

use App\Models\ApplicationModels\ApplicationUsers;
use App\Models\GeneralModel\GeneralSystemRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class OrganizationUser extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'organization_users';

    // Define the primary key
    protected $primaryKey = 'organization_user_id';

    // Define the fillable fields to protect against mass-assignment vulnerabilities
    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_user_type_id',
        'application_user_id',
        'is_active',
        
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }


    public function Applicationuser()
    {
        return $this->hasMany(ApplicationUsers::class, 'application_user_id', 'application_user_id');
    }

     public function RoleAssignment()
    {
        return $this->belongsTo(OrganizationUserRoleAssignment::class, 'organization_user_id', 'organization_user_id');
    }

    public function UserTypes()
    {
        return $this->hasOne(OrganizationUserType::class, 'organization_user_type_id', 'organization_user_type_id');
    }

}
