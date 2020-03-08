<?php

namespace GTCrais\Ion\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class EntityObjectStored
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

	public $object;
	public $filteredInput;
	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * Create a new event instance.
	 *
	 * @param $object
	 * @param $filteredInput
	 */
    public function __construct($object, $filteredInput)
    {
		$this->object = $object;
		$this->filteredInput = $filteredInput;
	}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        //return new PrivateChannel('channel-name');
    }
}
