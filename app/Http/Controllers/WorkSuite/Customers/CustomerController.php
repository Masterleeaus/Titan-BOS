<?php

namespace App\Http\Controllers\WorkSuite\Customers;

use App\Http\Controllers\WorkSuite\WorkSuiteBaseController;
use Illuminate\Http\Request;

class CustomerController extends WorkSuiteBaseController
{
    public function index()
    {
        return view('worksuite.customers.index', ->withLabels());
    }

    public function create()
    {
        return view('worksuite.customers.create', ->withLabels());
    }

    public function store(Request $request)
    {
        // TODO: implement store
        return redirect()->back()->with('success', 'Created successfully.');
    }

    public function show($id)
    {
        return view('worksuite.customers.show', ->withLabels(['id' => $id]));
    }

    public function edit($id)
    {
        return view('worksuite.customers.edit', ->withLabels(['id' => $id]));
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
