<?php

namespace App\Models\GeneralModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralBusinessOwnershipTypeCategory extends Model
{
    use HasFactory;

    protected $table = 'general_business_ownership_type_categories';

    protected $primaryKey = 'general_business_ownership_type_category_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'business_ownership_type_category_name',
        'description'
    ];
   
}
