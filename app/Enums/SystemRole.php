<?php

namespace App\Enums;

/**
 * System role slugs seeded by RolePermissionSeeder. Roles are database rows
 * (editable in the permission matrix); these constants are the stable handles
 * the application relies on for safety-critical behaviour.
 */
enum SystemRole: string
{
    case Administrator = 'administrator';
    case IctAdministrator = 'ict_administrator';
    case PrivacyOfficer = 'privacy_officer';
    case MedicalOfficer = 'medical_officer';
    case OpdDoctor = 'opd_doctor';
    case TriageNurse = 'triage_nurse';
    case WardNurse = 'ward_nurse';
    case Pharmacist = 'pharmacist';
    case LaboratoryTechnologist = 'laboratory_technologist';
    case Radiologist = 'radiologist';
    case Cashier = 'cashier';
    case RecordsOfficer = 'records_officer';

    public function labelKey(): string
    {
        return 'enums.system_role.'.$this->value;
    }

    /**
     * Roles for which MFA (TOTP) is mandatory (Part B).
     */
    public function requiresMfa(): bool
    {
        return in_array($this, [self::Administrator, self::IctAdministrator, self::PrivacyOfficer, self::MedicalOfficer, self::OpdDoctor, self::Pharmacist], true);
    }

    /**
     * Role home (work queue) route name for this role (Part D7 item 2).
     */
    public function homeRoute(): string
    {
        return match ($this) {
            self::OpdDoctor, self::MedicalOfficer => 'home.doctor',
            self::TriageNurse => 'home.triage',
            self::WardNurse => 'home.ward',
            self::LaboratoryTechnologist => 'home.laboratory',
            self::Radiologist => 'home.radiology',
            self::Pharmacist => 'home.pharmacy',
            self::Cashier => 'home.cashier',
            self::RecordsOfficer => 'home.records',
            self::Administrator, self::IctAdministrator, self::PrivacyOfficer => 'home.administrator',
        };
    }
}
