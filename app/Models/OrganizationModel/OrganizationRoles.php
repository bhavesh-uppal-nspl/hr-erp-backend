<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationRoles extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'organization_roles';

    // Define the primary key (optional if it follows Laravel's default)
    protected $primaryKey = 'role_id';

    // Define the fillable fields to protect against mass-assignment vulnerabilities
    protected $fillable = [
        'role_name',
        'role_description',
    ];

    // Define the inverse of the relationship with the OrganizationUser model
    public function users()
    {
        return $this->hasMany(OrganizationUser::class, 'role_id', 'role_id');
    }
}
