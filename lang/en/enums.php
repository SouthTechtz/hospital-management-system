<?php

return [

    'system_role' => [
        'administrator' => 'Administrator',
        'ict_administrator' => 'ICT Administrator',
        'privacy_officer' => 'Privacy Officer',
        'medical_officer' => 'Medical Officer',
        'opd_doctor' => 'OPD Doctor',
        'triage_nurse' => 'Triage Nurse',
        'ward_nurse' => 'Ward Nurse',
        'pharmacist' => 'Pharmacist',
        'laboratory_technologist' => 'Laboratory Technologist',
        'radiologist' => 'Radiologist',
        'cashier' => 'Cashier',
        'records_officer' => 'Records Officer',
    ],

    'audit_action' => [
        'login' => 'Login',
        'login_failed' => 'Failed login',
        'logout' => 'Logout',
        'view' => 'Viewed',
        'create' => 'Created',
        'update' => 'Updated',
        'delete' => 'Deleted',
        'export' => 'Exported',
        'print' => 'Printed',
        'break_glass' => 'Break-glass access',
        'permission_change' => 'Permission change',
        'key_operation' => 'Credential operation',
        'void' => 'Voided',
        'merge' => 'Merged',
        'unmerge' => 'Unmerged',
        'mfa_enabled' => 'MFA enabled',
        'mfa_disabled' => 'MFA disabled',
    ],

    'triage_category' => [
        'red' => 'Red – Immediate',
        'orange' => 'Orange – Very urgent',
        'yellow' => 'Yellow – Urgent',
        'green' => 'Green – Standard',
        'blue' => 'Blue – Non-urgent',
    ],

    'invoice_status' => [
        'open' => 'Open',
        'partially_paid' => 'Partially paid',
        'paid' => 'Paid',
        'waived' => 'Waived',
        'cancelled' => 'Cancelled',
    ],

    'order_status' => [
        'draft' => 'Draft',
        'ordered' => 'Ordered',
        'in_progress' => 'In progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],

    'order_priority' => [
        'routine' => 'Routine',
        'urgent' => 'Urgent',
        'stat' => 'STAT',
    ],

    'payment_method' => [
        'cash' => 'Cash',
        'mobile_money' => 'Mobile money',
        'bank_transfer' => 'Bank transfer',
        'nhif' => 'NHIF',
        'waiver' => 'Waiver',
    ],

    'sex' => [
        'male' => 'Male',
        'female' => 'Female',
        'unknown' => 'Unknown',
    ],

    'payer_type' => [
        'cash' => 'Cash',
        'nhif' => 'NHIF',
        'exemption' => 'Exemption',
        'other' => 'Other',
    ],

    'order_type' => [
        'medication' => 'Medication',
        'laboratory' => 'Laboratory',
        'radiology' => 'Radiology',
        'procedure' => 'Procedure',
        'referral' => 'Referral',
    ],

    'encounter_type' => [
        'opd' => 'Outpatient (OPD)',
        'ipd' => 'Inpatient (Ward)',
        'emergency' => 'Emergency',
        'maternity' => 'Maternity',
        'paediatric' => 'Paediatric',
        'theatre' => 'Theatre',
        'laboratory' => 'Laboratory',
        'radiology' => 'Radiology',
        'pharmacy' => 'Pharmacy',
    ],

    'encounter_status' => [
        'open' => 'Open',
        'closed' => 'Closed',
        'cancelled' => 'Cancelled',
    ],

    'exemption_category' => [
        'child_under_five' => 'Child under five',
        'elderly' => 'Elderly',
        'pregnant' => 'Pregnant',
        'indigent' => 'Indigent',
        'chronic_condition' => 'Chronic condition',
        'staff' => 'Facility staff',
        'other' => 'Other',
    ],

    'diagnosis_type' => [
        'primary' => 'Primary',
        'secondary' => 'Secondary',
        'provisional' => 'Provisional',
        'final' => 'Final',
    ],

    'queue_entry_status' => [
        'waiting' => 'Waiting',
        'called' => 'Called',
        'in_progress' => 'In progress',
        'completed' => 'Completed',
        'removed' => 'Removed',
    ],

    'break_glass_status' => [
        'active' => 'Active',
        'expired' => 'Expired',
        'reviewed' => 'Reviewed',
    ],

    'directorate' => [
        'clinical_services' => 'Clinical Services',
        'diagnostics_and_therapeutics' => 'Diagnostics & Therapeutics',
        'nursing_and_midwifery' => 'Nursing & Midwifery',
        'administration_and_support' => 'Administration & Support',
        'public_health_and_reporting' => 'Public Health & Reporting',
        'system_administration' => 'System Administration',
    ],

    'consent_purpose' => [
        'treatment' => 'Treatment',
        'data_sharing' => 'Data sharing',
        'research' => 'Research',
        'audit' => 'Audit and review',
    ],

    'clinical_action' => [
        'prescribe' => 'Prescribe',
        'administer' => 'Administer',
        'dispense' => 'Dispense',
        'admin' => 'System administration',
    ],

];