<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Truck extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate_number',
        'driver_id',
        'company_id',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function currentLoad()
    {
        return $this->hasOne(Load::class);
    }

    public function logs()
    {
        return $this->hasMany(LoadLog::class)->latest();
    }
}
?>