<?php

namespace App\Models\InterModel;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternStipend extends Model
{
    use HasFactory;

    protected $table = 'intern_stipends';

    protected $primaryKey = 'intern_stipend_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'stipend_type',
        'intern_id',
        'stipend_amount',
        'currency_code',
        'payment_cycle',
        'effective_start_date',
        'effective_end_date',
        'is_active',
        'last_payment_date',
        'next_payment_date',
        'remarks',
      
    ];


    

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

        public function Intern()
    {
        return $this->belongsTo(Interns::class, 'intern_id', 'intern_id');
    }

   
}
