<?php

namespace App\Http\Controllers\WorkSuite\TeamChat;

use App\Http\Controllers\WorkSuite\WorkSuiteBaseController;
use Illuminate\Http\Request;

class TeamChatController extends WorkSuiteBaseController
{
    public function index() { return view('worksuite.team-chat.index', $this->withLabels()); }
    public function messages($channel) { return view('worksuite.team-chat.messages', $this->withLabels(['channel' => $channel])); }
    public function store(Request $request) { return redirect()->back(); }
}
