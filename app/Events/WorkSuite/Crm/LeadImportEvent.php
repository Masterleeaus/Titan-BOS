<?php

namespace App\Events\WorkSuite\Crm;

use App\Models\Lead;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadImportEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notificationName;
    /**
     * @var Lead
     */
    public $leadContact;

    public function __construct(Lead $leadContact, $notificationName)
    {
        $this->leadContact = $leadContact;
        $this->notificationName = $notificationName;
    }

}
