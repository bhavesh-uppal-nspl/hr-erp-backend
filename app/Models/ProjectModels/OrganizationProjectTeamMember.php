<?php
namespace App\Models\ProjectModels;

use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\OrganizationEntities;
use Illuminate\Database\Eloquent\Model;

class OrganizationProjectTeamMember extends Model
{
    protected $table = 'organization_project_team_members';
    protected $primaryKey = 'organization_project_team_member_id';

    protected $fillable = [
        'organization_project_team_id',
        'employee_id',
        'organization_id',
        'organization_project_id',
        'organization_entity_id',
        'organization_user_role_id',
        'is_team_lead',
        'joining_date',
        'is_active',
    ];

        protected $casts = [
        'is_team_lead' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function team()
    {
        return $this->belongsTo(OrganizationProjectTeam::class, 'organization_project_team_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id');
    }

    public function entity()
    {
        return $this->belongsTo(OrganizationEntities::class, 'organization_entity_id');
    }
}
