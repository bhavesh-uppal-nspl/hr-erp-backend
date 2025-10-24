<?php

namespace App\Models\GeneralModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralIndustry extends Model
{
    use HasFactory;

    // Table name (optional if Laravel can't infer it)
    protected $table = 'general_industries';

    // Primary key
    protected $primaryKey = 'general_industry_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Fillable fields
    protected $fillable = [
       
        'industry_name',
    ];

    // Relationships
    // Relationships (example)
   
}
