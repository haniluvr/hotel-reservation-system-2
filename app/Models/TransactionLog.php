<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionLog extends Model
{
    protected $fillable = [
        'reservation_id',
        'room_id',
        'action',
        'before_state',
        'after_state',
        'quantity_change',
        'description',
        'performed_by',
    ];

    protected $casts = [
        'before_state' => 'array',
        'after_state' => 'array',
    ];

    /**
     * Get the reservation that owns the transaction log.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the room that owns the transaction log.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}

