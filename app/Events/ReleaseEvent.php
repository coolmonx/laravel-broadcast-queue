<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Http\Request;
use \DateTime;
use Illuminate\Support\Facades\Redis;

class ReleaseEvent extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $data;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($customer_id) {

        error_log('Released! '.$customer_id);

        $channels = [];
        $isbrowsinguser = 0;

        $date = new DateTime();
        $timestamp = $date->getTimestamp();

        $keys = Redis::get('test-channel');
        $keys = json_decode($keys);
        if(count($keys) != 0) {
            // Search for customer id
            foreach ($keys as $key) {       

                $channel = new \stdClass;
                if ($isbrowsinguser == 1 && $key->status == 'queueing') {
                    // change its status
                    $channel->status = 'browsing';
                    $channel->customer = $key->customer;
                    $channel->timestamp = $timestamp;
                    $channel->waittime = 0;
                    $isbrowsinguser = 0;
                } else {
                    $channel->status = $key->status;
                    $channel->customer = $key->customer;
                    $channel->timestamp = $key->timestamp;
                    $channel->waittime = $timestamp - $key->timestamp;
                }

                if ($customer_id == $key->customer) {
                    // found it , is it a browsing user?
                    if ($key->status == 'browsing') {
                        $isbrowsinguser = 1;
                    }
                } else {
                    // remain the same
                    $channels[] = $channel;
                }
            }
        }

        // Save Redis
        Redis::set('test-channel', json_encode($channels));

        // Delete All Queue
        if ($customer_id == 'all') {
            Redis::del('test-channel');
            $channels = [];
        }            

        $this->data = $channels;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['test-channel'];
    }
}