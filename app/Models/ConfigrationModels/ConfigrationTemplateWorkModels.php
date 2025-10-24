<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateWorkModels extends Model
{
    use HasFactory;

    protected $table = 'configuration_template_work_models';

    protected $primaryKey = 'configuration_template_work_model_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'configuration_template_id',
        'work_model_name',
    ];

}
