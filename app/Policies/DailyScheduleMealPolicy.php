<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DailyScheduleMeal;
use Illuminate\Auth\Access\HandlesAuthorization;

class DailyScheduleMealPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DailyScheduleMeal');
    }

    public function view(AuthUser $authUser, DailyScheduleMeal $dailyScheduleMeal): bool
    {
        return $authUser->can('View:DailyScheduleMeal');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DailyScheduleMeal');
    }

    public function update(AuthUser $authUser, DailyScheduleMeal $dailyScheduleMeal): bool
    {
        return $authUser->can('Update:DailyScheduleMeal');
    }

    public function delete(AuthUser $authUser, DailyScheduleMeal $dailyScheduleMeal): bool
    {
        return $authUser->can('Delete:DailyScheduleMeal');
    }

    public function restore(AuthUser $authUser, DailyScheduleMeal $dailyScheduleMeal): bool
    {
        return $authUser->can('Restore:DailyScheduleMeal');
    }

    public function forceDelete(AuthUser $authUser, DailyScheduleMeal $dailyScheduleMeal): bool
    {
        return $authUser->can('ForceDelete:DailyScheduleMeal');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DailyScheduleMeal');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DailyScheduleMeal');
    }

    public function replicate(AuthUser $authUser, DailyScheduleMeal $dailyScheduleMeal): bool
    {
        return $authUser->can('Replicate:DailyScheduleMeal');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DailyScheduleMeal');
    }

}