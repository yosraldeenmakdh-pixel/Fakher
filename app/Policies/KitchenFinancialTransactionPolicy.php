<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\KitchenFinancialTransaction;
use Illuminate\Auth\Access\HandlesAuthorization;

class KitchenFinancialTransactionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KitchenFinancialTransaction');
    }

    public function view(AuthUser $authUser, KitchenFinancialTransaction $kitchenFinancialTransaction): bool
    {
        return $authUser->can('View:KitchenFinancialTransaction');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KitchenFinancialTransaction');
    }

    public function update(AuthUser $authUser, KitchenFinancialTransaction $kitchenFinancialTransaction): bool
    {
        return $authUser->can('Update:KitchenFinancialTransaction');
    }

    public function delete(AuthUser $authUser, KitchenFinancialTransaction $kitchenFinancialTransaction): bool
    {
        return $authUser->can('Delete:KitchenFinancialTransaction');
    }

    public function restore(AuthUser $authUser, KitchenFinancialTransaction $kitchenFinancialTransaction): bool
    {
        return $authUser->can('Restore:KitchenFinancialTransaction');
    }

    public function forceDelete(AuthUser $authUser, KitchenFinancialTransaction $kitchenFinancialTransaction): bool
    {
        return $authUser->can('ForceDelete:KitchenFinancialTransaction');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KitchenFinancialTransaction');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KitchenFinancialTransaction');
    }

    public function replicate(AuthUser $authUser, KitchenFinancialTransaction $kitchenFinancialTransaction): bool
    {
        return $authUser->can('Replicate:KitchenFinancialTransaction');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KitchenFinancialTransaction');
    }

}