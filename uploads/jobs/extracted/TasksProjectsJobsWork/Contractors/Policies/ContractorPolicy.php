<?php

namespace Modules\Contractors\Policies;

use Modules\Contractors\Entities\Contractor;

class ContractorPolicy
{
    public function viewAny($user, \Modules\Contractors\Entities\Contractor $model = null): bool {
        $slug = config('contractor.permissions.abilities.viewAny', 'contractor.manage');
        return method_exists($user, 'can') ? $user->can($slug) : true;
    }

    public function view($user, \Modules\Contractors\Entities\Contractor $model = null): bool {
        $slug = config('contractor.permissions.abilities.view', 'contractor.manage');
        return method_exists($user, 'can') ? $user->can($slug) : true;
    }

    public function create($user, \Modules\Contractors\Entities\Contractor $model = null): bool {
        $slug = config('contractor.permissions.abilities.create', 'contractor.manage');
        return method_exists($user, 'can') ? $user->can($slug) : true;
    }

    public function update($user, \Modules\Contractors\Entities\Contractor $model = null): bool {
        $slug = config('contractor.permissions.abilities.update', 'contractor.manage');
        return method_exists($user, 'can') ? $user->can($slug) : true;
    }

    public function delete($user, \Modules\Contractors\Entities\Contractor $model = null): bool {
        $slug = config('contractor.permissions.abilities.delete', 'contractor.manage');
        return method_exists($user, 'can') ? $user->can($slug) : true;
    }

}
