<?php
namespace App\Models\EmployeesModel;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationProfileSection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocumentSectionLink extends Model
{
    use HasFactory;

    protected $table = 'employee_document_section_links';

    protected $primaryKey = 'employee_document_section_link_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_document_id',
        'organization_id',
        'organization_entity_id',
        'organization_employee_profile_section_id'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function ProfileSection()
    {
        return $this->belongsTo(OrganizationProfileSection::class, 'organization_employee_profile_section_id', 'organization_employee_profile_section_id');
    }
}
