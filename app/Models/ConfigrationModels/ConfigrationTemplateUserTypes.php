<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateUserTypes extends Model
{
    use HasFactory;

    protected $table = 'configuration_template_user_types';

    protected $primaryKey = 'configuration_template_user_type_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'configuration_template_id',
        'user_type_name',
        'description'
    ];

}
