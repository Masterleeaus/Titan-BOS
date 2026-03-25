<?php

namespace App\Http\Controllers\WorkSuite\Schedule;

use App\Http\Controllers\WorkSuite\WorkSuiteBaseController;
use Illuminate\Http\Request;

class ScheduleController extends WorkSuiteBaseController
{
    public function index()
    {
        return view('worksuite.schedule.index', $this->withLabels());
    }

    public function calendar()
    {
        return view('worksuite.schedule.calendar', $this->withLabels());
    }

    public function availability()
    {
        return view('worksuite.schedule.availability', $this->withLabels());
    }

    public function storeEvent(Request $request)
    {
        return redirect()->back()->with('success', 'Event created.');
    }

    public function updateEvent(Request $request, $event)
    {
        return redirect()->back()->with('success', 'Event updated.');
    }

    public function destroyEvent($event)
    {
        return redirect()->back()->with('success', 'Event deleted.');
    }
}
