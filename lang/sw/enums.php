<?php

return [

    'system_role' => [
        'administrator' => 'Msimamizi',
        'ict_administrator' => 'Msimamizi wa TEHAMA',
        'privacy_officer' => 'Afisa Usiri',
        'medical_officer' => 'Afisa Matibabu',
        'opd_doctor' => 'Daktari wa OPD',
        'triage_nurse' => 'Muuguzi wa Uchunguzi',
        'ward_nurse' => 'Muuguzi wa Wodi',
        'pharmacist' => 'Mfamasia',
        'laboratory_technologist' => 'Teknologia wa Maabara',
        'radiologist' => 'Daktari wa Radiologia',
        'cashier' => 'Mweka Hazina',
        'records_officer' => 'Afisa Kumbukumbu',
    ],

    'audit_action' => [
        'login' => 'Kuingia',
        'login_failed' => 'Kuingia kulikosea',
        'logout' => 'Kutoka',
        'view' => 'Imeonekana',
        'create' => 'Imeundwa',
        'update' => 'Imesasishwa',
        'delete' => 'Imefutwa',
        'export' => 'Imesafirishwa',
        'print' => 'Imechapishwa',
        'break_glass' => 'Ufikiaji wa break-glass',
        'permission_change' => 'Mabadiliko ya ruhusa',
        'key_operation' => 'Operesheni ya nywila',
        'void' => 'Imebatilishwa',
        'merge' => 'Imeunganishwa',
        'unmerge' => 'Imetenganishwa',
        'mfa_enabled' => 'MFA imewashwa',
        'mfa_disabled' => 'MFA imezimwa',
    ],

    'triage_category' => [
        'red' => 'Nyekundu – Haraka kabisa',
        'orange' => 'Chungwa – Haraka sana',
        'yellow' => 'Njano – Haraka',
        'green' => 'Kijani – Kawaida',
        'blue' => 'Buluu – Isiyo ya haraka',
    ],

    'invoice_status' => [
        'open' => 'Wazi',
        'partially_paid' => 'Imelipwa kiasi',
        'paid' => 'Imelipwa',
        'waived' => 'Imesamehewa',
        'cancelled' => 'Imebatilishwa',
    ],

    'order_status' => [
        'draft' => 'Rasimu',
        'ordered' => 'Imeagizwa',
        'in_progress' => 'Inaendelea',
        'completed' => 'Imekamilika',
        'cancelled' => 'Imebatilishwa',
    ],

    'order_priority' => [
        'routine' => 'Kawaida',
        'urgent' => 'Haraka',
        'stat' => 'STAT',
    ],

    'payment_method' => [
        'cash' => 'Fedha taslimu',
        'mobile_money' => 'Fedha za simu',
        'bank_transfer' => 'Uhamisho wa benki',
        'nhif' => 'NHIF',
        'waiver' => 'Msamaha',
    ],

    'sex' => [
        'male' => 'Mume',
        'female' => 'Mke',
        'unknown' => 'Haijulikani',
    ],

    'payer_type' => [
        'cash' => 'Fedha taslimu',
        'nhif' => 'NHIF',
        'exemption' => 'Msamaha',
        'other' => 'Nyingine',
    ],

    'order_type' => [
        'medication' => 'Dawa',
        'laboratory' => 'Maabara',
        'radiology' => 'Radiologia',
        'procedure' => 'Utaratibu',
        'referral' => 'Rufaa',
    ],

    'encounter_type' => [
        'opd' => 'Wagonjwa wa nje (OPD)',
        'ipd' => 'Wagonjwa wa ndani (Wodi)',
        'emergency' => 'Dharura',
        'maternity' => 'Uzazi',
        'paediatric' => 'Watoto',
        'theatre' => 'Chumba cha upasuaji',
        'laboratory' => 'Maabara',
        'radiology' => 'Radiologia',
        'pharmacy' => 'Famasia',
    ],

    'encounter_status' => [
        'open' => 'Wazi',
        'closed' => 'Imefungwa',
        'cancelled' => 'Imebatilishwa',
    ],

    'exemption_category' => [
        'child_under_five' => 'Mtoto chini ya miaka mitano',
        'elderly' => 'Mzee',
        'pregnant' => 'Mjamzito',
        'indigent' => 'Maskini',
        'chronic_condition' => 'Ugonjwa sugu',
        'staff' => 'Mfanyakazi wa kituo',
        'other' => 'Nyingine',
    ],

    'diagnosis_type' => [
        'primary' => 'Kuu',
        'secondary' => 'La pili',
        'provisional' => 'La muda',
        'final' => 'La mwisho',
    ],

    'queue_entry_status' => [
        'waiting' => 'Anasubiri',
        'called' => 'Ameitwa',
        'in_progress' => 'Anaendelea kuhudumiwa',
        'completed' => 'Amekamilika',
        'removed' => 'Ameondolewa',
    ],

    'break_glass_status' => [
        'active' => 'Inatumika',
        'expired' => 'Imekamilika muda',
        'reviewed' => 'Imepitiwa',
    ],

    'directorate' => [
        'clinical_services' => 'Huduma za Kliniki',
        'diagnostics_and_therapeutics' => 'Uchunguzi na Matibabu',
        'nursing_and_midwifery' => 'Uuguzi na Ukunga',
        'administration_and_support' => 'Utawala na Msaada',
        'public_health_and_reporting' => 'Afya ya Umma na Taarifa',
        'system_administration' => 'Usimamizi wa Mfumo',
    ],

    'consent_purpose' => [
        'treatment' => 'Matibabu',
        'data_sharing' => 'Kushirikiana taarifa',
        'research' => 'Utafiti',
        'audit' => 'Ukaguzi na upembuzi',
    ],

    'clinical_action' => [
        'prescribe' => 'Kuandika dawa',
        'administer' => 'Kutoa dawa',
        'dispense' => 'Kugawa dawa',
        'admin' => 'Usimamizi wa mfumo',
    ],

];