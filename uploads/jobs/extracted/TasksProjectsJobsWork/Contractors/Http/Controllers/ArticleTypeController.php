<?php

namespace Modules\Contractors\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Controllers\Member\MemberBaseController;
use Modules\Contractors\Entities\Article;
use Modules\Contractors\Entities\ArticleType;
use Modules\Contractors\Entities\Writer;
use Modules\Contractors\Entities\ArticleFile;
use Modules\Contractors\Entities\ArticleComment;
use Modules\Contractors\Entities\ArticleSetting;
use Modules\Contractors\Notifications\NewArticle;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Response;
use App\Helper\Reply;
use Illuminate\Http\File;
use App\User;
use App\Setting;
use App\RoleUser;
use App\Role;
use App\Task;
use Pusher\Pusher;

class ArticleTypeController extends MemberBaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
        $this->pageTitle = __('article::app.menu.article');
        $this->pageIcon = 'icon-pencil';
        $this->user = auth()->user();
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $this->types = ArticleType::paginate(10);
        return view('article::type.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('article::type', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $type = ArticleType::create([
            'name' => $request->name,
            'description' => $request->description
        ]);
        $type->save();
        return Reply::success('Type created!');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('article::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('article::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy(ArticleType $type)
    {
        $type->delete();
        return Reply::success('Type deleted!');
    }
}
