<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Meal;
use Illuminate\Auth\Access\HandlesAuthorization;

class MealPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Meal');
    }

    public function view(AuthUser $authUser, Meal $meal): bool
    {
        return $authUser->can('View:Meal');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Meal');
    }

    public function update(AuthUser $authUser, Meal $meal): bool
    {
        return $authUser->can('Update:Meal');
    }

    public function delete(AuthUser $authUser, Meal $meal): bool
    {
        return $authUser->can('Delete:Meal');
    }

    public function restore(AuthUser $authUser, Meal $meal): bool
    {
        return $authUser->can('Restore:Meal');
    }

    public function forceDelete(AuthUser $authUser, Meal $meal): bool
    {
        return $authUser->can('ForceDelete:Meal');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Meal');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Meal');
    }

    public function replicate(AuthUser $authUser, Meal $meal): bool
    {
        return $authUser->can('Replicate:Meal');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Meal');
    }

}