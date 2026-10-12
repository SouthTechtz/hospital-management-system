<?php

namespace Database\Seeders;

use App\Models\ServiceTariff;
use Illuminate\Database\Seeder;

/**
 * Sample service tariff list for cash and exemption billing (Part E, Phase 1).
 * Prices are placeholders for local development; every row is marked
 * VERIFY_WITH_PROVIDER and must be confirmed with the facility finance office
 * and the national pricing schedule before production use.
 */
class TariffSeeder extends Seeder
{
    /**
     * [item_code, item_name, category, unit_price, is_exemptible]
     */
    private const TARIFFS = [
        ['REG-OPD', 'Registration (Outpatient)', 'registration', 2000, true],
        ['REG-EMER', 'Registration (Emergency)', 'registration', 5000, true],
        ['REG-IPD', 'Admission registration', 'registration', 5000, true],
        ['CON-OPD', 'Consultation (General OPD)', 'consultation', 5000, true],
        ['CON-FU', 'Consultation (Follow-up)', 'consultation', 3000, true],
        ['CON-SPEC', 'Consultation (Specialist)', 'consultation', 10000, true],
        ['CON-ANC', 'Antenatal visit', 'consultation', 3000, true],
        ['LAB-MAL', 'Malaria rapid diagnostic test', 'laboratory', 3000, true],
        ['LAB-FBC', 'Full blood count', 'laboratory', 8000, true],
        ['LAB-URI', 'Urinalysis', 'laboratory', 3000, true],
        ['LAB-LFT', 'Liver function test', 'laboratory', 15000, true],
        ['LAB-RFT', 'Renal function test', 'laboratory', 15000, true],
        ['LAB-BS', 'Blood glucose test', 'laboratory', 2000, true],
        ['LAB-TB', 'Tuberculosis microscopy', 'laboratory', 5000, true],
        ['LAB-HIV', 'HIV rapid test', 'laboratory', 3000, true],
        ['LAB-CS', 'Culture and sensitivity', 'laboratory', 20000, false],
        ['RAD-XRAY', 'Chest X-ray', 'procedure', 15000, false],
        ['RAD-USS', 'Abdominal ultrasound', 'procedure', 25000, false],
        ['RAD-OCG', 'Obstetric ultrasound', 'procedure', 20000, false],
        ['PROC-MAJ', 'Major surgical procedure', 'procedure', 500000, false],
        ['PROC-MIN', 'Minor surgical procedure', 'procedure', 50000, false],
        ['PROC-DRESS', 'Wound dressing', 'procedure', 5000, true],
        ['PROC-STITCH', 'Suturing of laceration', 'procedure', 10000, true],
        ['MAT-DEL', 'Normal delivery package', 'procedure', 100000, true],
        ['MAT-CS', 'Caesarean section package', 'procedure', 400000, false],
        ['PHA-DISP', 'Dispensing fee', 'pharmacy', 1000, true],
        ['PHA-IV', 'Intravenous infusion administration', 'pharmacy', 3000, true],
        ['PHA-INJ', 'Injection administration fee', 'pharmacy', 2000, true],
        ['OTH-ADMIN', 'Administration fee', 'other', 2000, true],
        ['OTH-CERT', 'Medical certificate', 'other', 5000, true],
    ];

    public function run(): void
    {
        foreach (self::TARIFFS as [$itemCode, $itemName, $category, $unitPrice, $isExemptible]) {
            ServiceTariff::updateOrCreate(
                ['facility_id' => null, 'item_code' => $itemCode],
                [
                    'item_name' => $itemName,
                    'category' => $category,
                    'unit_price' => $unitPrice,
                    'currency' => 'TZS',
                    'is_exemptible' => $isExemptible,
                    'is_active' => true,
                    'effective_from' => now()->toDateString(),
                    'verification_status' => 'VERIFY_WITH_PROVIDER',
                ]
            );
        }
    }
}