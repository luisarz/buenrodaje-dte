<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RetaceoModel;
use Illuminate\Auth\Access\HandlesAuthorization;

class RetaceoModelPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RetaceoModel');
    }

    public function view(AuthUser $authUser, RetaceoModel $retaceoModel): bool
    {
        return $authUser->can('View:RetaceoModel');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RetaceoModel');
    }

    public function update(AuthUser $authUser, RetaceoModel $retaceoModel): bool
    {
        return $authUser->can('Update:RetaceoModel');
    }

    public function delete(AuthUser $authUser, RetaceoModel $retaceoModel): bool
    {
        return $authUser->can('Delete:RetaceoModel');
    }

    public function restore(AuthUser $authUser, RetaceoModel $retaceoModel): bool
    {
        return $authUser->can('Restore:RetaceoModel');
    }

    public function forceDelete(AuthUser $authUser, RetaceoModel $retaceoModel): bool
    {
        return $authUser->can('ForceDelete:RetaceoModel');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RetaceoModel');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RetaceoModel');
    }

    public function replicate(AuthUser $authUser, RetaceoModel $retaceoModel): bool
    {
        return $authUser->can('Replicate:RetaceoModel');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RetaceoModel');
    }

}