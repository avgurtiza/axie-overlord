<?php

namespace App\Console\Commands;

use App\HourlyTracking;
use App\Models\Account;
use App\Notifications\HourlyTrackingNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lunacia:update-balances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Starting! " . now()->toDateTimeString());

        // $hourlyTracking = new HourlyTracking();

        /** @var Account $account */

        foreach (Account::active()->get() as $account) {
            $this->info("Processing {$account->title} ({$account->ronin_wallet})...");

            if($balance = $account->getSlpBalance()) {
                $balance_record = $account->insertBalance($balance);
                // $hourlyTracking->addBalance($balance_record);
            } else {
                logger()->error("Failed to get balance for {$account->title} ({$account->ronin_wallet})!");
            }

            sleep(rand(2,5));
        }


        $hour_now  = now()->hour;

        if($hour_now > 6 && $hour_now < 23) {
            Notification::route('slack', config("logging.channels.slack.url"))->notify(new HourlyTrackingNotification());
        }

        return 0;
    }
}
