<?php

namespace App\Http\Controllers\WorkSuite\ServiceRequests;

use App\Http\Controllers\WorkSuite\WorkSuiteBaseController;
use Illuminate\Http\Request;

class TicketController extends WorkSuiteBaseController
{
    public function index()
    {
        return view('worksuite.service-requests.index', $this->withLabels());
    }

    public function create()
    {
        return view('worksuite.service-requests.create', $this->withLabels());
    }

    public function store(Request $request)
    {
        return redirect()->back()->with('success', 'Service request created.');
    }

    public function show($ticket)
    {
        return view('worksuite.service-requests.show', $this->withLabels(['id' => $ticket]));
    }

    public function close($ticket)
    {
        return redirect()->back()->with('success', 'Service request closed.');
    }

    public function reply(Request $request, $ticket)
    {
        return redirect()->back()->with('success', 'Reply sent.');
    }
}
