<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateLanguages extends Model
{
    use HasFactory;

    protected $table = 'configuration_template_languages';

    protected $primaryKey = 'configuration_template_language_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'configuration_template_id',
        'language_name',
        'language_code',
        'description'

    ];

}
