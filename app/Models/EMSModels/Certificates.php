<?php

namespace App\Models\EMSModels;
use Illuminate\Database\Eloquent\Model;

class Certificates extends Model
{
    protected $table = 'organization_ems_certificates';
    protected $primaryKey = 'organization_ems_certificate_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_ems_student_id',
        'organization_ems_admission_id',
        'organization_ems_batch_id',
        'training_program_id',
        'certificate_number',
        'issue_date',
        'valid_until',
        'certificate_status',
        'certificate_name',
        'certificate_url',
        'qr_code_url',
        'remarks',
    ];

    public $timestamps = true;

    public function geCertificateUrlAttribute($value)
    {
        return $value ? asset('storage/app/public/certificates' . $value) : null;
    }
    public function geQrCodeUrlAttribute($value)
    {
        return $value ? asset('storage/app/public/qr_certificates' . $value) : null;
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'organization_ems_student_id');
    }
    public function admission()
    {
        return $this->hasOne(Admission::class, 'organization_ems_admission_id');
    }

    public function batch()
    {
        return $this->hasOne(Batch::class, 'organization_ems_batch_id');
    }
    public function trainingProgram()
    {
        return $this->hasOne(TrainingProgram::class, 'organization_ems_training_program_id', 'training_program_id');
    }


}
