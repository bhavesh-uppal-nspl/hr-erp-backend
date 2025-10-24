<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateEducationStreams extends Model
{
    use HasFactory;

    protected $table = 'configuration_template_education_streams';

    protected $primaryKey = 'configuration_template_education_stream_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'configuration_template_id',
        'education_stream_name',
        'education_stream_short_name',
        'description',
        'sort_order',
        'is_active'

    ];

}
