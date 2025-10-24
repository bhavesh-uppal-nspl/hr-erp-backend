<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationHolidayTypes extends Model
{
    use HasFactory;

    protected $table = 'organization_holiday_types';

    // Define the primary key
    protected $primaryKey = 'organization_holiday_type_id';

    // Enable timestamps (created_at and updated_at)
    public $timestamps = true;

    // Mass assignable fields
    protected $fillable = [
        'organization_holiday_template_id',
        'holiday_type_name',
        'organization_id',
        'description',
        'organization_entity_id',
        'created_by',
        'organization_id'
    ];

    // Relationship to the organization (optional)
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    
}
