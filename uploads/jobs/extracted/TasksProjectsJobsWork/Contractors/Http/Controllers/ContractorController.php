<?php

namespace Modules\Contractors\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Modules\Contractors\Entities\Contractor;
use Modules\Contractors\Http\Requests\StoreContractorRequest;
use Modules\Contractors\Http\Requests\UpdateContractorRequest;

class ContractorController extends Controller
{
    private function logActivity($action, $model, array $meta = []) {
        try {
            \DB::table('contractor_activity_logs')->insert([
                'contractor_id' => $model ? $model->id : null,
                'user_id' => optional(auth()->user())->id,
                'action' => $action,
                'meta' => json_encode($meta),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) { /* noop */ }
    }

    public function ping()
    {
        return response()->json([
            'module' => 'Contractor',
            'status' => 'ok',
            'time' => now()->toDateTimeString(),
        ]);
    }

    public function index()
    {
        $this->authorizeIfPossible('contractor.viewAny', Contractor::class);

        $items = Contractor::orderBy('id','desc')->paginate(20);

        return view('contractor::contractors.index', compact('items'));
    }

    public function create()
    {
        $this->authorizeIfPossible('contractor.create', Contractor::class);
        return view('contractor::contractors.create');
    }

    public function store(StoreContractorRequest $request)
    {
        $this->authorizeIfPossible('contractor.create', Contractor::class);

        $data = $request->validated();
        $data['company_id'] = $data['company_id'] ?? optional(auth()->user())->company_id;
        $item = Contractor::create($data);
        event(new \Modules\Contractors\Events\ContractorCreated($item));
        $this->logActivity('created', $item);

        return redirect()->route('contractor.index')->with('status', 'Contractor created.');
    }

    public function edit(Contractor $contractor)
    {
        $this->authorizeIfPossible('contractor.update', $contractor);
        return view('contractor::contractors.edit', ['item' => $contractor]);
    }

    public function update(UpdateContractorRequest $request, Contractor $contractor)
    {
        $this->authorizeIfPossible('contractor.update', $contractor);
        $contractor->update($request->validated());
        event(new \Modules\Contractors\Events\ContractorUpdated($contractor));
        $this->logActivity('updated', $contractor);
        return redirect()->route('contractor.index')->with('status', 'Contractor updated.');
    }

    public function destroy(Contractor $contractor)
    {
        $this->authorizeIfPossible('contractor.delete', $contractor);
        event(new \Modules\Contractors\Events\ContractorDeleted($contractor));
        $contractor->delete();
        $this->logActivity('deleted', $contractor);
        return redirect()->route('contractor.index')->with('status', 'Contractor deleted.');
    }

    // Helper to avoid crashes if policies not bound
    protected function authorizeIfPossible(string $ability, $arguments = []): void
    {
        try { $this->authorize($ability, $arguments); } catch (\Throwable $e) { /* noop */ }
    }
}


    public function exportCsv()
    {
        $this->authorizeIfPossible('contractor.viewAny', \Modules\Contractors\Entities\Contractor::class);
        $q = request('q'); $status = request('status'); $companyId = request('company_id');
        $query = \Modules\Contractors\Entities\Contractor::query();
        if ($q) { $query->where(function($qq) use ($q) { $qq->where('name','like',"%{$q}%")->orWhere('email','like',"%{$q}%")->orWhere('phone','like',"%{$q}%"); }); }
        if ($status) { $query->where('status', $status); }
        if ($companyId) { $query->where('company_id', $companyId); }

        $rows = $query->orderBy('id','desc')->get(['id','company_id','name','email','phone','status','created_at']);
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename=contractors.csv'];
        $callback = function() use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID','Company','Name','Email','Phone','Status','Created']);
            foreach ($rows as $r) { fputcsv($out, [$r->id, $r->company_id, $r->name, $r->email, $r->phone, $r->status, $r->created_at]); }
            fclose($out);
        };
        return response()->stream($callback, 200, $headers);
    }


    public function bulkUpdateStatus()
    {
        $this->authorizeIfPossible('contractor.update', \Modules\Contractors\Entities\Contractor::class);
        $ids = (array) request('ids', []);
        $status = request('status');
        if (!$ids || !in_array($status, ['active','inactive'])) { return back()->with('status', 'No items or invalid status.'); }
        \Modules\Contractors\Entities\Contractor::whereIn('id', $ids)->update(['status' => $status]);
        foreach ($ids as $id) { $this->logActivity('updated', \Modules\Contractors\Entities\Contractor::find($id), ['bulk' => true]); }
        return back()->with('status', 'Status updated.');
    }

    public function bulkDelete()
    {
        $this->authorizeIfPossible('contractor.delete', \Modules\Contractors\Entities\Contractor::class);
        $ids = (array) request('ids', []);
        if (!$ids) { return back()->with('status', 'No items selected.'); }
        foreach ($ids as $id) {
            $c = \Modules\Contractors\Entities\Contractor::find($id);
            if ($c) { $c->delete(); $this->logActivity('deleted', $c, ['bulk' => true]); }
        }
        return back()->with('status', 'Deleted selected.');
    }

    public function exportXlsx()
    {
        $this->authorizeIfPossible('contractor.viewAny', \Modules\Contractors\Entities\Contractor::class);
        // If Maatwebsite\Excel is available, use it; otherwise fallback to CSV with XLSX filename.
        if (class_exists('Maatwebsite\\Excel\\Facades\\Excel')) {
            $q = request('q'); $status = request('status'); $companyId = request('company_id');
            $query = \Modules\Contractors\Entities\Contractor::query();
            if ($q) { $query->where(function($qq) use ($q) { $qq->where('name','like',"%{$q}%")->orWhere('email','like',"%{$q}%")->orWhere('phone','like',"%{$q}%"); }); }
            if ($status) { $query->where('status', $status); }
            if ($companyId) { $query->where('company_id', $companyId); }
            $rows = $query->orderBy('id','desc')->get(['id','company_id','name','email','phone','status','created_at']);
            $export = new class($rows) implements \Maatwebsite\Excel\Concerns\FromArray {
                public function __construct(public $rows) {} 
                public function array(): array {
                    $out = [['ID','Company','Name','Email','Phone','Status','Created']];
                    foreach ($this->rows as $r) { $out[] = [$r->id, $r->company_id, $r->name, $r->email, $r->phone, $r->status, $r->created_at]; }
                    return $out;
                }
            };
            return \Maatwebsite\Excel\Facades\Excel::download($export, 'contractors.xlsx');
        } else {
            return $this->exportCsv(); // graceful fallback
        }
    }

    public function logs()
    {
        $items = \DB::table('contractor_activity_logs')->orderBy('id','desc')->paginate(50);
        return view('contractor::contractors.logs', compact('items'));
    }

    public function addTag($contractorId)
    {
        $tag = trim((string) request('tag'));
        if ($tag) {
            \DB::table('contractor_tags')->insert([
                'contractor_id' => $contractorId,
                'tag' => $tag,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        return back();
    }

    public function addNote($contractorId)
    {
        $note = trim((string) request('note'));
        if ($note) {
            \DB::table('contractor_notes')->insert([
                'contractor_id' => $contractorId,
                'user_id' => optional(auth()->user())->id,
                'note' => $note,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        return back();
    }
