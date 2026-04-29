<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExecutiveTargetRecord extends Model
{
    use HasFactory, SoftDeletes, AutoLogsActivity;

    protected $fillable = [
        'job_card_id',
        'executive_id',
        'date',
        'bag_type',
        'width',
        'length',
        'guzzete',
        'gsm',
        'per_pcs_weight',
        'total_pcs',
        'total_weight'
    ];

    public function job_card()
    {
        return $this->belongsTo(JobCard::class);
    }

    public function executive()
    {
        return $this->belongsTo(User::class, 'executive_id');
    }
}
