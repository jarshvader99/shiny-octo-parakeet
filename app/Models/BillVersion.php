<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'version_code',
        'version_name',
        'published_at',
        'full_text',
        'text_url',
        'pdf_url',
        'text_hash',
        'diff_from_previous',
        'page_count',
        'character_count',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * Get the bill this version belongs to.
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * Generate content hash for change detection.
     */
    public function generateTextHash(): string
    {
        return hash('sha256', $this->full_text ?? '');
    }
}
