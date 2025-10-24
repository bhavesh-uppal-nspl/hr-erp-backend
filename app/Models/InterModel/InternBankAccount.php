<?php
namespace App\Models\InterModel;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternBankAccount extends Model
{
    use HasFactory;

    protected $table = 'intern_bank_accounts';

    protected $primaryKey = 'intern_bank_account_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'intern_id',
        'account_holder_name',
        'bank_name',
        'branch_name',
        'account_number',
        'ifsc_code',
        'swift_code',
        'iban_number',
        'upi_id',
        'wallet_id',
        'is_primary',
        'is_active'
    ];


    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

   
}
