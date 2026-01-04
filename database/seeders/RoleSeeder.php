<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'teacher',
                'display_name' => 'Teacher',
                'description' => 'Can create exams, upload PDFs, grade results',
            ],
            [
                'name' => 'student',
                'display_name' => 'Student',
                'description' => 'Can take exams and view results',
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'System administrator with full access',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
