<?php

namespace App\Http\Controllers\WorkSuite\Customers;

use App\Http\Controllers\WorkSuite\WorkSuiteBaseController;
use Illuminate\Http\Request;

class FollowUpController extends WorkSuiteBaseController
{
    public function index()
    {
        return view('worksuite.followups.index', $this->withLabels());
    }

    public function store(Request $request)
    {
        return redirect()->back()->with('success', 'Follow-up created.');
    }

    public function update(Request $request, $id)
    {
        return redirect()->back()->with('success', 'Follow-up updated.');
    }

    public function destroy($id)
    {
        return redirect()->back()->with('success', 'Follow-up deleted.');
    }

    public function complete($id)
    {
        return redirect()->back()->with('success', 'Follow-up marked complete.');
    }
}
