<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Balance extends Model
{
    use HasFactory;

    protected $fillable = ['total', 'claimable','last_claim', 'item_id', 'ingame'];
    protected $dates = ['created_at', 'updated_at'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
