<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Traits\AutoLogsActivity;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, AutoLogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'show_password',
        'role_as',
        'created_by_id',
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
        'otp_created_at' => 'datetime',
        'last_active_at' => 'datetime',
    ];
    /**
     * Users managed by this user (if this user is a manager).
     */
    public function manages()
    {
        return $this->belongsToMany(User::class, 'manager_user', 'manager_id', 'user_id');
    }

    /**
     * Managers managing this user.
     */
    public function managedBy()
    {
        return $this->belongsToMany(User::class, 'manager_user', 'user_id', 'manager_id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    /**
     * Get IDs of users managed by this user.
     */
    public function getManagedUserIds()
    {
        return $this->manages()->pluck('users.id')->toArray();
    }

    /**
     * Apply data visibility restrictions to a query.
     */
    public function applyDataRestriction($query, $column = 'user_id', $menu_key = null)
    {
        if ($this->role_as == 'Admin') {
            return $query;
        }

        // Check if access is explicitly set to "Public / All Data" in permissions
        if ($menu_key) {
            $permission = \App\Models\MenuPermission::where('menu_key', $menu_key)
                ->where(function($q) {
                    $q->where('user_id', $this->id)
                      ->orWhereRaw('LOWER(TRIM(role_name)) = ?', [strtolower(trim($this->role_as))]);
                })
                ->orderBy('user_id', 'desc') // prioritize user-specific over role
                ->first();

            if ($permission && $permission->data_access == 'all') {
                return $query;
            }
        }

        $ids = $this->getPermittedUserIds($menu_key);
        
        return $query->where(function($q) use ($column, $ids) {
            $cols = is_array($column) ? $column : [$column];
            foreach($cols as $index => $col) {
                if($index == 0) $q->whereIn($col, $ids);
                else $q->orWhereIn($col, $ids);
            }
        });
    }

    public function ledgerFollowups()
    {
        return $this->hasMany(LedgerFollowup::class, 'user_id');
    }

    /**
     * Get all permitted user IDs for a given menu, combining managed users, custom selections, and own ID.
     */
    public function getPermittedUserIds($menu_key = null)
    {
        $ids = [$this->id];

        // Manager / Senior SE hierarchy applies to these modules
        $hierarchy_menus = [
            'customer_ledger', 
            'customer_ledger_report', 
            'agent_customer', 
            'ledger_followups',
            'roto_orders',
            'common_orders',
            'common_product_stock'
        ];

        if (in_array($menu_key, $hierarchy_menus)) {
            if ($this->role_as != 'Admin') { 
                $ids = array_merge($ids, $this->getManagedUserIds());
            }
        }

        // For strictly own data (like followup creation ownership), we bypass hierarchy in the controller or use a specific key
        if ($menu_key === 'ledger_followup_private') {
            return [$this->id];
        }

        return array_unique($ids);
    }

    public function getRoleAttribute()
    {
        return $this->role_as;
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
        if ($this->role_as == 'Admin') {
            return User::pluck('id')->toArray();
        }
        
        $ids = [$this->id];
        $childIds = $this->getManagedUserIds();
        return array_unique(array_merge($ids, $childIds));
    }
}
