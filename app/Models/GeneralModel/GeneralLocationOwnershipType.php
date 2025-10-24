<?php

namespace App\Models\GeneralModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralLocationOwnershipType extends Model
{
    use HasFactory;

    // Table name (optional if Laravel can't infer it)
    protected $table = 'general_location_ownership_types';

    // Primary key
    protected $primaryKey = 'general_location_ownership_type_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Fillable fields
    protected $fillable = [
        'location_ownership_type_name',
    ];

   
}