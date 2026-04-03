<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadTag extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'color', 'status'];

    public function leads()
    {
        return $this->belongsToMany(Lead::class, 'lead_tag_pivot', 'tag_id', 'lead_id');
    }
}
