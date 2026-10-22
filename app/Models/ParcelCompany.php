<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParcelCompany extends Model
{
    protected $table = 'parcelcompany';
    protected $primaryKey = 'parcelcom_id';
    public $timestamps = false;
    
    protected $fillable = [
        'companyname',
        'trackingurl',
        'apiendpoint',
        'apikey',
        'supportcontact',
        'deliverytimeestimate',
        'inactive',
    ];
    
    protected $casts = [
        'inactive' => 'boolean',
    ];
    
    public function parcels()
    {
        return $this->hasMany(Parcel::class, 'parcelcom_id');
    }
    
    /**
     * Scope to get only active companies
     */
    public function scopeActive($query)
    {
        return $query->where('inactive', false);
    }
    
    /**
     * Generate tracking URL for a specific tracking number
     */
    public function generateTrackingUrl($trackingNumber)
    {
        if (!$this->trackingurl) {
            return null;
        }
        
        return str_replace('{tracking_number}', $trackingNumber, $this->trackingurl);
    }
}
