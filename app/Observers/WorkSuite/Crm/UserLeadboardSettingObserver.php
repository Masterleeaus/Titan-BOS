<?php

namespace App\Observers\WorkSuite\Crm;

use App\Models\UserLeadboardSetting;

class UserLeadboardSettingObserver
{

    public function creating(UserLeadboardSetting $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
