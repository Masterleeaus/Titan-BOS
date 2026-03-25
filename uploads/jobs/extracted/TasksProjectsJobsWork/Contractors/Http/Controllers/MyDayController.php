<?php
namespace Modules\Contractors\Http\Controllers;
use Illuminate\Http\Request; use Illuminate\Routing\Controller;
use Carbon\Carbon;
class MyDayController extends Controller {
  public function index(Request $r){
    $date = $r->input('date', now()->toDateString());
    $userId = auth()->id();
    $Assignment = \Modules\Contractors\Entities\WorkOrderContractorAssignment::class;
    $rows = $Assignment::with(['workOrder'])->whereDate('scheduled_at',$date)
      ->where('user_id',$userId)->orderBy('scheduled_at')->get();
    return view('contractors::myday.index', ['date'=>$date,'rows'=>$rows]);
  }
  public function status(Request $r, $id){
    $Assignment = \Modules\Contractors\Entities\WorkOrderContractorAssignment::class;
    $a = $Assignment::findOrFail($id);
    $this->authorize('update',$a);
    $action = $r->input('action');
    $now = now();
    if ($action==='accept'){ $a->status='accepted'; $a->accepted_at=$now; }
    elseif ($action==='en_route'){ $a->status='en_route'; $a->en_route_at=$now; }
    elseif ($action==='start'){ $a->status='in_progress'; $a->started_at=$now; }
    elseif ($action==='complete'){ $a->status='completed'; $a->completed_at=$now; }
    $a->save();
    return back()->with('status','Updated.');
  }
}