<?php

namespace Modules\Contractors\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Contractors\Entities\Invoice;

class InvoiceFile extends Model
{
    protected $fillable = ['invoice_id', 'file'];

    public function invoice()
    {
    	return belongsTo(Invoice::class);
    }
}
