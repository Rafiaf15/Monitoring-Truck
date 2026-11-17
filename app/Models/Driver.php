<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
    ];

    public function trucks()
    {
        return $this->hasMany(Truck::class);
    }

    /**
     * Nomor WA yang sudah dinormalisasi ke format 628xxx (tanpa +/spasi/strip)
     */
    public function getWhatsappNumberAttribute(): string
    {
        $digits = preg_replace('/\D/', '', $this->phone);

        // Jika diawali 0, ganti dengan 62
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }

        return $digits;
    }
}
?>