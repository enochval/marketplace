<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $super_admin_user = User::create([
            'email' => 'super_admin@mastermindtech.ng',
            'phone' => '08063800482',
            'password' => bcrypt('P@ssword@01'),
            'is_premium' => true,
            'is_active' => true,
            'is_confirmed' => true,
        ]);
        $super_admin_user->profile()->create([
            'first_name' => 'Super',
            'last_name' => 'Admin'
        ]);

        $admin_user = User::create([
            'email' => 'admin@timabala.com',
            'phone' => '00000000001',
            'password' => bcrypt('admin'),
            'is_premium' => true,
            'is_active' => true,
            'is_confirmed' => true,
        ]);
        $admin_user->profile()->create([
            'first_name' => 'Admin',
            'last_name' => 'Timbala'
        ]);

        $agent_user = User::create([
            'email' => 'agent@timbala.com',
            'phone' => '00000000002',
            'password' => bcrypt('agent'),
            'is_premium' => true,
            'is_active' => true,
            'is_confirmed' => true,
        ]);
        $agent_user->profile()->create([
            'first_name' => 'Agent',
            'last_name' => 'Timbala'
        ]);

        $employer_user = User::create([
            'email' => 'employer@timbala.com',
            'phone' => '00000000003',
            'password' => bcrypt('employer'),
            'is_premium' => true,
            'is_active' => true,
            'is_confirmed' => true,
        ]);
        $employer_user->profile()->create([
            'first_name' => 'Employer',
            'last_name' => 'Timbala'
        ]);

        $worker_user = User::create([
            'email' => 'worker@timbala.com',
            'phone' => '00000000004',
            'password' => bcrypt('worker'),
            'is_premium' => true,
            'is_active' => true,
            'is_confirmed' => true,
        ]);
        $worker_user->profile()->create([
            'first_name' => 'Worker',
            'last_name' => 'Timbala'
        ]);

        $super_admin_role = Role::where('name', 'super_admin')->first();
        $admin_role = Role::where('name', 'admin')->first();
        $agent_role = Role::where('name', 'agent')->first();
        $employer_role = Role::where('name', 'employer')->first();
        $worker_role = Role::where('name', 'worker')->first();

        $super_admin_user->attachRole($super_admin_role);
        $admin_user->attachRole($admin_role);
        $agent_user->attachRole($agent_role);
        $employer_user->attachRole($employer_role);
        $worker_user->attachRole($worker_role);
    }
}
