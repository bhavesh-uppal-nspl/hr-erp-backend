<?php

namespace App\Models\InterModel;

use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationEducationDegree;
use App\Models\OrganizationModel\OrganizationEducationLevel;
use App\Models\OrganizationModel\OrganizationEducationStreams;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternEducation extends Model
{
    use HasFactory;

    protected $table = 'intern_educations';

    protected $primaryKey = 'intern_education_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'intern_id',
        'organization_id',
        'organization_education_level_id',
        'organization_education_degree_id',
        'organization_education_stream_id',
        'organization_education_level_degree_stream_id',
        'institute_name',
        'board_name',
        'marks_percentage',
        'year_of_passing',
        'is_pursuing',
        'is_active'

    ];


    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function Level()
    {
        return $this->hasMany(OrganizationEducationLevel::class, 'organization_education_level_id', 'organization_education_level_id');
    }

    public function degree()
    {
        return $this->hasMany(OrganizationEducationDegree::class, 'organization_education_degree_id', 'organization_education_degree_id');
    }


    public function Stream()
    {
        return $this->hasMany(OrganizationEducationStreams::class, 'organization_education_stream_id', 'organization_education_stream_id');
    }


}
