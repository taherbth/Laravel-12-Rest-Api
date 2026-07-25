<?php

namespace Database\Seeders;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Gender;

class InitialSetupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         /**
         * Gender List
         */
        $gender_list = ['Male', 'Female','Other'];
        foreach( $gender_list as $key => $value ){
            $gender_listObj = new Gender();
            $gender_listObj->name = $value;
            $gender_listObj->save();
        }
    }
}
