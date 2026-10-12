<?php

namespace Database\Seeders;

use App\Enums\SystemRole;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the twelve system roles (Part B), the permission catalog and the
 * role-permission matrix. Permission names are the stable strings checked by
 * policies and middleware; they must never be renamed without a migration.
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * Permission catalog. Each entry is [module => [name => [en, sw]]].
     * The catalog is deliberately broader than today's code so that upcoming
     * modules (reports, downtime) ship with a defined permission surface.
     */
    private const PERMISSIONS = [
        'patient' => [
            'patient.view' => ['View patient records in their care', 'Kutazama rekodi za mgonjwa wanaomhudumia'],
            'patient.search' => ['Search the Master Patient Index', 'Kutafuta katika Fahirisi Kuu ya Wagonjwa'],
            'patient.view.any' => ['View any patient record', 'Kutazama rekodi ya mgonjwa yeyote'],
            'patient.register' => ['Register new patients', 'Kusajili wagonjwa wapya'],
            'patient.update' => ['Update patient records', 'Kusasisha rekodi za wagonjwa'],
            'patient.delete' => ['Void patient records', 'Kubatilisha rekodi za wagonjwa'],
            'patient.merge' => ['Merge duplicate patient records', 'Kuunganisha rekodi za mgonjwa maradufu'],
            'patient.break_glass' => ['Request break-glass access', 'Kuomba ufikiaji wa break-glass'],
            'patient.allergy.manage' => ['Record and confirm allergies', 'Kurekodi na kuthibitisha mzio'],
        ],
        'encounter' => [
            'encounter.view' => ['View encounters in their area', 'Kutazama ziara za eneo lao'],
            'encounter.view.any' => ['View any encounter', 'Kutazama ziara yoyote'],
            'encounter.create' => ['Open new encounters', 'Kufungua ziara mpya'],
            'encounter.update' => ['Update encounters', 'Kusasisha ziara'],
            'encounter.close' => ['Close encounters', 'Kufunga ziara'],
        ],
        'consultation' => [
            'consultation.view' => ['View consultation notes', 'Kutazama maelezo ya uchunguzi'],
            'consultation.write' => ['Record consultations and diagnoses', 'Kuandika uchunguzi na utambuzi'],
        ],
        'order' => [
            'order.view' => ['View orders', 'Kutazama maagizo'],
            'order.create' => ['Place orders and prescriptions', 'Kuweka maagizo na hati za dawa'],
            'order.update' => ['Execute orders (dispense, perform)', 'Kutekeleza maagizo (kugawa, kufanya)'],
            'order.void' => ['Void orders', 'Kubatilisha maagizo'],
        ],
        'queue' => [
            'queue.view' => ['View queues', 'Kutazama foleni'],
            'queue.create' => ['Add patients to queues', 'Kuongeza wagonjwa kwenye foleni'],
            'queue.update' => ['Manage queue status', 'Kudhibiti hali ya foleni'],
        ],
        'triage' => [
            'triage.view' => ['View triage assessments', 'Kutazama tathmini za uchunguzi'],
            'triage.create' => ['Record triage assessments', 'Kurekodi tathmini za uchunguzi'],
            'triage.update' => ['Update triage assessments', 'Kusasisha tathmini za uchunguzi'],
        ],
        'billing' => [
            'invoice.view' => ['View invoices', 'Kutazama ankara'],
            'invoice.create' => ['Open invoices', 'Kufungua ankara'],
            'invoice.pay' => ['Record payments', 'Kurekodi malipo'],
            'invoice.void' => ['Void invoices and payments', 'Kubatilisha ankara na malipo'],
            'exemption.manage' => ['Approve exemptions', 'Kuidhinisha msamaha wa ada'],
        ],
        'user' => [
            'user.view' => ['View user accounts', 'Kutazama akaunti za watumiaji'],
            'user.create' => ['Create user accounts', 'Kuunda akaunti za watumiaji'],
            'user.update' => ['Update user accounts', 'Kusasisha akaunti za watumiaji'],
            'user.update.self' => ['Update own profile', 'Kusasisha taarifa zake mwenyewe'],
            'user.assign_role' => ['Assign roles to users', 'Kumpa mtumiaji jukumu'],
            'user.deactivate' => ['Deactivate or reactivate users', 'Kusimamisha au kurejesha watumiaji'],
        ],
        'role' => [
            'role.view' => ['View roles', 'Kutazama majukumu'],
            'role.manage' => ['Manage roles and permissions', 'Kudhibiti majukumu na ruhusa'],
        ],
        'facility' => [
            'facility.view' => ['View facility settings', 'Kutazama mipangilio ya kituo'],
            'facility.manage' => ['Manage facility settings', 'Kudhibiti mipangilio ya kituo'],
        ],
        'department' => [
            'department.view' => ['View departments', 'Kutazama idara'],
            'department.manage' => ['Manage departments', 'Kudhibiti idara'],
        ],
        'audit' => [
            'audit.view' => ['View audit logs', 'Kutazama kumbukumbu za ukaguzi'],
            'audit.export' => ['Export audit logs', 'Kusafirisha kumbukumbu za ukaguzi'],
        ],
        'breakglass' => [
            'breakglass.use' => ['Request break-glass access', 'Kuomba ufikiaji wa dharura'],
            'breakglass.review' => ['Review break-glass accesses', 'Kupitia ufikiaji wa dharura'],
        ],
        'credentials' => [
            'credentials.manage' => ['Manage outbound credentials', 'Kudhibiti nywila za mawasiliano ya nje'],
            'credentials.manage_secret' => ['View outbound credential secrets', 'Kutazama siri za nywila za nje'],
        ],
        'apikey' => [
            'apikey.manage' => ['Manage inbound API keys', 'Kudhibiti funguo za API za kuingia'],
        ],
        'settings' => [
            'settings.view' => ['View system settings', 'Kutazama mipangilio ya mfumo'],
            'settings.manage' => ['Change system settings', 'Kubadilisha mipangilio ya mfumo'],
        ],
        'report' => [
            'report.view' => ['View reports', 'Kutazama ripoti'],
            'report.export' => ['Export reports', 'Kusafirisha ripoti'],
        ],
        'downtime' => [
            'downtime.view' => ['View downtime operations', 'Kutazama shughuli za wakati wa usumbufu'],
            'downtime.manage' => ['Perform downtime operations', 'Kufanya shughuli za wakati wa usumbufu'],
        ],
    ];

    /**
     * Role matrix: role name => permission names granted.
     */
    private const MATRIX = [
        'administrator' => [
            'patient.view', 'patient.search', 'patient.view.any',
            'encounter.view', 'encounter.view.any',
            'consultation.view',
            'order.view',
            'queue.view',
            'triage.view',
            'invoice.view', 'exemption.manage',
            'user.view', 'user.create', 'user.update', 'user.update.self', 'user.assign_role', 'user.deactivate',
            'role.view', 'role.manage',
            'facility.view', 'facility.manage',
            'department.view', 'department.manage',
            'audit.view', 'audit.export',
            'breakglass.review',
            'settings.view', 'settings.manage',
            'report.view', 'report.export',
            'downtime.view', 'downtime.manage',
        ],
        'ict_administrator' => [
            // Bypass applies via isIctAdministrator(); the catalog is still
            // granted in full for clarity and for role-scoped checks.
            'patient.view', 'patient.search', 'patient.view.any', 'patient.register', 'patient.update', 'patient.delete', 'patient.merge', 'patient.break_glass', 'patient.allergy.manage',
            'encounter.view', 'encounter.view.any', 'encounter.create', 'encounter.update', 'encounter.close',
            'consultation.view', 'consultation.write',
            'order.view', 'order.create', 'order.update', 'order.void',
            'queue.view', 'queue.create', 'queue.update',
            'triage.view', 'triage.create', 'triage.update',
            'invoice.view', 'invoice.create', 'invoice.pay', 'invoice.void', 'exemption.manage',
            'user.view', 'user.create', 'user.update', 'user.update.self', 'user.assign_role', 'user.deactivate',
            'role.view', 'role.manage',
            'facility.view', 'facility.manage',
            'department.view', 'department.manage',
            'audit.view', 'audit.export',
            'breakglass.use', 'breakglass.review',
            'credentials.manage', 'credentials.manage_secret',
            'apikey.manage',
            'settings.view', 'settings.manage',
            'report.view', 'report.export',
            'downtime.view', 'downtime.manage',
        ],
        'privacy_officer' => [
            'patient.view', 'patient.search', 'patient.view.any',
            'encounter.view', 'encounter.view.any',
            'consultation.view',
            'order.view',
            'queue.view', 'triage.view',
            'invoice.view',
            'user.view',
            'audit.view', 'audit.export',
            'breakglass.review',
            'settings.view',
            'report.view', 'report.export',
        ],
        'medical_officer' => [
            'patient.view', 'patient.search', 'patient.view.any', 'patient.register', 'patient.update', 'patient.break_glass', 'patient.allergy.manage',
            'encounter.view', 'encounter.view.any', 'encounter.create', 'encounter.update', 'encounter.close',
            'consultation.view', 'consultation.write',
            'order.view', 'order.create', 'order.update', 'order.void',
            'queue.view', 'queue.create', 'queue.update',
            'triage.view', 'triage.create', 'triage.update',
            'invoice.view',
        ],
        'opd_doctor' => [
            'patient.view', 'patient.search', 'patient.register', 'patient.update', 'patient.break_glass', 'patient.allergy.manage',
            'encounter.view', 'encounter.create', 'encounter.update', 'encounter.close',
            'consultation.view', 'consultation.write',
            'order.view', 'order.create', 'order.void',
            'queue.view', 'queue.create', 'queue.update',
            'triage.view',
            'invoice.view',
        ],
        'triage_nurse' => [
            'patient.view', 'patient.search', 'patient.register', 'patient.update',
            'encounter.view', 'encounter.create', 'encounter.update',
            'queue.view', 'queue.create', 'queue.update',
            'triage.view', 'triage.create', 'triage.update',
            'order.view',
            'consultation.view',
        ],
        'ward_nurse' => [
            'patient.view', 'patient.search', 'patient.allergy.manage',
            'encounter.view',
            'consultation.view',
            'order.view', 'order.update',
            'queue.view',
            'triage.view',
        ],
        'pharmacist' => [
            'patient.view', 'patient.search',
            'encounter.view',
            'consultation.view',
            'order.view', 'order.update', 'order.void',
            'queue.view', 'queue.create', 'queue.update',
            'invoice.view',
        ],
        'laboratory_technologist' => [
            'patient.view', 'patient.search',
            'encounter.view',
            'order.view', 'order.update',
            'queue.view', 'queue.create', 'queue.update',
            'triage.view',
        ],
        'radiologist' => [
            'patient.view', 'patient.search',
            'encounter.view',
            'consultation.view',
            'order.view', 'order.update',
            'queue.view', 'queue.create', 'queue.update',
            'triage.view',
        ],
        'cashier' => [
            'patient.view', 'patient.search',
            'encounter.view',
            'queue.view',
            'invoice.view', 'invoice.create', 'invoice.pay', 'invoice.void', 'exemption.manage',
        ],
        'records_officer' => [
            'patient.view', 'patient.search', 'patient.view.any', 'patient.register', 'patient.update', 'patient.delete', 'patient.merge', 'patient.allergy.manage',
            'encounter.view', 'encounter.create', 'encounter.update',
            'queue.view', 'queue.create',
            'triage.view',
            'invoice.view',
        ],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $this->seedPermissions();

            foreach (SystemRole::cases() as $systemRole) {
                $this->seedRole($systemRole);
            }
        });
    }

    private function seedPermissions(): void
    {
        foreach (self::PERMISSIONS as $module => $permissions) {
            foreach ($permissions as $name => [$en, $sw]) {
                Permission::updateOrCreate(
                    ['name' => $name],
                    [
                        'module' => $module,
                        'description_en' => $en,
                        'description_sw' => $sw,
                    ]
                );
            }
        }
    }

    private function seedRole(SystemRole $systemRole): void
    {
        $role = Role::updateOrCreate(
            ['name' => $systemRole->value],
            [
                'display_name_en' => __($systemRole->labelKey(), [], 'en'),
                'display_name_sw' => __($systemRole->labelKey(), [], 'sw'),
                'scope' => 'system',
                'description_en' => 'System role: '.$systemRole->value,
                'description_sw' => 'Jukumu la mfumo: '.$systemRole->value,
                'requires_mfa' => $systemRole->requiresMfa(),
                'is_system' => true,
            ]
        );

        $permissionNames = self::MATRIX[$systemRole->value] ?? [];

        $permissionIds = Permission::query()
            ->whereIn('name', $permissionNames)
            ->pluck('id');

        $role->syncPermissionsSafely($permissionIds->all());
    }
}