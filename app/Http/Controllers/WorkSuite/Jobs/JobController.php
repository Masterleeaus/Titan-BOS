<?php

namespace App\Http\Controllers\WorkSuite\Jobs;

use App\Http\Controllers\WorkSuite\WorkSuiteBaseController;
use Illuminate\Http\Request;

class JobController extends WorkSuiteBaseController
{
    public function index()
    {
        return view('worksuite.jobs.index', $this->withLabels());
    }

    public function create()
    {
        return view('worksuite.jobs.create', $this->withLabels());
    }

    public function store(Request $request)
    {
        return redirect()->back()->with('success', 'Job created.');
    }

    public function show($id)
    {
        return view('worksuite.jobs.show', $this->withLabels(['id' => $id]));
    }

    public function edit($id)
    {
        return view('worksuite.jobs.edit', $this->withLabels(['id' => $id]));
    }

    public function update(Request $request, $id)
    {
        return redirect()->back()->with('success', 'Job updated.');
    }

    public function destroy($id)
    {
        return redirect()->back()->with('success', 'Job deleted.');
    }

    public function timerStart(Request $request, $job)
    {
        return response()->json(['status' => 'started', 'job' => $job]);
    }

    public function timerStop(Request $request, $job)
    {
        return response()->json(['status' => 'stopped', 'job' => $job]);
    }
}
