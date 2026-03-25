<?php

namespace App\Http\Controllers\WorkSuite\Money;

use App\Http\Controllers\WorkSuite\WorkSuiteBaseController;
use Illuminate\Http\Request;

class PaymentController extends WorkSuiteBaseController
{
    public function index()
    {
        return view('worksuite.money.payments.index', $this->withLabels());
    }

    public function create()
    {
        return view('worksuite.money.payments.create', $this->withLabels());
    }

    public function store(Request $request)
    {
        return redirect()->back()->with('success', 'Created successfully.');
    }

    public function show($id)
    {
        return view('worksuite.money.payments.show', $this->withLabels(['id' => $id]));
    }

    public function edit($id)
    {
        return view('worksuite.money.payments.edit', $this->withLabels(['id' => $id]));
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
