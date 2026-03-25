<?php

namespace Modules\Contractors\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contractor extends Model
{
    use SoftDeletes;
{
    protected $table = 'contractors';

    protected $fillable = [
        'company_id','name','email','phone','status'
    ];
}
