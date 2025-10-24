<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationEducationLevelDegreeStreams extends Model
{
    use HasFactory;

    // Table name (optional if it follows convention)
    protected $table = 'organization_education_level_degree_streams';

    // Primary key
    protected $primaryKey = 'organization_education_level_degree_stream_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Fillable fields
    protected $fillable = [
       'organization_id',
        'organization_configuration_template_id',
        'organization_education_level_id',
        'organization_entity_id',
        'organization_education_degree_id',
        'organization_education_stream_id',
        'is_active',
    ];

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function department()
    {
        return $this->belongsTo(OrganizationDepartment::class, 'organization_department_id', 'organization_department_id'); // Adjust if model/table name differs
    }


}
