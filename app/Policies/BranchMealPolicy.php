<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BranchMeal;
use Illuminate\Auth\Access\HandlesAuthorization;

class BranchMealPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BranchMeal');
    }

    public function view(AuthUser $authUser, BranchMeal $branchMeal): bool
    {
        return $authUser->can('View:BranchMeal');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BranchMeal');
    }

    public function update(AuthUser $authUser, BranchMeal $branchMeal): bool
    {
        return $authUser->can('Update:BranchMeal');
    }

    public function delete(AuthUser $authUser, BranchMeal $branchMeal): bool
    {
        return $authUser->can('Delete:BranchMeal');
    }

    public function restore(AuthUser $authUser, BranchMeal $branchMeal): bool
    {
        return $authUser->can('Restore:BranchMeal');
    }

    public function forceDelete(AuthUser $authUser, BranchMeal $branchMeal): bool
    {
        return $authUser->can('ForceDelete:BranchMeal');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BranchMeal');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BranchMeal');
    }

    public function replicate(AuthUser $authUser, BranchMeal $branchMeal): bool
    {
        return $authUser->can('Replicate:BranchMeal');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BranchMeal');
    }

}