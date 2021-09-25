<?php


namespace App;


class Balance
{
    /**
     * @var int
     */
    public $client_id;
    /**
     * @var int
     */
    public $item_id;
    /**
     * @var int
     */
    public $total;

    /**
     * @var int
     */
    public $claimable;

    /**
     * @var int
     */
    public $ingame;
    /**
     * @var \DateTime
     */
    public $last_claim;

    public function __construct(
        string $client_id,
        int $total,
        int $ingame,
        \DateTime $last_claim = null)
    {
        $this->item_id = 1; // SLP
        $this->claimable = 0; // Legacy
        $this->client_id = $client_id;
        $this->total = $total;
        $this->ingame = $ingame;
        $this->last_claim = $last_claim;
    }

    public function toArray(): array
    {
        return [
            'client_id'=>$this->client_id,
            'total'=>$this->total,
            'ingame'=>$this->ingame,
            'last_claim'=>$this->last_claim,
        ];
    }
}
