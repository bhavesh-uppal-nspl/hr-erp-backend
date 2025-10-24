<?php

namespace App\Models\ApplicationModels;

use Illuminate\Database\Eloquent\Model;

class Applicationuserloginlogs extends Model
{
    protected $table = 'application_user_login_logs';
    protected $primaryKey = 'application_user_login_log_id';
    public $timestamps = true;
    protected $fillable = [
        'application_user_id',
        'login_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(ApplicationUsers::class, 'application_user_id', 'application_user_id');
    }
}
