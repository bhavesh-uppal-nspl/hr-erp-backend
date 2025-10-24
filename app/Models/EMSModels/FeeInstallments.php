<?php

namespace App\Models\EMSModels;
use Illuminate\Database\Eloquent\Model;

class FeeInstallments extends Model
{
    protected $table = 'organization_ems_fee_installments';
    protected $primaryKey = 'organization_ems_fee_installment_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_ems_admission_id',
        'organization_ems_student_id',
        'installment_number',
        'due_date',
        'amount_due',
        'currency_code',
        'status',
        'remarks'
    ];

    public $timestamps = true;

    public function student()
    {
        return $this->hasOne(Student::class , 'organization_ems_student_id');
    }

    public function admission()
    {
        return $this->hasOne(Admission::class , 'organization_ems_admission_id');
    }

}
