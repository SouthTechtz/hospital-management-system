<?php

namespace Database\Seeders;

use App\Enums\SystemRole;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * One demobilisation-restricted demo user per system role. Every account is
 * created inactive-by-flag with a forced password change on first login and
 * must complete TOTP enrolment where the role requires MFA (Part B).
 */
class AdminUserSeeder extends Seeder
{
    /**
     * Shared one-time password for demo accounts. It is replaced on first
     * login (must_change_password). Never use this value in production.
     */
    public const DEMO_PASSWORD = 'TzHMS#Seed2026';

    private const USERS = [
        SystemRole::Administrator->value => ['admin', 'Hospital', 'Administrator', 'Hospital Administrator', null],
        SystemRole::IctAdministrator->value => ['ict', 'Systems', 'Administrator', 'ICT System Administrator', 'ICT'],
        SystemRole::PrivacyOfficer->value => ['privacy', 'Data', 'Protection', 'Privacy Officer', 'REC'],
        SystemRole::MedicalOfficer->value => ['medical', 'Senior', 'Clinician', 'Medical Officer', 'OPD'],
        SystemRole::OpdDoctor->value => ['doctor', 'Ali', 'Mtoro', 'OPD Doctor', 'OPD'],
        SystemRole::TriageNurse->value => ['triage', 'Neema', 'Kawambwa', 'Triage Nurse', 'OPD'],
        SystemRole::WardNurse->value => ['ward', 'Grace', 'Mmari', 'Ward Nurse', 'MEDW'],
        SystemRole::Pharmacist->value => ['pharmacy', 'Baraka', 'Komba', 'Pharmacist', 'PHA'],
        SystemRole::LaboratoryTechnologist->value => ['lab', 'Joseph', 'Mushi', 'Laboratory Technologist', 'LAB'],
        SystemRole::Radiologist->value => ['radio', 'Rehema', 'Swai', 'Radiologist', 'RAD'],
        SystemRole::Cashier->value => ['cashier', 'Zawadi', 'Massawe', 'Cashier', 'FIN'],
        SystemRole::RecordsOfficer->value => ['records', 'Mariam', 'Nkya', 'Records Officer', 'REC'],
    ];

    public function run(): void
    {
        $facilityId = \App\Models\Facility::query()->value('id');

        foreach (self::USERS as $roleName => [$username, $firstName, $lastName, $jobTitle, $departmentCode]) {
            $role = Role::query()->where('name', $roleName)->firstOrFail();
            $departmentId = $departmentCode
                ? Department::query()->where('code', $departmentCode)->value('id')
                : null;

            $user = User::updateOrCreate(
                ['username' => $username],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'job_title' => $jobTitle,
                    'email' => $username.'@rhh.local',
                    'password' => Hash::make(self::DEMO_PASSWORD),
                    'facility_id' => $facilityId,
                    'department_id' => $departmentId,
                    'primary_role_id' => $role->getKey(),
                    'must_change_password' => true,
                    'password_changed_at' => null,
                    'is_active' => true,
                    'preferred_locale' => 'en',
                ]
            );

            $user->roles()->syncWithoutDetaching([$role->getKey()]);
            $user->forgetPermissionCache();
        }
    }
}