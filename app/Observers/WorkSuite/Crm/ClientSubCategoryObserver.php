<?php

namespace App\Observers\WorkSuite\Crm;

use App\Models\ClientSubCategory;

class ClientSubCategoryObserver
{

    public function creating(ClientSubCategory $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
