<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // هنا نقوم باستدعاء ملفات الـ Seeders الأخرى بالترتيب الصحيح لكي تعمل تلقائياً
        $this->call([
            RolePermissionSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);
    }
}