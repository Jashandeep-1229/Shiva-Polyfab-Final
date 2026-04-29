<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiIntelligenceDesign extends Model
{
    use HasFactory, SoftDeletes, AutoLogsActivity;

    protected $fillable = [
        'user_id',
        'customer_name',
        'contact_no',
        'requirements',
        'ai_parsed_data',
        'design_mockups',
        'status',
        'approval_date',
        'job_card_id',
    ];

    protected $casts = [
        'ai_parsed_data' => 'array',
        'design_mockups' => 'array',
        'approval_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function job_card()
    {
        return $this->belongsTo(JobCard::class);
    }
}
