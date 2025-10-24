<?php
namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class OrganizationEmpExitReasonType extends Model
{
    use HasFactory;

    // Table associated with this model
    protected $table = 'organization_employment_exit_reason_types';

    // Primary key
    protected $primaryKey = 'organization_employment_exit_reason_type_id';

    // Enable timestamps
    public $timestamps = true;

    // Mass-assignable fields
    protected $fillable = [
        'organization_id',
        'employment_exit_reason_type_name',
        'description',
        'organization_entity_id',
        'organization_configuration_template_id',
        'created_by'
    ];


    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }
}
