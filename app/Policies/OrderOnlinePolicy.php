<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OrderOnline;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderOnlinePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OrderOnline');
    }

    public function view(AuthUser $authUser, OrderOnline $orderOnline): bool
    {
        return $authUser->can('View:OrderOnline');
    }

    public function create(AuthUser $authUser): bool
    {
        // return $authUser->can('Create:OrderOnline');
        return false ;
    }

    public function update(AuthUser $authUser, OrderOnline $orderOnline): bool
    {
        return $authUser->can('Update:OrderOnline');
    }

    public function delete(AuthUser $authUser, OrderOnline $orderOnline): bool
    {
        return $authUser->can('Delete:OrderOnline');
    }

    public function restore(AuthUser $authUser, OrderOnline $orderOnline): bool
    {
        return $authUser->can('Restore:OrderOnline');
    }

    public function forceDelete(AuthUser $authUser, OrderOnline $orderOnline): bool
    {
        return $authUser->can('ForceDelete:OrderOnline');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OrderOnline');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OrderOnline');
    }

    public function replicate(AuthUser $authUser, OrderOnline $orderOnline): bool
    {
        return $authUser->can('Replicate:OrderOnline');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OrderOnline');
    }

}
