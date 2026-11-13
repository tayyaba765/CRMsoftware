<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'module_id',
        'parent_role_id',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function parentRole()
    {
        return $this->belongsTo(Role::class, 'parent_role_id');
    }

    public function childRoles()
    {
        return $this->hasMany(Role::class, 'parent_role_id');
    }

    /**
     * Determine if this role is a protected system role (superadmin or crmadmin).
     */
    public function isProtected(): bool
    {
        $clean = str_replace([' ', '-', '_'], '', strtolower($this->name));
        return in_array($clean, ['superadmin', 'crmadmin'], true);
    }
}
