<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateSettings extends Model
{
    use HasFactory;

    protected $table = 'configuration_template_settings';

    protected $primaryKey = 'configuration_template_setting_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'configuration_template_id',
        'general_setting_type_id',
        'general_setting_data_type_id',
        'setting_name',
        'has_predefined_values',
        'predefined_values',
        'min_value',
        'max_value',
        'unit',
        'min_date',
        'max_date',
        'pattern',
        'default_value',
        'setting_value',
        'customizable',
        'is_required'

    ];

}
