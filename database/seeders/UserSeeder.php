<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company=Company::query()->create([
            'name'=>'Start Coding',
            'is_active' => true
        ]);
        $user=User::query()->create([
            'name'=>'Start Coding',
            'email' => 'zmraupov@gmail.com',
            'role'=>'company',
            'password'=>Hash::make('zmraupov@gmail.com'),
            'company_id' =>$company->id
        ]);
    }
}
