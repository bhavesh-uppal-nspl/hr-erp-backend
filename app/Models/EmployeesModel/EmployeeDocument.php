<?php
namespace App\Models\EmployeesModel;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EmployeeDocument extends Model
{
    use HasFactory;
    protected $table = 'employee_documents';
    protected $primaryKey = 'employee_document_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_document_id',
        'organization_id',
        'employee_id',
        'organization_entity_id',
        'document_name',
        'document_url',
        'employee_document_type_id',
        'document_size_kb'
    ];



    // FOR ONLINE 
    // public function getDocumentUrlAttribute($value)
    // {
    //     return $value ? asset('storage/app/public/' . $value) : null;
    // }

    public function getDocumentUrlAttribute($value)
    {
        return $value ? asset(Storage::url($value)) : null;
    }


    public function employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id', 'employee_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function SectionLink()
    {
        return $this->hasMany(EmployeeDocumentSectionLink::class, 'employee_document_id', 'employee_document_id');
    }

    public function DocumentType()
    {
        return $this->belongsTo(EmployeeDocumentTypes::class, 'employee_document_type_id', 'employee_document_type_id');
    }
}
