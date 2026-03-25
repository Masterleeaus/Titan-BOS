<?php

namespace Modules\Contractors\Policies;

use App\Models\User;
use Modules\Contractors\Entities\WorkOrderContractorAssignment;

class AssignmentPolicy
{
    public function viewAny(User $user): bool { return $user->can('contractors.view_assignments'); }
    public function view(User $user, WorkOrderContractorAssignment $m): bool { return $user->can('contractors.view_assignments'); }
    public function create(User $user): bool { return $user->can('contractors.assign'); }
    public function update(User $user, WorkOrderContractorAssignment $m): bool { return $user->can('contractors.assign'); }
    public function delete(User $user, WorkOrderContractorAssignment $m): bool { return $user->can('contractors.assign'); }
}
