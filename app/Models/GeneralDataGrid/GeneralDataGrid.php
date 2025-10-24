<?php

namespace App\Models\GeneralDataGrid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralDataGrid extends Model
{
    use HasFactory;

    protected $table = 'general_datagrid_default_configurations';
    protected $primaryKey = 'general_datagrid_default_configuration_id';
    protected $fillable = [
        'datagrid_key',
        'datagrid_default_configuration'
    ];

    protected $casts = [
        'datagrid_default_configuration' => 'array',
    ];

     public function organization_datagrid_configurations()
    {
        return $this->hasMany(OrganizationDataGrid::class, 'datagrid_key');
    }
}
