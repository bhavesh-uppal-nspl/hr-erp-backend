<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralEntityType extends Model
{
    use HasFactory;

    protected $table = 'general_entity_types';
    protected $primaryKey = 'general_entity_type_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'entity_type_name',
        'description',
        'parent_entity_type_id'
    ];

}
