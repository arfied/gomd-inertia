<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait BusinessUserRelationship
{
    /**
     * Get the business admin user for the current business
     * 
     * @return User|null
     */
    protected function getBusinessAdmin(): ?User
    {
        $user = Auth::user();
        
        // If user is business_admin, return self
        if ($user->hasRole('business_admin')) {
            return $user;
        }
        
        // If user is business_hr, find the business_admin for the same business
        if ($user->hasRole('business_hr') && $user->business_id) {
            return User::role('business_admin')
                ->where('business_id', $user->business_id)
                ->first();
        }
        
        return null;
    }
    
    /**
     * Check if the current user can manage the given user's credit cards
     * 
     * @param User $targetUser
     * @return bool
     */
    protected function canManageCreditCards(User $targetUser): bool
    {
        $user = Auth::user();
        
        // User can always manage their own credit cards
        if ($user->id === $targetUser->id) {
            return true;
        }
        
        // Business HR can manage business admin's credit cards
        if ($user->hasRole('business_hr') && 
            $targetUser->hasRole('business_admin') && 
            $user->business_id === $targetUser->business_id) {
            return true;
        }
        
        // Admin can manage any user's credit cards
        if ($user->hasRole('admin')) {
            return true;
        }
        
        return false;
    }
}
