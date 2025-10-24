<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateLeaveCategory extends Model
{
    use HasFactory;

    protected $table = 'configuration_template_leave_categories';

    protected $primaryKey = 'configuration_template_leave_category_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'configuration_template_id',
        'leave_category_name',
        'description',
        'leave_category_code'

    ];

}
