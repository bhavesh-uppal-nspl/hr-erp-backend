<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateEducationLevelDegreeStreams extends Model
{
    use HasFactory;

    protected $table = 'configuration_template_education_level_degree_streams';

    protected $primaryKey = 'configuration_template_education_level_degree_stream_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'configuration_template_id',
        'configuration_template_education_level_id',
        'configuration_template_education_degree_id',
        'configuration_template_education_stream_id',
        'is_active'

    ];

}
