<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class DataMaskingHelper
{
    /**
     * Mask email address for security
     * Shows first 2 characters and domain, masks the rest
     * Example: john@example.com -> jo***@example.com
     * 
     * @param string|null $email
     * @param bool $showFull If true, returns full email (for admin only)
     * @return string
     */
    public static function maskEmail(?string $email, bool $showFull = false): string
    {
        if (empty($email)) {
            return 'N/A';
        }

        // If showFull is true and user is admin, return full email
        if ($showFull && self::isAdmin()) {
            return $email;
        }

        // Parse email
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $localPart = $parts[0];
        $domain = $parts[1];

        // Show first 2 characters of local part, mask the rest
        if (strlen($localPart) <= 2) {
            $maskedLocal = str_repeat('*', strlen($localPart));
        } else {
            $maskedLocal = substr($localPart, 0, 2) . str_repeat('*', strlen($localPart) - 2);
        }

        return $maskedLocal . '@' . $domain;
    }

    /**
     * Mask phone number for security
     * Shows last 4 digits only
     * Example: 9876543210 -> XXXXX3210
     * 
     * @param string|null $phone
     * @param bool $showFull If true, returns full phone (for admin only)
     * @return string
     */
    public static function maskPhone(?string $phone, bool $showFull = false): string
    {
        if (empty($phone)) {
            return 'N/A';
        }

        // Remove any non-digit characters for processing
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // If showFull is true and user is admin, return original phone
        if ($showFull && self::isAdmin()) {
            return $phone;
        }

        // If phone is too short, return as is
        if (strlen($cleanPhone) <= 4) {
            return $phone;
        }

        // Show last 4 digits only
        $lastFour = substr($cleanPhone, -4);
        $maskedLength = strlen($cleanPhone) - 4;
        $masked = str_repeat('X', $maskedLength) . $lastFour;

        return $masked;
    }

    /**
     * Check if current user is admin
     * 
     * @return bool
     */
    private static function isAdmin(): bool
    {
        $user = Auth::user();
        return $user && ($user->hasRole('Admin') || (!$user->hasRole('School') && !$user->hasRole('Initiator') && !$user->hasRole('Student')));
    }

    /**
     * Check if current user can view full data (admin only)
     * 
     * @return bool
     */
    public static function canViewFullData(): bool
    {
        return self::isAdmin();
    }
}
