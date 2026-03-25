<?php

namespace App\Http\Controllers\WorkSuite\Jobs;

use App\Http\Controllers\WorkSuite\WorkSuiteBaseController;
use Illuminate\Http\Request;

class MilestoneController extends WorkSuiteBaseController
{
    public function create($job)
    {
        return view('worksuite.jobs.milestones.create', $this->withLabels(['job' => $job]));
    }

    public function store(Request $request, $job)
    {
        return redirect()->back()->with('success', 'Milestone created.');
    }

    public function edit($job, $milestone)
    {
        return view('worksuite.jobs.milestones.edit', $this->withLabels(['job' => $job, 'milestone' => $milestone]));
    }

    public function update(Request $request, $job, $milestone)
    {
        return redirect()->back()->with('success', 'Milestone updated.');
    }

    public function destroy($job, $milestone)
    {
        return redirect()->back()->with('success', 'Milestone deleted.');
    }
}
