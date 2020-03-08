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

class EntityObjectUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

	public $object;
	public $originalObject;
	public $filteredInput;
	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * Create a new event instance.
	 *
	 * @param $object
	 * @param $originalObject
	 * @param $filteredInput
	 */
    public function __construct($object, $originalObject, $filteredInput)
    {
		$this->object = $object;
		$this->originalObject = $originalObject;
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
