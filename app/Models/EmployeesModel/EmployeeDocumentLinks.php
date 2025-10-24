<?php
namespace App\Models\EmployeesModel;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocumentLinks extends Model
{
    use HasFactory;

    protected $table = 'employee_document_links';

    protected $primaryKey = 'employee_document_link_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_document_id',
        'organization_id',
        'linked_record_id',
        'organization_entity_id',
        'general_employee_document_table_reference_id'
    ];


    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id', 'employee_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }
}
