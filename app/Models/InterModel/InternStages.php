<?php

namespace App\Models\InterModel;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternStages extends Model
{
    use HasFactory;

    protected $table = 'organization_internship_stages';

    protected $primaryKey = 'organization_internship_stage_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_configuration_template_id',
        'organization_internship_status_id',
        'internship_stage_name',
        'description',
        'created_by',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

        public function Status()
    {
        return $this->belongsTo(IntershipStatus::class, 'organization_internship_status_id', 'organization_internship_status_id');
    }

   
}
