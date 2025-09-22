<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Payments;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentsPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Payments');
    }

    public function view(AuthUser $authUser, Payments $payments): bool
    {
        return $authUser->can('View:Payments');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Payments');
    }

    public function update(AuthUser $authUser, Payments $payments): bool
    {
        return $authUser->can('Update:Payments');
    }

    public function delete(AuthUser $authUser, Payments $payments): bool
    {
        return $authUser->can('Delete:Payments');
    }

    public function restore(AuthUser $authUser, Payments $payments): bool
    {
        return $authUser->can('Restore:Payments');
    }

    public function forceDelete(AuthUser $authUser, Payments $payments): bool
    {
        return $authUser->can('ForceDelete:Payments');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Payments');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Payments');
    }

    public function replicate(AuthUser $authUser, Payments $payments): bool
    {
        return $authUser->can('Replicate:Payments');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Payments');
    }

}