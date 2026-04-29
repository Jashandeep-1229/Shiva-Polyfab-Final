<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadTag extends Model
{
    use HasFactory, SoftDeletes, AutoLogsActivity;

    protected $fillable = ['name', 'color', 'status'];

    public function leads()
    {
        return $this->belongsToMany(Lead::class, 'lead_tag_pivot', 'tag_id', 'lead_id');
    }
}
