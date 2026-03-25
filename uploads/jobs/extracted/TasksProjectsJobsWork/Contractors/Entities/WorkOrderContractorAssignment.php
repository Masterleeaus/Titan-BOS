<?php

namespace Modules\Contractors\Entities;

use Illuminate\Database\Eloquent\Model;

class WorkOrderContractorAssignment extends Model
{
    protected $table = 'work_order_contractor_assignments';
    protected $guarded = [];

    public function workOrder()
    {
        return $this->belongsTo(\Modules\WorksuiteWorkOrders\Entities\WorkOrder::class, 'work_order_id');
    }

    public function contractor()
    {
        return $this->belongsTo(\Modules\Contractors\Entities\Contractor::class, 'contractor_id');
    }
}
