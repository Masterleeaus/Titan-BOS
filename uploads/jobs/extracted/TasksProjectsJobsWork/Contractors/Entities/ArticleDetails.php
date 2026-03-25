<?php

namespace Modules\Contractors\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Contractors\Entities\Article;
use App\User;

class ArticleDetails extends Model
{
    protected $guarded = [];

    public function article()
    {
    	return $this->belongsTo(Article::class);
    }

    public function user()
    {
    	return $this->belongsTo(User::class);
    }
}
