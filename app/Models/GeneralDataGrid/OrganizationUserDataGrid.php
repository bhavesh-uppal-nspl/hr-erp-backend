<?php

namespace App\Models\GeneralDataGrid;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationUserDataGrid extends Model
{
    use HasFactory;

    protected $table = 'organization_user_datagrid_configuration';
    protected $primaryKey = 'organization_user_datagrid_configuration_id';
    protected $fillable = [
        'organization_user_id',
        'organization_id',
        'organization_entity_id',
        'datagrid_key',
        'datagrid_configuration',
    ];

    protected $casts = [
        'datagrid_configuration' => 'array',
    ];

    /**
     * Get the organization user that owns this configuration
     */
    public function organizationUser()
    {
        return $this->belongsTo(OrganizationUserDataGrid::class, 'organization_user_id', 'organization_user_id');
    }

    /**
     * Get the organization that this configuration belongs to
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    /**
     * Get the organization's default configuration for this datagrid (if exists)
     */
    public function organizationDefaultConfiguration()
    {
        return $this->hasOne(
            OrganizationDataGrid::class,
            'organization_id',
            'organization_id'
        )->where('organization_entity_id', $this->organization_entity_id)
            ->where('datagrid_key', $this->datagrid_key);
    }
}