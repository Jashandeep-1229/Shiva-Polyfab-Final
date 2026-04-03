<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lead_statuses';

    protected $fillable = ['name', 'slug', 'sort_order', 'color', 'status', 'is_required', 'form_fields'];

    public function leads()
    {
        return $this->hasMany(Lead::class, 'status_id');
    }
}
