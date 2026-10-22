<?php

namespace App\Helpers;

class DateHelper
{
    /**
     * Convert DD/MM/YYYY to YYYY-MM-DD format
     * 
     * @param string|null $dateString
     * @return string|null
     */
    public static function convertDateFormat($dateString)
    {
        if (!$dateString) return null;
        
        // If already in YYYY-MM-DD format, return as is
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            return $dateString;
        }
        
        // Convert DD/MM/YYYY to YYYY-MM-DD
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateString)) {
            $parts = explode('/', $dateString);
            return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        }
        
        return $dateString;
    }
}
