<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\InstitutionalMealPrice;
use Illuminate\Auth\Access\HandlesAuthorization;

class InstitutionalMealPricePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InstitutionalMealPrice');
    }

    public function view(AuthUser $authUser, InstitutionalMealPrice $institutionalMealPrice): bool
    {
        return $authUser->can('View:InstitutionalMealPrice');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InstitutionalMealPrice');
    }

    public function update(AuthUser $authUser, InstitutionalMealPrice $institutionalMealPrice): bool
    {
        return $authUser->can('Update:InstitutionalMealPrice');
    }

    public function delete(AuthUser $authUser, InstitutionalMealPrice $institutionalMealPrice): bool
    {
        return $authUser->can('Delete:InstitutionalMealPrice');
    }

    public function restore(AuthUser $authUser, InstitutionalMealPrice $institutionalMealPrice): bool
    {
        return $authUser->can('Restore:InstitutionalMealPrice');
    }

    public function forceDelete(AuthUser $authUser, InstitutionalMealPrice $institutionalMealPrice): bool
    {
        return $authUser->can('ForceDelete:InstitutionalMealPrice');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InstitutionalMealPrice');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InstitutionalMealPrice');
    }

    public function replicate(AuthUser $authUser, InstitutionalMealPrice $institutionalMealPrice): bool
    {
        return $authUser->can('Replicate:InstitutionalMealPrice');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InstitutionalMealPrice');
    }

}