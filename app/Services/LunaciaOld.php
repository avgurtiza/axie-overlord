<?php


namespace App\Services;


use App\Balance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Lunacia
{
    const AXIE_SLP = 1;

    /**
     * @var string
     */
    private $baseUrl;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param string $wallet_hash
     * @return Balance|false
     */
    public function getSlpBalance(string $wallet_hash)
    {
        /*
        if (config("services.lunacia.uses_ethereum_address")) {
            $wallet_hash = preg_replace("/^ronin:/", "0x", $wallet_hash);
        }
        */

        return $this->getBalance($wallet_hash, self::AXIE_SLP);
    }

    private function getBalance(string $wallet_hash, $type)
    {

        $url = sprintf("%s/game-api/clients/%s/items/%s", $this->baseUrl, $wallet_hash, $type);

        $response = Http::withHeaders([
            'User-Agent' => 'curl/7.64.1',
            'Accept' => '*/*'
        ])->acceptJson()
            ->get($url);

        if ($response->failed()) {
            logger()->error("Could not get balance!", [
                'wallet' => $wallet_hash,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        $data = json_decode($response->body());

        // logger(print_r($data, true));

        $balance = new Balance(
            $data->client_id,
            $data->item_id,
            $data->total,
            $data->claimable_total,
            $data->last_claimed_item_at ? Carbon::parse($data->last_claimed_item_at) : null
        );

        logger("Got balance!", [
            'wallet' => $wallet_hash,
            'type' => $type,
            'response' => $balance
        ]);

        return $balance;
    }
}
