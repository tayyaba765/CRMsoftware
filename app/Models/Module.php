<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
       protected $fillable = ['name'];

    public function roles()
    {
        return $this->hasMany(Role::class);
    }
    public function permissions(): HasMany
{
    return $this->hasMany(Permission::class);
}
}
