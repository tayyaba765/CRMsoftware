<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'source',
        'email',
        'password',
        'avatar',
        'cnic',
        'phone',
        'address_1',
        'address_2',
        'latitude',
        'longitude',
        'status',
        'assigned_to',
        'job_type',
        'income',
        'education',
        'age_group',
        'facebook_link',
        'instagram_link',
        'linkedin_link',
        'threads_link',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the user assigned to this profile (e.g., sales agent/staff).
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Helper accessor for full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Helper accessor for avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && file_exists(public_path('uploads/avatars/' . $this->avatar))) {
            return asset('uploads/avatars/' . $this->avatar);
        }

        $name = urlencode($this->full_name ?: 'User');
        return "https://ui-avatars.com/api/?name={$name}&background=5b3fd4&color=fff";
    }
}
