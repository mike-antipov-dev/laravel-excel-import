<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RowCreatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $processed;

    public function __construct($processed)
    {
        $this->processed = $processed;
    }

    public function broadcastOn()
    {
        return new Channel('rows');
    }

    public function broadcastWith()
    {
        return [ 'Обработано строк: ' => $this->processed ];
    }

    public function broadcastAs()
    {
        return 'row.created';
    }
}
