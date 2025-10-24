<?php
namespace App\Models\PayrollModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class EmployeePaySlipPayments extends Model
{
    use HasFactory;
    protected $table = 'employee_payslip_payments';
    protected $primaryKey = 'employee_payslip_payment_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'employee_payslip_id',
        'payment_date',
        'payment_mode',
        'amount',
        'reference_no',
        'status',
        'remarks',
    ];
   
    public function Payslip()
    {
        return $this->belongsTo(EmployeePaySlip::class, 'employee_payslip_id', 'employee_payslip_id');
    }
}
