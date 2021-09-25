<?php


namespace App;


use Illuminate\Support\Collection;

class HourlyTracking
{
    private Collection $balances;

    public function __construct() {
        $this->balances = new Collection();
    }

    public function addBalance(\App\Models\Balance $balance) {
        $this->balances->push($balance);
    }

    public function getBalances(): Collection
    {
        return $this->balances;
    }

    public function toArray(): array
    {
        return $this->balances->toArray();
    }
}
