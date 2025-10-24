<?php

namespace App\Models\OrganizationModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationShiftRotationPattern extends Model
{
    use HasFactory;
    protected $table = 'organization_work_shift_rotation_patterns';
    protected $primaryKey = 'organization_work_shift_rotation_pattern_id';
    public $timestamps = true;
    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'pattern_name',
        'cycle_days',
        'description',
    ];

    // Relationship to the organization (if applicable)
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

  

}
