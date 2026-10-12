<?php

namespace Database\Seeders;

use App\Enums\Directorate;
use App\Models\Department;
use App\Models\Facility;
use App\Models\Queue;
use Illuminate\Database\Seeder;

/**
 * Default facility record plus the departments and queues that drive the
 * work-queue screens (Part D7.2). The facility identity is configuration,
 * read from the environment, never hardcoded here.
 */
class FacilityDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        $facility = Facility::updateOrCreate(
            ['code' => config('hms.facility.code')],
            [
                'name' => env('HMS_FACILITY_NAME', 'Regional Referral Hospital'),
                'short_name' => env('HMS_FACILITY_SHORT_NAME', 'RRH'),
                'type' => env('HMS_FACILITY_TYPE', 'regional_referral_hospital'),
                'hfr_code' => env('HMS_HFR_CODE', 'RRH-TZ-0001'),
                'region' => env('HMS_FACILITY_REGION', 'North-West Zone'),
                'district' => env('HMS_FACILITY_DISTRICT', ''),
                'ward' => env('HMS_FACILITY_WARD', ''),
                'physical_address' => env('HMS_FACILITY_ADDRESS', 'P.O. Box 0000'),
                'timezone' => config('hms.facility.timezone'),
                'currency' => 'TZS',
                'is_active' => true,
            ]
        );

        $departments = [
            ['code' => 'OPD', 'name' => 'Outpatient Department', 'directorate' => Directorate::ClinicalServices, 'is_clinical' => true],
            ['code' => 'EMER', 'name' => 'Emergency Department', 'directorate' => Directorate::ClinicalServices, 'is_clinical' => true],
            ['code' => 'MAT', 'name' => 'Maternity', 'directorate' => Directorate::ClinicalServices, 'is_clinical' => true],
            ['code' => 'PAED', 'name' => 'Paediatrics', 'directorate' => Directorate::ClinicalServices, 'is_clinical' => true],
            ['code' => 'THEATRE', 'name' => 'Theatre', 'directorate' => Directorate::ClinicalServices, 'is_clinical' => true],
            ['code' => 'MEDW', 'name' => 'Adult Medical Ward', 'directorate' => Directorate::NursingAndMidwifery, 'is_clinical' => true],
            ['code' => 'SURGW', 'name' => 'Surgical Ward', 'directorate' => Directorate::NursingAndMidwifery, 'is_clinical' => true],
            ['code' => 'PEDW', 'name' => 'Paediatric Ward', 'directorate' => Directorate::NursingAndMidwifery, 'is_clinical' => true],
            ['code' => 'LAB', 'name' => 'Laboratory', 'directorate' => Directorate::DiagnosticsAndTherapeutics, 'is_clinical' => true],
            ['code' => 'RAD', 'name' => 'Radiology and Imaging', 'directorate' => Directorate::DiagnosticsAndTherapeutics, 'is_clinical' => true],
            ['code' => 'PHA', 'name' => 'Pharmacy', 'directorate' => Directorate::DiagnosticsAndTherapeutics, 'is_clinical' => true],
            ['code' => 'REC', 'name' => 'Records and Master Patient Index', 'directorate' => Directorate::AdministrationAndSupport, 'is_clinical' => false],
            ['code' => 'FIN', 'name' => 'Finance and Cashier', 'directorate' => Directorate::AdministrationAndSupport, 'is_clinical' => false],
            ['code' => 'ICT', 'name' => 'ICT Support', 'directorate' => Directorate::SystemAdministration, 'is_clinical' => false],
            ['code' => 'HMIS', 'name' => 'Public Health and HMIS', 'directorate' => Directorate::PublicHealthAndReporting, 'is_clinical' => false],
        ];

        $departmentIds = [];
        foreach ($departments as $department) {
            $departmentIds[$department['code']] = Department::updateOrCreate(
                ['facility_id' => $facility->getKey(), 'code' => $department['code']],
                [
                    'name' => $department['name'],
                    'directorate' => $department['directorate']->value,
                    'is_clinical' => $department['is_clinical'],
                    'is_active' => true,
                ]
            )->getKey();
        }

        $queues = [
            ['code' => 'TRIAGE-OPD', 'name' => 'OPD Triage', 'type' => 'triage', 'department_id' => $departmentIds['OPD']],
            ['code' => 'CLINIC-OPD', 'name' => 'OPD Doctors', 'type' => 'clinical', 'department_id' => $departmentIds['OPD']],
            ['code' => 'TRIAGE-EMER', 'name' => 'Emergency Triage', 'type' => 'triage', 'department_id' => $departmentIds['EMER']],
            ['code' => 'LAB-MAIN', 'name' => 'Laboratory', 'type' => 'laboratory', 'department_id' => $departmentIds['LAB']],
            ['code' => 'RAD-MAIN', 'name' => 'Radiology', 'type' => 'radiology', 'department_id' => $departmentIds['RAD']],
            ['code' => 'PHA-MAIN', 'name' => 'Pharmacy', 'type' => 'pharmacy', 'department_id' => $departmentIds['PHA']],
            ['code' => 'CAS-MAIN', 'name' => 'Cashier', 'type' => 'cashier', 'department_id' => $departmentIds['FIN']],
        ];

        foreach ($queues as $queue) {
            Queue::updateOrCreate(
                ['facility_id' => $facility->getKey(), 'code' => $queue['code']],
                [
                    'name' => $queue['name'],
                    'department_id' => $queue['department_id'],
                    'type' => $queue['type'],
                    'is_active' => true,
                ]
            );
        }
    }
}