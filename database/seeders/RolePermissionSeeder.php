<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

    // 2. إنشاء الصلاحيات (استخدام updateOrCreate يمنع تكرار الخطأ إذا أعدتِ التشغيل)
    $p1 = Permission::updateOrCreate(['name' => 'view_dashboard', 'guard_name' => 'web']);
    $p2 = Permission::updateOrCreate(['name' => 'manage_sales', 'guard_name' => 'web']);
    $p3 = Permission::updateOrCreate(['name' => 'manage_products', 'guard_name' => 'web']);
    $p4 = Permission::updateOrCreate(['name' => 'view_reports', 'guard_name' => 'web']);

    // 3. إنشاء الأدوار
    $roleCashier = Role::updateOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
    $roleAdmin = Role::updateOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    // 4. ربط الصلاحيات بالأدوار (استخدام syncPermissions يحل مشكلة الـ DoesNotExist)
    $roleCashier->syncPermissions([$p1, $p2]);
    $roleAdmin->syncPermissions([$p1, $p2, $p3, $p4]);
    }
    }

