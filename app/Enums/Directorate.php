<?php

namespace App\Enums;

/**
 * Top-level organisational grouping used by the left navigation (Part D4).
 */
enum Directorate: string
{
    case ClinicalServices = 'clinical_services';
    case DiagnosticsAndTherapeutics = 'diagnostics_and_therapeutics';
    case NursingAndMidwifery = 'nursing_and_midwifery';
    case AdministrationAndSupport = 'administration_and_support';
    case PublicHealthAndReporting = 'public_health_and_reporting';
    case SystemAdministration = 'system_administration';

    public function labelKey(): string
    {
        return 'enums.directorate.'.$this->value;
    }
}
