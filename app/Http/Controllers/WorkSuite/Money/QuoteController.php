<?php

namespace App\Http\Controllers\WorkSuite\Money;

use App\Http\Controllers\WorkSuite\WorkSuiteBaseController;
use Illuminate\Http\Request;

class QuoteController extends WorkSuiteBaseController
{
    public function index()
    {
        return view('worksuite.money.quotes.index', $this->withLabels());
    }

    public function create()
    {
        return view('worksuite.money.quotes.create', $this->withLabels());
    }

    public function store(Request $request)
    {
        return redirect()->back()->with('success', 'Created successfully.');
    }

    public function show($id)
    {
        return view('worksuite.money.quotes.show', $this->withLabels(['id' => $id]));
    }

    public function edit($id)
    {
        return view('worksuite.money.quotes.edit', $this->withLabels(['id' => $id]));
    }

    public function update(Request $request, $id)
    {
        return redirect()->back()->with('success', 'Updated successfully.');
    }

    public function destroy($id)
    {
        return redirect()->back()->with('success', 'Deleted successfully.');
    }
}
