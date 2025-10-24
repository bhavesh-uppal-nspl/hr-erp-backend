<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationBusinessDivision extends Model
{
    use HasFactory;

    // Table name (optional if Laravel can't infer it)
    protected $table = 'organization_business_divisions';

    // Primary key
    protected $primaryKey = 'organization_business_division_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Fillable fields
    protected $fillable = [
       
        'business_division_name',
        'business_division_short_name',
        'organization_id',
        'description',
    ];

    // Relationships
    public function businessUnit()
    {
        return $this->belongsTo(OrganizationBusinessUnit::class, 'business_unit_id', 'business_unit_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

 
}
