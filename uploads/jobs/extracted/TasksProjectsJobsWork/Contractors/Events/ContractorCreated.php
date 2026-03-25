<?php

namespace Modules\Contractors\Events;

use Modules\Contractors\Entities\Contractor;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContractorCreated {
    use Dispatchable, SerializesModels;
    public $contractor;
    public function __construct(Contractor $contractor) { $this->contractor = $contractor; }
}
