<?php

namespace App\Models\GeneralModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralLeaveDuration extends Model
{
    use HasFactory;

    protected $table = 'general_leave_duration_types';
    protected $primaryKey = 'general_leave_duration_type_id';
    public $timestamps = true;

    protected $fillable = [
        'leave_duration_type_name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
