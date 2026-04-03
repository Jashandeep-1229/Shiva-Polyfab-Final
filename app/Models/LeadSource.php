<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Lead;

class LeadSource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'status'];

    public function leads()
    {
        return $this->hasMany('App\Models\Lead', 'source_id');
    }
}
