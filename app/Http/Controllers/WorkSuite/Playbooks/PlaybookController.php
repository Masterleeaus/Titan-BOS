<?php

namespace App\Http\Controllers\WorkSuite\Playbooks;

use App\Http\Controllers\WorkSuite\WorkSuiteBaseController;
use Illuminate\Http\Request;

class PlaybookController extends WorkSuiteBaseController
{
    public function index()
    {
        return view('worksuite.playbooks.index', ->withLabels());
    }

    public function create()
    {
        return view('worksuite.playbooks.create', ->withLabels());
    }

    public function store(Request $request)
    {
        // TODO: implement store
        return redirect()->back()->with('success', 'Created successfully.');
    }

    public function show($id)
    {
        return view('worksuite.playbooks.show', ->withLabels(['id' => $id]));
    }

    public function edit($id)
    {
        return view('worksuite.playbooks.edit', ->withLabels(['id' => $id]));
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
