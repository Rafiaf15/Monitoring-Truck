<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Truck;
use App\Models\Load;

class TruckSeeder extends Seeder
{
    public function run()
    {
        $companies = [
            'MTS'   => Company::firstOrCreate(['name' => 'PT MTS']),
            'TTS'   => Company::firstOrCreate(['name' => 'PT TTS']),
            'Badui' => Company::firstOrCreate(['name' => 'PT Badui']),
            'Tjp'   => Company::firstOrCreate(['name' => 'PT Tjp']),
        ];

        $data = [
            // MTS
            ['plate' => 'B 9285 BEL', 'driver' => 'Wardo Widodo',   'phone' => '+62 858-1705-6201', 'company' => 'MTS'],
            ['plate' => 'B 9290 BEL', 'driver' => 'Kasimun',        'phone' => '+62 858-6830-0240', 'company' => 'MTS'],
            ['plate' => 'B 9291 BEL', 'driver' => 'Sujatna',       'phone' => '+62 877-8875-2999', 'company' => 'MTS'],
            ['plate' => 'B 9389 BEL', 'driver' => 'Deni',          'phone' => '+62 858-8252-8201', 'company' => 'MTS'],
            ['plate' => 'B 9392 BEL', 'driver' => 'Nur Alpian',    'phone' => '+62 857-7295-4804', 'company' => 'MTS'],
            ['plate' => 'B 9397 BEL', 'driver' => 'Ombih',         'phone' => '+62 878-7353-9808', 'company' => 'MTS'],
            // TTS
            ['plate' => 'B 9601 UIZ', 'driver' => 'Ece Taryadi',   'phone' => '0838-7333-6394',    'company' => 'TTS'],
            ['plate' => 'B 9373 UWW', 'driver' => 'Wahyu Hari',   'phone' => '0819-2863-1723',    'company' => 'TTS'],
            ['plate' => 'B 9398 UWW', 'driver' => 'Ujang',        'phone' => '0813-8964-7933',    'company' => 'TTS'],
            ['plate' => 'B 9879 UWV', 'driver' => 'Roni',         'phone' => '0819-9355-0234',    'company' => 'TTS'],
            ['plate' => 'B 9345 UFY', 'driver' => 'Darwis',       'phone' => '0858-1074-5382',    'company' => 'TTS'],
            // Badui
            ['plate' => 'B 9540 UFZ', 'driver' => 'Adang',        'phone' => '+62 812-8132-3220','company' => 'Badui'],
            ['plate' => 'B 9137 UWV', 'driver' => 'Trimo',        'phone' => '+62 821-1312-3337','company' => 'Badui'],
            ['plate' => 'B 9890 UWV', 'driver' => 'Rohendi',      'phone' => '+62 856-9274-6398','company' => 'Badui'],
            ['plate' => 'B 9629 UFZ', 'driver' => 'Aris',         'phone' => '+62 896-7635-9875','company' => 'Badui'],
            ['plate' => 'B 9628 UFZ', 'driver' => 'Johar',        'phone' => '+62 856-9348-0944','company' => 'Badui'],
            // Tjp
            ['plate' => 'B 9526 JEH', 'driver' => 'Nirin',        'phone' => '+62 857-1626-9291','company' => 'Tjp'],
            ['plate' => 'B 9936 UEL', 'driver' => 'AMJAH',        'phone' => '+62 881-0231-45457','company' => 'Tjp'],
            ['plate' => 'B 9303 UEL', 'driver' => 'RUDI',         'phone' => '+62 812-1326-8892','company' => 'Tjp'],
            ['plate' => 'B 9327 UEI', 'driver' => 'SUHADA',       'phone' => '+62 858-8064-6613','company' => 'Tjp'],
            ['plate' => 'B 9640 UEJ', 'driver' => 'SUKMAWI',      'phone' => '+62 857-7664-3492','company' => 'Tjp'],
            ['plate' => 'B 9133 UEH', 'driver' => 'Hikmah',       'phone' => '+62 815-7225-0428','company' => 'Tjp'],
            ['plate' => 'B 9834 UEL', 'driver' => 'Miswan',       'phone' => '+62 877-7562-1785','company' => 'Tjp'],
            ['plate' => 'B 9828 UEL', 'driver' => 'Sungari',      'phone' => '+62 821-4630-5411','company' => 'Tjp'],
            ['plate' => 'B 9519 PEI', 'driver' => 'Erpan',        'phone' => '+62 838-3414-5481','company' => 'Tjp'],
            ['plate' => 'B 9250 UEJ', 'driver' => 'Kiswanto',     'phone' => '+62 878-4092-5447','company' => 'Tjp'],
        ];

        foreach ($data as $d) {
            $driver = Driver::firstOrCreate(
                ['name' => $d['driver']],
                ['phone' => $d['phone']]
            );

            $truck = Truck::create([
                'plate_number' => $d['plate'],
                'driver_id'    => $driver->id,
                'company_id'   => $companies[$d['company']]->id,
            ]);

            Load::create([
                'truck_id'   => $truck->id,
                'pipe_count' => 29,
                'status'     => 'muat',
            ]);
        }
    }
}
?>