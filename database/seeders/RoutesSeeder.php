<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoutesSeeder extends Seeder
{
    public function run()
    {
        DB::table('routes')->insert([
            [
                'path' => '/dashboard',
                'component_name' => 'Dashboard',
                'requires_auth' => true,
                'required_permission' => 'view dashboard',
            ],
            [
                'path' => '/manage-users',
                'component_name' => 'ManageUsers',
                'requires_auth' => true,
                'required_permission' => 'manage users',
            ],
        ]);
    }
}
