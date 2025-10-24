<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationEducationStreams extends Model
{
    use HasFactory;

    // Table name (optional if it follows convention)
    protected $table = 'organization_education_streams';

    // Primary key
    protected $primaryKey = 'organization_education_stream_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Fillable fields
    protected $fillable = [
       'organization_id',
        'organization_configuration_template_id',
        'education_stream_name',
        'organization_education_level_id',
        'organization_entity_id',
        'education_stream_short_name',
        'description',
        'sort_order',
        'is_active'
    ];

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

      public function levels()
    {
        return $this->hasMany(OrganizationEducationStreams::class, 'organization_education_level_id', 'organization_education_level_id');
    }

    public function degree()
    {
        return $this->hasMany(OrganizationEducationDegree::class, 'organization_education_level_id', 'organization_education_level_id');
    }


}
