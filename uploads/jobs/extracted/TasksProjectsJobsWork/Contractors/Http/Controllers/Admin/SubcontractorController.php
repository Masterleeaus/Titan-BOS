<?php

namespace Modules\Contractors\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Contractors\Models\Contractor;
use Illuminate\Support\Facades\Config;

class ContractorController extends Controller
{
    public function index()
    {
        $items = Contractor::latest()->paginate(20);
        return view('contractor::index', compact('items'));
    }

    public function create()
    {
        return view('contractor::create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'abn' => 'nullable|string|max:50',
            'rate_type' => 'nullable|in:hourly,fixed',
            'rate_amount' => 'nullable|numeric',
            'status' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);
        Contractor::create($data);
        return redirect()->route('admin.contractors.index')->with('status', 'Created');
    }

    public function show(Contractor $contractor)
    {
        return view('contractor::show', compact('contractor'));
    }

    public function edit(Contractor $contractor)
    {
        return view('contractor::edit', compact('contractor'));
    }

    public function update(Request $request, Contractor $contractor)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'abn' => 'nullable|string|max:50',
            'rate_type' => 'nullable|in:hourly,fixed',
            'rate_amount' => 'nullable|numeric',
            'status' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);
        $contractor->update($data);
        return redirect()->route('admin.contractors.index')->with('status', 'Updated');
    }

    public function destroy(Contractor $contractor)
    {
        $contractor->delete();
        return redirect()->route('admin.contractors.index')->with('status', 'Deleted');
    }

    // "Create with AI" demo endpoint: proposes a contractor entry from a prompt
    public function createWithAi(Request $request)
    {
        $prompt = $request->input('prompt');
        // NOTE: We do not call external APIs here. Wire to your AI client in app if desired.
        // This is a safe placeholder that echoes a structured suggestion.
        $suggestion = [
            'name' => 'Suggested Name',
            'company' => 'Suggested Company Pty Ltd',
            'rate_type' => 'hourly',
            'rate_amount' => 95.00,
            'status' => 'active',
            'notes' => 'AI-suggested profile based on prompt.'
        ];
        return response()->json(['prompt' => $prompt, 'suggestion' => $suggestion]);
    }
}
