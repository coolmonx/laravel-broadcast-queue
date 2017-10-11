<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Http\Request;
use \DateTime;
use Illuminate\Support\Facades\Redis;

class TestEvent extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $data;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($customer_id) {

        $maxqueuetime = 3600; // 1 hour
        $maxbrowsingtime = 900; // 15 minutes
        $maxbrowsingusers = 4; 

        $alreadyinqueue = 0;    
        $browsingusers = 0;

        error_log('Received! '.$customer_id);
        $date = new DateTime();
        $timestamp = $date->getTimestamp();

        $keys = Redis::get('test-channel');
        $keys = json_decode($keys);
        if(count($keys) == 0) {
            // No keys in redis
            error_log('Nothing in Redis !!');

            Redis::set('test-channel', json_encode(array([
                'status' => 'browsing',
                'customer' => $customer_id,
                'timestamp' => $timestamp,
                'waittime' => 0
            ]
            )));

            $this->data = [
                'status' => 'browsing',
                'customer' => $customer_id,
                'timestamp' => $timestamp,
                'waittime' => 0
            ];
        } else {
            // Get queue from Redis
            error_log("Queue in redis: ".count($keys));

            // Get number of browsing users
            foreach ($keys as $key) {                
                if ($key->status == 'browsing') {
                    $browsingusers = $browsingusers + 1;
                }
            }
            error_log("Browsing users: ".$browsingusers);
        
            $channels = [];
            foreach ($keys as $key) {            
                $channel = new \stdClass;

                // if current browsing user is less than max allow browsing user, move them
                if ($browsingusers < $maxbrowsingusers && $key->status == 'queueing') {
                    $channel->status = 'browsing';
                    $channel->customer = $key->customer;
                    $channel->timestamp = $timestamp;
                    $channel->waittime = 0;
                    $browsingusers = $browsingusers + 1;
                } else {
                    // else remain the same
                    $channel->status = $key->status;
                    $channel->customer = $key->customer;
                    $channel->timestamp = $key->timestamp;
                    $channel->waittime = $timestamp - $key->timestamp;
                }                

                // check null
                if ($key->customer) {
                    // check max time in queue
                    if ($channel->status == 'queueing') {
                        if ($channel->waittime < $maxqueuetime) {
                            $channels[] = $channel;
                        }
                    }

                    // check max time in browsing
                    if ($channel->status == 'browsing') {
                        if ($channel->waittime < $maxbrowsingtime) {
                            $channels[] = $channel;
                        }
                    }
                }

                // check duplicate
                if ($key->customer == $customer_id) {
                    $alreadyinqueue = 1;
                    error_log("Customer already in queue.");
                }
            }

            if ($alreadyinqueue != 1) {
                $new_channel = new \stdClass;
                if ($browsingusers < $maxbrowsingusers) {
                    $new_channel->status = 'browsing';
                } else {
                    $new_channel->status = 'queueing';
                }
                $new_channel->customer = $customer_id;
                $new_channel->timestamp = $timestamp;
                $new_channel->waittime = 0;
                $channels[] = $new_channel;
            }
            $alreadyinqueue = 0;

            // Save Redis
            Redis::set('test-channel', json_encode($channels));
            $this->data = $channels;
        }
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
