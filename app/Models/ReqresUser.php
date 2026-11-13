<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReqresUser extends Model
{
    use HasFactory;

    protected $table = 'reqres_users';

    protected $fillable = [
        'reqres_id',
        'email',
        'first_name',
        'last_name',
        'avatar',
    ];

    /**
     * Accessor: Get full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}