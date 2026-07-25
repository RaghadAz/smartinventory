<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    
        public function run(): void
        {
    
            // إنشاء الصلاحيات
            $permissions = [
                // المبيعات
                'view sales',
                'create sales',
                'edit sales',
                'delete sales',
                'print sales',
                // المنتجات
                'view products',
                'create products',
                'edit products',
                'delete products', 
                // التقارير
                'view reports',
                'view profits', 
                // الإعدادات
                'manage users',
                'manage settings',
            ];
    
            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => $permission]);
            }
    
            // إنشاء الأدوار
            $admin = Role::firstOrCreate(['name' => 'admin']);
            $cashier = Role::firstOrCreate(['name' => 'cashier']);
    
            // Admin: كل الصلاحيات
            $admin->syncPermissions(Permission::all());
    
            // Cashier: صلاحيات محدودة
            $cashier->syncPermissions([
                'view sales',
                'create sales',
                'edit sales',
                'print sales',
                'view products',
            ]);
        }
    }

