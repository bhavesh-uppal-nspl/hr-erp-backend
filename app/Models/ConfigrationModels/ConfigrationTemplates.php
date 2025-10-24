<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplates extends Model
{
    use HasFactory;

    protected $table = 'configuration_templates';

    protected $primaryKey = 'configuration_template_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'template_code',
        'template_name',
        'description',
        'scope',
        'general_country_id',
        'general_state_id'
    ];

}
