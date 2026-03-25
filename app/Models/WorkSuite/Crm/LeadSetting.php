<?php

namespace App\Models\WorkSuite\Crm;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadSetting extends BaseModel
{

    use HasFactory, HasCompany;

    protected $table = 'lead_setting';

}
