<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parcel extends Model
{
    protected $table = 'parcel';
    protected $primaryKey = 'parcel_id';
    public $timestamps = false;
    
    protected $fillable = [
        'student_id',
        'parcelcom_id',
        'trakingnumber',
        'notes',
    ];
    
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
    
    public function parcelCompany()
    {
        return $this->belongsTo(ParcelCompany::class, 'parcelcom_id');
    }
}
