<?php

namespace App\Http\Controllers\WorkSuite\Team;

use App\Http\Controllers\WorkSuite\WorkSuiteBaseController;
use Illuminate\Http\Request;

class LeaveController extends WorkSuiteBaseController
{
    public function index()
    {
        return view('worksuite.team.leave.index', $this->withLabels());
    }

    public function store(Request $request)
    {
        return redirect()->back()->with('success', 'Created successfully.');
    }

    public function update(Request $request, $id)
    {
        return redirect()->back()->with('success', 'Updated successfully.');
    }

    public function approve($id)
    {
        return redirect()->back()->with('success', 'Leave approved.');
    }

    public function reject($id)
    {
        return redirect()->back()->with('success', 'Leave rejected.');
    }

    public function destroy($id)
    {
        return redirect()->back()->with('success', 'Deleted successfully.');
    }
}
