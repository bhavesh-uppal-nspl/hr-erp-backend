<?php

namespace App\Models\EMSModels;
use Illuminate\Database\Eloquent\Model;

class StudentFees extends Model
{
    protected $table = 'organization_ems_student_fees';
    protected $primaryKey = 'organization_ems_student_fee_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_ems_admission_id',
        'organization_ems_student_id',
        'organization_ems_fee_installment_id',
        'payment_number',
        'payment_date',
        'student_currency_code',
        'amount_paid_student_currency',
        'settlement_currency_code',
        'amount_received_inr',
        'settlement_date',
        'gateway_charges',
        'forex_difference',
        'payment_mode',
        'transaction_reference',
        'payment_status',
        'remarks',
    ];

    public $timestamps = true;

    public function admission()
    {
        return $this->hasOne(Admission::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }
    
    public function installment()
    {
        return $this->hasOne(FeeInstallments::class);
    }
}
