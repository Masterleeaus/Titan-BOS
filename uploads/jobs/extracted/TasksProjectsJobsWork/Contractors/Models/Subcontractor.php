<?php

namespace Modules\Contractors\Models;

use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    protected $table = 'contractors';

    protected $fillable = [
        'name', 'email', 'phone', 'company', 'abn', 'rate_type', 'rate_amount',
        'status', 'notes'
    ];
}
