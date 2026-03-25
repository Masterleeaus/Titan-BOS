<?php

namespace Modules\Contractors\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Contractors\Entities\WorkOrderContractorAssignment as Assignment;

class AssignmentController extends Controller
{
    public function index(Request $req)
    {
        $this->authorize('viewAny', Assignment::class);
        $q = Assignment::query()->with(['workOrder','contractor'])->latest();
        if ($req->filled('work_order_id')) $q->where('work_order_id', $req->integer('work_order_id'));
        if ($req->filled('contractor_id')) $q->where('contractor_id', $req->integer('contractor_id'));
        return view('contractors::assignments.index', ['rows'=>$q->paginate(20)]);
    }

    public function store(Request $req)
    {
        $this->authorize('create', Assignment::class);
        $data = $req->validate([
            'work_order_id' => ['required','integer','min:1'],
            'contractor_id' => ['required','integer','min:1'],
            'role' => ['nullable','string','max:120'],
            'scheduled_at' => ['nullable','date'],
            'status' => ['nullable','string','in:assigned,accepted,in_progress,completed,cancelled']
        ]);
        $a = Assignment::create($data);
        return redirect()->back()->with('status', 'Assigned contractor #'.$a->contractor_id.' to work order #'.$a->work_order_id);
    }

    public function destroy($id)
    {
        $a = Assignment::findOrFail($id);
        $this->authorize('delete', $a);
        $a->delete();
        return redirect()->back()->with('status', 'Assignment removed.');
    }
}
