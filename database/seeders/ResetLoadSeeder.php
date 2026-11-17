<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Load;

class ResetLoadSeeder extends Seeder
{
    public function run(): void
    {
        Load::query()->update([
            'pipe_count' => 0,
            'status'     => 'bpi',
        ]);
    }
}
