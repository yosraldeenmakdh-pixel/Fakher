<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Kitchen;
use Illuminate\Auth\Access\HandlesAuthorization;

class KitchenPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Kitchen');
    }

    public function view(AuthUser $authUser, Kitchen $kitchen): bool
    {
        return $authUser->can('View:Kitchen');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Kitchen');
    }

    public function update(AuthUser $authUser, Kitchen $kitchen): bool
    {
        return $authUser->can('Update:Kitchen');
    }

    public function delete(AuthUser $authUser, Kitchen $kitchen): bool
    {
        return $authUser->can('Delete:Kitchen');
    }

    public function restore(AuthUser $authUser, Kitchen $kitchen): bool
    {
        return $authUser->can('Restore:Kitchen');
    }

    public function forceDelete(AuthUser $authUser, Kitchen $kitchen): bool
    {
        return $authUser->can('ForceDelete:Kitchen');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Kitchen');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Kitchen');
    }

    public function replicate(AuthUser $authUser, Kitchen $kitchen): bool
    {
        return $authUser->can('Replicate:Kitchen');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Kitchen');
    }

}