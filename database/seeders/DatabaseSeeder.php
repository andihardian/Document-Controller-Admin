<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DocumentCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 1. Roles ─────────────────────────────────────
        $roles = ['admin', 'department_head', 'employee', 'viewer'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // ─── 2. Permissions ───────────────────────────────
        $permissions = [
            // User management
            'view users', 'create users', 'edit users', 'delete users',

            // Department management
            'view departments', 'create departments', 'edit departments', 'delete departments',

            // Document management
            'view documents', 'create documents', 'edit documents', 'delete documents',
            'upload documents', 'download documents', 'restore documents',

            // Approval
            'approve documents', 'reject documents',

            // Audit log
            'view audit logs',

            // Settings
            'manage settings', 'backup restore',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ─── 3. Assign permissions ke role ────────────────

        Role::findByName('admin')->givePermissionTo(Permission::all());

        Role::findByName('department_head')->givePermissionTo([
            'view documents', 'download documents',
            'approve documents', 'reject documents',
            'view audit logs',
        ]);

        Role::findByName('employee')->givePermissionTo([
            'view documents', 'create documents', 'edit documents',
            'upload documents', 'download documents',
        ]);

        Role::findByName('viewer')->givePermissionTo([
            'view documents', 'download documents',
        ]);

        // ─── 4. Departments ───────────────────────────────
        $departments = [
            ['name' => 'Human Resources',     'code' => 'HR',   'description' => 'Departemen Sumber Daya Manusia'],
            ['name' => 'Finance',              'code' => 'FIN',  'description' => 'Departemen Keuangan'],
            ['name' => 'Purchasing',           'code' => 'PUR',  'description' => 'Departemen Pembelian'],
            ['name' => 'Warehouse',            'code' => 'WHS',  'description' => 'Departemen Gudang'],
            ['name' => 'Production',           'code' => 'PRD',  'description' => 'Departemen Produksi'],
            ['name' => 'Quality Assurance',    'code' => 'QA',   'description' => 'Departemen Jaminan Mutu'],
            ['name' => 'Information Technology','code' => 'IT',  'description' => 'Departemen Teknologi Informasi'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], $dept);
        }

        // ─── 5. Document Categories ───────────────────────
        $categories = [
            ['name' => 'Standard Operating Procedure', 'prefix' => 'SOP',    'description' => 'Prosedur operasional standar'],
            ['name' => 'Work Instruction',             'prefix' => 'WI',     'description' => 'Instruksi kerja detail'],
            ['name' => 'Form',                         'prefix' => 'FRM',    'description' => 'Formulir & dokumen isian'],
            ['name' => 'Policy',                       'prefix' => 'POL',    'description' => 'Kebijakan perusahaan'],
            ['name' => 'Contract',                     'prefix' => 'CTR',    'description' => 'Kontrak & perjanjian'],
            ['name' => 'Certificate',                  'prefix' => 'CERT',   'description' => 'Sertifikat & lisensi'],
        ];

        foreach ($categories as $cat) {
            DocumentCategory::firstOrCreate(['prefix' => $cat['prefix']], $cat);
        }

        // ─── 6. Users ─────────────────────────────────────
        $itDept = Department::where('code', 'IT')->first();
        $hrDept = Department::where('code', 'HR')->first();
        $qaDept = Department::where('code', 'QA')->first();

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@dcs.test'],
            [
                'name'          => 'Administrator',
                'password'      => Hash::make('password'),
                'department_id' => $itDept?->id,
                'is_active'     => true,
            ]
        );
        $admin->assignRole('admin');

        // Department Head HR
        $headHr = User::firstOrCreate(
            ['email' => 'head.hr@dcs.test'],
            [
                'name'          => 'Kepala HR',
                'password'      => Hash::make('password'),
                'department_id' => $hrDept?->id,
                'is_active'     => true,
            ]
        );
        $headHr->assignRole('department_head');

        // Department Head QA
        $headQa = User::firstOrCreate(
            ['email' => 'head.qa@dcs.test'],
            [
                'name'          => 'Kepala QA',
                'password'      => Hash::make('password'),
                'department_id' => $qaDept?->id,
                'is_active'     => true,
            ]
        );
        $headQa->assignRole('department_head');

        // Employee HR
        $empHr = User::firstOrCreate(
            ['email' => 'employee.hr@dcs.test'],
            [
                'name'          => 'Staff HR',
                'password'      => Hash::make('password'),
                'department_id' => $hrDept?->id,
                'is_active'     => true,
            ]
        );
        $empHr->assignRole('employee');

        $this->command->info('✓ Seeding selesai!');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Admin',           'admin@dcs.test',       'password'],
                ['Department Head', 'head.hr@dcs.test',     'password'],
                ['Department Head', 'head.qa@dcs.test',     'password'],
                ['Employee',        'employee.hr@dcs.test', 'password'],
            ]
        );
    }
}