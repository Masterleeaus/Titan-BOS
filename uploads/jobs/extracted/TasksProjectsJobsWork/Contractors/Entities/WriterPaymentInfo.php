<?php

namespace Modules\Contractors\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Contractors\Entities\Writer;

class WriterPaymentInfo extends Model
{
	protected $table = 'writer_payment_infos';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(Writer::class, 'user_id');
    }
}
