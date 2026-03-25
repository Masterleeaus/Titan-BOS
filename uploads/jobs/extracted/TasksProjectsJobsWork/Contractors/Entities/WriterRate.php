<?php

namespace Modules\Contractors\Entities;

use Illuminate\Database\Eloquent\Model;

class WriterRate extends Model
{
    protected $fillable = ['user_id', 'rate'];
}
