<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdfScrapedRecord extends Model
{
    use HasFactory;

    protected $table = 'pdf_scraped_records';

    protected $fillable = [
        'source_url',
        'source_name',
        'business_name',
        'category',
        'address',
        'contact_person',
        'phone',
        'phone_2',
        'email',
        'website_domain',
        'status',
        'raw_data',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'raw_data' => 'array',
    ];

    /**
     * User who created/scraped this record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if a data array satisfies the minimal field requirement
     * (at least one of business_name, contact_person, phone, or email).
     */
    public static function hasMinimumFields(array $data): bool
    {
        return !empty(trim($data['business_name'] ?? ''))
            || !empty(trim($data['contact_person'] ?? ''))
            || !empty(trim($data['phone'] ?? ''))
            || !empty(trim($data['email'] ?? ''));
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Scope to filter by source name.
     */
    public function scopeSource($query, $source)
    {
        if ($source) {
            return $query->where('source_name', $source);
        }
        return $query;
    }
}
