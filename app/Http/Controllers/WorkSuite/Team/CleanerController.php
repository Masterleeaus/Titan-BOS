<?php

namespace App\Http\Controllers\WorkSuite\Team;

use App\Http\Controllers\WorkSuite\WorkSuiteBaseController;
use Illuminate\Http\Request;

class CleanerController extends WorkSuiteBaseController
{
    public function index()
    {
        return view('worksuite.team.index', ->withLabels());
    }

    public function create()
    {
        return view('worksuite.team.create', ->withLabels());
    }

    public function store(Request $request)
    {
        // TODO: implement store
        return redirect()->back()->with('success', 'Created successfully.');
    }

    public function show($id)
    {
        return view('worksuite.team.show', ->withLabels(['id' => $id]));
    }

    public function edit($id)
    {
        return view('worksuite.team.edit', ->withLabels(['id' => $id]));
    }

    public function update(Request $request, $id)
    {
        // TODO: implement update
        return redirect()->back()->with('success', 'Updated successfully.');
    }

    public function destroy($id)
    {
        // TODO: implement destroy
        return redirect()->back()->with('success', 'Deleted successfully.');
    }
}
