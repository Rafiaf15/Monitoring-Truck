<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Load extends Model
{
    use HasFactory;

    protected $fillable = [
        'truck_id',
        'pipe_count',
        'trip_count',
        'status',
    ];

    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }
}
