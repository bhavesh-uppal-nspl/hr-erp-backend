<?php
namespace App\Models\ProjectModels;

use App\Models\OrganizationModel\OrganizationEntities;
use Illuminate\Database\Eloquent\Model;

class OrganizationProjectTeam extends Model
{
    protected $table = 'organization_project_teams';
    protected $primaryKey = 'organization_project_team_id';

    protected $fillable = [
        'organization_project_id',
        'organization_id',
        'organization_entity_id',
        'project_team_name',
        'project_team_short_name',
        'description',
        'is_active',
    ];

        protected $casts = [
        'is_active' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(OrganizationProject::class, 'organization_project_id');
    }

    public function entity()
    {
        return $this->belongsTo(OrganizationEntities::class, 'organization_entity_id');
    }

    public function members()
    {
        return $this->hasMany(OrganizationProjectTeamMember::class, 'organization_project_team_id');
    }
}
