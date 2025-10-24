<?php

namespace App\Models\GeneralDataGrid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationDataGrid extends Model
{
    use HasFactory;

    protected $table = 'organization_datagrid_default_configurations';
    protected $primaryKey = 'organization_datagrid_default_configuration_id';
    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'datagrid_key',
        'datagrid_default_configuration',
    ];

    // OrganizationDataGrid Model
    protected $casts = [
        'datagrid_default_configuration' => 'array',
    ];

    /**
     * Get the organization that owns this datagrid configuration
     */
    public function organization()
    {
        return $this->belongsTo(OrganizationDataGrid::class, 'organization_id', 'organization_id');
    }

    /**
     * Get all user configurations based on this organization configuration
     */
    public function userConfigurations()
    {
        return $this->hasMany(
            OrganizationUserDataGrid::class,
            'organization_id',
            'organization_id'
        )->where('organization_entity_id', $this->organization_entity_id)
            ->where('datagrid_key', $this->datagrid_key);
    }
}
