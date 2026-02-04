<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MealMedia;
use Illuminate\Auth\Access\HandlesAuthorization;

class MealMediaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MealMedia');
    }

    public function view(AuthUser $authUser, MealMedia $mealMedia): bool
    {
        return $authUser->can('View:MealMedia');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MealMedia');
    }

    public function update(AuthUser $authUser, MealMedia $mealMedia): bool
    {
        return $authUser->can('Update:MealMedia');
    }

    public function delete(AuthUser $authUser, MealMedia $mealMedia): bool
    {
        return $authUser->can('Delete:MealMedia');
    }

    public function restore(AuthUser $authUser, MealMedia $mealMedia): bool
    {
        return $authUser->can('Restore:MealMedia');
    }

    public function forceDelete(AuthUser $authUser, MealMedia $mealMedia): bool
    {
        return $authUser->can('ForceDelete:MealMedia');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MealMedia');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MealMedia');
    }

    public function replicate(AuthUser $authUser, MealMedia $mealMedia): bool
    {
        return $authUser->can('Replicate:MealMedia');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MealMedia');
    }

}