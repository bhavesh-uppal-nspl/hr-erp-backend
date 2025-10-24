<?php

namespace App\Models\InterModel;

use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class InternCertificate extends Model
{
    use HasFactory;

    protected $table = 'intern_certificates';
    protected $primaryKey = 'intern_certificate_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'intern_id',
        'certificate_type',
        'certificate_title',
        'certificate_number',
        'issue_date',
        'certificate_file_url',
        'issued_by_employee_id',
        'remarks',
    ];


      // FOR ONLINE 
    // public function getDocumentUrlAttribute($value)
    // {
    //     return $value ? asset('storage/app/public/' . $value) : null;
    // }

    public function getCertificateFileUrlAttribute($value)
    {
        return $value ? asset(Storage::url($value)) : null;
    }



    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }


     public function Intern()
    {
        return $this->belongsTo(Interns::class, 'intern_id', 'intern_id');
    }
     public function IssuedBy()
    {
        return $this->belongsTo(Employees::class, 'issued_by_employee_id', 'employee_id');
    }
   
}
