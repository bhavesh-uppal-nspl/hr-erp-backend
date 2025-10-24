<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationSetting extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'organization_settings';

    // Define the primary key
    protected $primaryKey = 'organization_setting_id';

    // Enable timestamps
    public $timestamps = true;

    // Define the fillable fields for mass-assignment
    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_setting_data_type_id',
        'setting_name',
        'has_predefined_values',
        'predefined_values',
        'setting_value',
        'min_date',
        'min_value',
        'max_value',
        'max_date',
        'default_value',
        'unit',
        'pattern',
        'is_required',
        'customizable',
        'is_active',
        'created_by',
        'organization_setting_type_id'
    ];


    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }
    public function SettingType()
    {
        return $this->belongsTo(OrganizationSettingType::class, 'organization_setting_type_id', 'organization_setting_type_id');
    }

    public function SettingDataType()
    {
        return $this->belongsTo(OrganizationSettingDataType::class, 'organization_setting_data_type_id', 'organization_setting_data_type_id');
    }
}
