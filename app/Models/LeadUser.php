<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Database\Eloquent\SoftDeletes;

class LeadUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, AutoLogsActivity;

    protected $table = 'lead_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'show_password',
        'parent_id',
        'role',
        'phone',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Hierarchy Relationship
     */
    public function parent()
    {
        return $this->belongsTo(LeadUser::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(LeadUser::class, 'parent_id');
    }

    public function leads()
    {
        return $this->hasMany('App\Models\Lead', 'assigned_user_id');
    }

    public function agentLeads()
    {
        return $this->hasMany('App\Models\AgentLead', 'assigned_user_id');
    }

    public function getViewableUserIds()
    {
        if ($this->role == 'Admin') {
            return LeadUser::pluck('id')->toArray();
        }
        
        $ids = [$this->id];
        if ($this->role == 'Senior Sale Executive') {
            $childIds = LeadUser::where('parent_id', $this->id)->pluck('id')->toArray();
            $ids = array_merge($ids, $childIds);
        }
        return $ids;
    }
}
