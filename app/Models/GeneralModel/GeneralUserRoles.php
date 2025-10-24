<?php

namespace App\Models\GeneralModel;

use App\Models\ApplicationModels\ApplicationUserRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralUserRoles extends Model
{
    use HasFactory;

    // Table name (optional if Laravel can't infer it)
    protected $table = 'general_user_roles';

    // Primary key
    protected $primaryKey = 'general_user_role_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Fillable fields
    protected $fillable = [
       'description',
       'user_role_name'
    ];


    public function UserRoles()
    {
        return $this->hasOne(ApplicationUserRoles::class, 'general_user_role_id', 'general_user_role_id');
    }

     
   
}
