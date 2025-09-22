<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\adjustmentInventory;
use Illuminate\Auth\Access\HandlesAuthorization;

class adjustmentInventoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AdjustmentInventory');
    }

    public function view(AuthUser $authUser, adjustmentInventory $adjustmentInventory): bool
    {
        return $authUser->can('View:AdjustmentInventory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AdjustmentInventory');
    }

    public function update(AuthUser $authUser, adjustmentInventory $adjustmentInventory): bool
    {
        return $authUser->can('Update:AdjustmentInventory');
    }

    public function delete(AuthUser $authUser, adjustmentInventory $adjustmentInventory): bool
    {
        return $authUser->can('Delete:AdjustmentInventory');
    }

    public function restore(AuthUser $authUser, adjustmentInventory $adjustmentInventory): bool
    {
        return $authUser->can('Restore:AdjustmentInventory');
    }

    public function forceDelete(AuthUser $authUser, adjustmentInventory $adjustmentInventory): bool
    {
        return $authUser->can('ForceDelete:AdjustmentInventory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AdjustmentInventory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AdjustmentInventory');
    }

    public function replicate(AuthUser $authUser, adjustmentInventory $adjustmentInventory): bool
    {
        return $authUser->can('Replicate:AdjustmentInventory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AdjustmentInventory');
    }

}