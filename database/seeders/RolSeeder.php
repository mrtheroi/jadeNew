<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role1 = Role::create(['name' => 'Super']);
        $role2 = Role::create(['name' => 'Admin']);
        $role3 = Role::create(['name' => 'User']);

        Permission::create(['name' => 'super', 'description' => 'Para entrar a todo el sitio'])->syncRoles([$role1,]);
        Permission::create(['name' => 'admin', 'description' => 'Para administrar'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'user', 'description' => 'Para usuario normal'])->syncRoles([$role1, $role2, $role3]);
    }
}
