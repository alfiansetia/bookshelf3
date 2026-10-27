<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $provider
 * @property string $query_hash
 * @property string $query
 * @property array<int, array<string, mixed>> $results
 * @property int $hits
 * @property Carbon $fetched_at
 * @property Carbon|null $expires_at
 */
class BookLookupCache extends Model
{
    protected $table = 'book_lookup_cache';

    protected $fillable = [
        'provider',
        'query_hash',
        'query',
        'results',
        'hits',
        'fetched_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'results' => 'array',
            'hits' => 'integer',
            'fetched_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function isFresh(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isFuture();
    }
}
