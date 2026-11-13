<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'size',
        'website_domain',
        'source',
        'logo',
        'organization_type',
        'revenue',
        'ownership_type',
        'phone',
        'phone_2',
        'address',
        'address_2',
        'latitude',
        'longitude',
        'tier',
        'status',
        'assigned_to',
        'focal_person',
        'cnic',
    ];

    /**
     * Get the user assigned to this organization (e.g., sales agent/account manager).
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Helper accessor for logo URL.
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo && file_exists(public_path('uploads/organizations/' . $this->logo))) {
            return asset('uploads/organizations/' . $this->logo);
        }

        $name = urlencode($this->name ?: 'Organization');
        return "https://ui-avatars.com/api/?name={$name}&background=5b3fd4&color=fff&rounded=true";
    }

    /**
     * Formatted revenue display.
     */
    public function getFormattedRevenueAttribute(): string
    {
        if ($this->revenue === null || $this->revenue === '') {
            return '—';
        }

        return '$' . number_format((float) $this->revenue, 2);
    }
}
