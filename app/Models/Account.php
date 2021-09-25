<?php

namespace App\Models;

use App\Services\Lunacia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property mixed ethereum_wallet
 */
class Account extends Model
{
    use HasFactory;

    /**
     * @var Lunacia
     */
    private $lunacia;

    public function __construct()
    {
        parent::__construct();
        $this->lunacia = resolve(Lunacia::class);
    }

    /**
     * @return \App\Balance|false
     */
    public function getSlpBalance()
    {
        $wallet_hash = preg_replace("/^ronin:/", "0x", $this->ronin_wallet);

        return $this->lunacia->getSlpBalance($wallet_hash);
    }

    public function insertBalance(\App\Balance $balance)
    {
        return $this->balance()->create([
            'total' => $balance->total,
            'claimable' => $balance->claimable,
            'ingame' => $balance->ingame,
            'last_claim' => $balance->last_claim,
            'item_id' => $balance->item_id,
        ]);
    }

    public function getTodaysStartingBalanceA($hour_cutoff = 8)
    {
        $today = Carbon::today();

        if (now()->hour < $hour_cutoff) {
            return $this->balance()->where("created_at", "<", $today->hour($hour_cutoff))->latest()->first();
        } else {
            $today = Carbon::today();
            return $this->balance()->where("created_at", ">=", $today->hour($hour_cutoff))->first();
        }

    }

    /**
     * @param int $hour_cutoff
     * @param Carbon|null $current_time
     * @return Model|HasMany|object|null
     */
    public function getTodaysStartingBalance(int $hour_cutoff = 8, \DateTime $current_time = null)
    {
        if(!$current_time instanceof \DateTime) {
            $current_time = now();
        }

        if ($current_time->hour < $hour_cutoff) {
            $today = Carbon::yesterday();
        } else {
            $today = Carbon::today();
        }

        return $this->balance()->where("created_at", ">=", $today->hour($hour_cutoff))->first();
    }

    /**
     * @param int $hour_cutoff
     * @param Carbon|null $current_time
     * @return Model|HasMany|object|null
     */
    public function getTodaysLatestBalance(int $hour_cutoff = 8, \DateTime $current_time = null)
    {
        if(!$current_time instanceof \DateTime) {
            $current_time = now();
        }

        if ($current_time->hour < $hour_cutoff) {
            echo "Getting latest bal from yesterday\n";

            $today = Carbon::yesterday();
        } else {
            echo "Getting latest bal from today\n";

            $today = Carbon::today();
        }

        return $this->balance()
            ->where("created_at", ">=", $today->copy()->startOfDay()->addHours($hour_cutoff))
            ->where("created_at", "<", $today->copy()->endOfDay()->addHours($hour_cutoff))
            ->latest()->first();
    }

    public function balance(): HasMany
    {
        return $this->hasMany(Balance::class);
    }

    public function scopeActive() {
        return $this->where("is_active" , 1);
    }


    public function scopeInactive() {
        return $this->where("is_active" , 0);
    }
}
