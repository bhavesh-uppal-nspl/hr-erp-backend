<?php

namespace App\Models\PayrollModels;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollJournalEntries extends Model
{
    use HasFactory;
    protected $table = 'organization_payroll_journal_entries';
    protected $primaryKey = 'organization_payroll_journal_entry_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'organization_payroll_run_id',
        'organization_entity_id',
        'organization_id',
        'employee_id',
        'journal_date',
        'account_code',
        'account_name',
        'debit_amount',
        'credit_amount',
        'reference_type',
        'reference_id',
        'remarks'
    ];

       public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

   
}
