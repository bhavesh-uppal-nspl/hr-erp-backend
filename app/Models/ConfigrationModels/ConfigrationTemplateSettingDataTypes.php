<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateSettingDataTypes extends Model
{
    use HasFactory;

    protected $table = 'configuration_template_setting_data_types';

    protected $primaryKey = 'configuration_template_setting_data_type_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'configuration_template_id',
        'setting_data_type_name',
        'description'
    ];

}
