<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateSettingType extends Model
{
    use HasFactory;

    protected $table = 'configuration_template_setting_types';

    protected $primaryKey = 'configuration_template_setting_type_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'setting_type_name',
    ];

}
