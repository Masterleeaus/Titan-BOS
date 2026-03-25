<?php

namespace App\Observers\WorkSuite\Crm;

use App\Models\ClientCategory;

class ClientCategoryObserver
{

    public function creating(ClientCategory $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
