<?php

namespace App\Models\InterModel;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternMedical extends Model
{
    use HasFactory;

    protected $table = 'intern_medicals';

    protected $primaryKey = 'intern_medical_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'intern_id',
        'organization_id',
        'organization_entity_id',
        'blood_group',
        'allergies',
        'diseases',
        'disability_status',
        'disability_description',
        'is_fit_for_duty',
        'last_health_check_date',
        'medical_notes',
        'is_active'
    ];


    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

   

}
