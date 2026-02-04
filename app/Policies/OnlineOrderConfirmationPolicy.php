<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OnlineOrderConfirmation;
use Illuminate\Auth\Access\HandlesAuthorization;

class OnlineOrderConfirmationPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OnlineOrderConfirmation');
    }

    public function view(AuthUser $authUser, OnlineOrderConfirmation $onlineOrderConfirmation): bool
    {
        return $authUser->can('View:OnlineOrderConfirmation');
    }

    public function create(AuthUser $authUser): bool
    {
        // return $authUser->can('Create:OnlineOrderConfirmation');
        return false ;
    }

    public function update(AuthUser $authUser, OnlineOrderConfirmation $onlineOrderConfirmation): bool
    {
        return $authUser->can('Update:OnlineOrderConfirmation');
    }

    public function delete(AuthUser $authUser, OnlineOrderConfirmation $onlineOrderConfirmation): bool
    {
        return $authUser->can('Delete:OnlineOrderConfirmation');
    }

    public function restore(AuthUser $authUser, OnlineOrderConfirmation $onlineOrderConfirmation): bool
    {
        return $authUser->can('Restore:OnlineOrderConfirmation');
    }

    public function forceDelete(AuthUser $authUser, OnlineOrderConfirmation $onlineOrderConfirmation): bool
    {
        return $authUser->can('ForceDelete:OnlineOrderConfirmation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OnlineOrderConfirmation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OnlineOrderConfirmation');
    }

    public function replicate(AuthUser $authUser, OnlineOrderConfirmation $onlineOrderConfirmation): bool
    {
        return $authUser->can('Replicate:OnlineOrderConfirmation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OnlineOrderConfirmation');
    }

}
