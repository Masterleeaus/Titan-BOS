<?php

namespace App\Http\Controllers\WorkSuite\Insights;

use App\Http\Controllers\WorkSuite\WorkSuiteBaseController;

class InsightController extends WorkSuiteBaseController
{
    public function index() { return view('worksuite.insights.index', $this->withLabels()); }
    public function jobs() { return view('worksuite.insights.jobs', $this->withLabels()); }
    public function finance() { return view('worksuite.insights.finance', $this->withLabels()); }
    public function team() { return view('worksuite.insights.team', $this->withLabels()); }
    public function customers() { return view('worksuite.insights.customers', $this->withLabels()); }
}
