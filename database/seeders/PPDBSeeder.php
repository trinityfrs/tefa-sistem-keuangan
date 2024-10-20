<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ppdb;

class PpdbSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Ppdb::create([
            'status' => 1,
            'merchant_order_id' => 'ORD123456789',
            'created_at' => now(),
            'updated_at' => now(),
        ]);//tes

    }
}
