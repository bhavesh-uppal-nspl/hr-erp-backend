<?php

namespace App\Models\InterModel;

use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationEducationDegree;
use App\Models\OrganizationModel\OrganizationEducationLevel;
use App\Models\OrganizationModel\OrganizationEducationStreams;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternExperience extends Model
{
    use HasFactory;

    protected $table = 'intern_experiences';

    protected $primaryKey = 'intern_experience_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'intern_id',
        'organization_id',
        'experience_type',
        'organization_entity_id',
        'organization_name',
        'location',
        'general_industry_id',
        'location',
        'work_title',
        'description',
        'work_mode',
        'compensation_status',
        'compensation_amount',
        'currency_code',
        'start_date',
        'end_date',
        'reporting_manager_name',
        'reporting_manager_contact',
        'is_verified',
        'verified_by',
        'verification_date',
        'verification_notes',
        'is_active'

    ];


    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function Level()
    {
        return $this->belongsTo(OrganizationEducationLevel::class, 'organization_education_level_id', 'organization_education_level_id');
    }

    public function Degree()
    {
        return $this->belongsTo(OrganizationEducationDegree::class, 'organization_education_degree_id', 'organization_education_degree_id');
    }


    public function Stream()
    {
        return $this->belongsTo(OrganizationEducationStreams::class, 'organization_education_stream_id', 'organization_education_stream_id');
    }


}
