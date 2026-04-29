<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Lead;

class LeadSource extends Model
{
    use HasFactory, SoftDeletes, AutoLogsActivity;

    protected $fillable = ['name', 'status'];

    public function leads()
    {
        return $this->hasMany('App\Models\Lead', 'source_id');
    }
}
