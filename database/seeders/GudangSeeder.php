<?php

namespace Database\Seeders;

use App\Models\Gudang;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GudangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Gudang::create([
            'nama_gudang'=>'Ini Gudang',
            'latitude'=>'-6.408310',
            'longitude'=>'108.281795',
        ]);

        
    }
}
