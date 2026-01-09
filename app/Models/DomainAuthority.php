<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DomainAuthority extends Model
{
    protected $fillable = [
        'website_id',
        'domain_authority',
        'page_authority',
        'spam_score',
        'backlinks',
        'referring_domains',
        'raw_data',
    ];

    protected $casts = [
        'domain_authority' => 'integer',
        'page_authority' => 'integer',
        'spam_score' => 'integer',
        'backlinks' => 'integer',
        'referring_domains' => 'integer',
    ];

    /**
     * Get the website that owns the domain authority check
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
