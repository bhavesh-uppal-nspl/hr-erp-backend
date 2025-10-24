<?php

namespace App\Models\GeneralModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralSystemRole extends Model
{
    use HasFactory;

    // Table name (optional if Laravel can't infer it)
    protected $table = 'general_system_roles';

    // Primary key
    protected $primaryKey = 'general_system_role_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Fillable fields
    protected $fillable = [
       'system_role_name'
    ];


     
   
}
