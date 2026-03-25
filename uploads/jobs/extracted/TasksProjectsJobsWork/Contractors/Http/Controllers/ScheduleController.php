<?php

namespace Modules\Contractors\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Contractors\Entities\WorkOrderContractorAssignment as Assignment;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function day(Request $req)
    {
        $this->authorize('viewAny', Assignment::class);
        $date = $req->input('date', now()->toDateString());

        $start = Carbon::parse($date.' 00:00:00');
        $end   = Carbon::parse($date.' 23:59:59');

        $rows = Assignment::with(['contractor','workOrder'])
            ->whereBetween('scheduled_at', [$start, $end])
            ->orderBy('contractor_id')->orderBy('scheduled_at')->get();

        // Build conflicts per contractor
        $conflicts = [];
        $byContractor = $rows->groupBy('contractor_id');
        foreach ($byContractor as $cid => $items) {
            $prev_end = null;
            foreach ($items as $a) {
                $slot_start = Carbon::parse($a->scheduled_at);
                $minutes = $a->duration_minutes ?? 60;
                $slot_end = (clone $slot_start)->addMinutes($minutes);
                if ($prev_end and $slot_start < $prev_end) {
                    $conflicts[$cid] = true;
                }
                $prev_end = max($prev_end or $slot_end, $slot_end);
            }
        }

        return view('contractors::schedule.day', [
            'date' => $date,
            'rows' => $rows,
            'conflicts' => $conflicts,
        ]);
    }
}
