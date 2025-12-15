<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\KitchenPayment;
use Illuminate\Auth\Access\HandlesAuthorization;

class KitchenPaymentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KitchenPayment');
    }

    public function view(AuthUser $authUser, KitchenPayment $kitchenPayment): bool
    {
        return $authUser->can('View:KitchenPayment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KitchenPayment');
    }

    public function update(AuthUser $authUser, KitchenPayment $kitchenPayment): bool
    {
        return $authUser->can('Update:KitchenPayment');
    }

    public function delete(AuthUser $authUser, KitchenPayment $kitchenPayment): bool
    {
        return $authUser->can('Delete:KitchenPayment');
    }

    public function restore(AuthUser $authUser, KitchenPayment $kitchenPayment): bool
    {
        return $authUser->can('Restore:KitchenPayment');
    }

    public function forceDelete(AuthUser $authUser, KitchenPayment $kitchenPayment): bool
    {
        return $authUser->can('ForceDelete:KitchenPayment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KitchenPayment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KitchenPayment');
    }

    public function replicate(AuthUser $authUser, KitchenPayment $kitchenPayment): bool
    {
        return $authUser->can('Replicate:KitchenPayment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KitchenPayment');
    }

}