<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateEducationDegree extends Model
{
    use HasFactory;

    protected $table = 'configuration_template_education_degrees';

    protected $primaryKey = 'configuration_template_education_degree_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'configuration_template_id',
        'education_degree_name',
        'education_degree_short_name',
        'description',
        'sort_order',
        'is_active'

    ];

}
