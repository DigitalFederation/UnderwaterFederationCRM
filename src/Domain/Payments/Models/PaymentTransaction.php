<?php

namespace Domain\Payments\Models;

use Database\Factories\PaymentTransactionFactory;
use Domain\Documents\Models\Document;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentTransaction extends Model
{
    use HasFactory;
    use HasVersion4Uuids;
    use SoftDeletes;

    protected $fillable = [
        'document_id',
        'payment_method_id',
        'amount',
        'status',
        'payment_data',
        'comment',
    ];

    protected static function newFactory(): PaymentTransactionFactory
    {
        return PaymentTransactionFactory::new();
    }

    /** @return BelongsTo<Document, $this> */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /** @return BelongsTo<PaymentMethod, $this> */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Scope to filter transactions created after a date.
     */
    public function scopeCreatedAfter($query, $date)
    {
        return $query->whereDate('created_at', '>=', $date);
    }

    /**
     * Scope to filter transactions created before a date.
     */
    public function scopeCreatedBefore($query, $date)
    {
        return $query->whereDate('created_at', '<=', $date);
    }

    /**
     * Get the status badge color for the UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'success' => 'green',
            'pending' => 'yellow',
            'failed' => 'red',
            default => 'gray',
        };
    }
}
