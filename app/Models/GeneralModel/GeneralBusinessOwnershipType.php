<?php

namespace App\Models\GeneralModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralBusinessOwnershipType extends Model
{
    use HasFactory;

    protected $table = 'organization_business_ownership_types';

    protected $primaryKey = 'general_business_ownership_type_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'business_ownership_type_name',
    ];
   
}
