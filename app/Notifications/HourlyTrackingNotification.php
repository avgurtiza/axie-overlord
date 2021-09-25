<?php

namespace App\Notifications;

use App\HourlyTracking;
use App\Models\Account;
use App\Models\Balance;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class HourlyTrackingNotification extends Notification
{
    use Queueable;

    private string $body;
    private HourlyTracking $hourlyTracking;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(\DateTime $current_time = null)
    {
        $this->body = "";

        $balances = new Collection();

        /** @var Account $account */
        foreach (Account::active()->get() as $account) {
            $starting_balance = $account->getTodaysStartingBalance(8, $current_time)->total;

            $current_balance = $account->getTodaysLatestBalance(8, $current_time)->total;

            $delta = $current_balance - $starting_balance;

            $balances->push((object)["title" => $account->title, "delta" => $delta, "starting_balance" => $starting_balance]);
        }

        $increment = 0;

        $balances->sortByDesc("delta")->values()->each(function ($item) use (&$increment) {
            $love = "";

            if ($item->delta < 120) {
                $love = ":meow_yikes:";
            }

            if ($item->delta >= 150) {
                $love = ":meow_wow:";
            }

            if ($item->delta >= 180) {
                $love = $increment > 0 ? ":meow_heart:" : ":meow_starstruck:";
            }

            $this->body .= sprintf("%s %s - %d [Start %d] SLP \n\n", $love, $item->title, $item->delta, $item->starting_balance);

            $increment++;
        });


        print_r($this->body);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ["slack"];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->content($this->body);
    }
}
