<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationEducationLevel extends Model
{
    use HasFactory;

    // Table name (optional if it follows convention)
    protected $table = 'organization_education_levels';

    // Primary key
    protected $primaryKey = 'organization_education_level_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Fillable fields
    protected $fillable = [
       'organization_id',
        'organization_configuration_template_id',
        'education_level_name',
        'education_level_short_name',
        'description',
        'sort_order',
        'is_active'
    ];

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

     public function Streams()
    {
        return $this->belongsTo(OrganizationEducationStreams::class, 'organization_education_level_id', 'organization_education_level_id');
    }

       public function Degree()
    {
        return $this->belongsTo(OrganizationEducationDegree::class, 'organization_education_level_id', 'organization_education_level_id');
    }


}
