<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMode extends Model
{
    protected $table = 'paymentmode';
    protected $primaryKey = 'paymode_id';
    public $timestamps = false;
    
    protected $fillable = ['paymodename'];
    
    public function getRouteKeyName()
    {
        return 'paymode_id';
    }
    
    public function paymentLogs()
    {
        return $this->hasMany(PaymentLog::class, 'paymode_id');
    }
    
    public function uploadBatches()
    {
        return $this->hasMany(UploadBatch::class, 'payment_mode_id');
    }
}
