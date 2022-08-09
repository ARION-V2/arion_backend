<?php

namespace Database\Seeders;

use App\Models\Gudang;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        User::create([
            'name'=> 'Triana Dyah Pangestuti',
            'password'=> Hash::make('12345678'),
            'username'=> 'triana',
            'role'=> 'admin',
            'no_hp'=> '08971613199',
            'date_of_birth'=> '2000-05-01',
        ]);

        $this->call([
            GudangSeeder::class,
        ]);
    }
}
