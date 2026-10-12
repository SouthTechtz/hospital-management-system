<?php

namespace Database\Seeders;

use App\Models\Drug;
use Illuminate\Database\Seeder;

/**
 * Essential-medicines subset used by the prescription autocomplete. The list
 * follows the Tanzania National Essential Medicines List (NEMLIT); quantities,
 * schedules and any change must be confirmed with the facility pharmacy and
 * the national system (VERIFY_WITH_PROVIDER, docs/ASSUMPTIONS.md).
 */
class DrugSeeder extends Seeder
{
    /**
     * [code, generic_name, brand_name, strength, form, route, unit, atc_code, is_controlled, is_essential, requires_prescription]
     */
    private const DRUGS = [
        ['PARA-500', 'Paracetamol', null, '500 mg', 'Tablet', 'Oral', 'mg', 'N02BE01', false, true, false],
        ['PARA-SYR', 'Paracetamol', 'Panadol Syrup', '120 mg/5 ml', 'Syrup', 'Oral', 'mg', 'N02BE01', false, true, false],
        ['IBU-400', 'Ibuprofen', null, '400 mg', 'Tablet', 'Oral', 'mg', 'M01AE01', false, true, true],
        ['AMOX-500', 'Amoxicillin', null, '500 mg', 'Capsule', 'Oral', 'mg', 'J01CA04', false, true, true],
        ['AMOX-125', 'Amoxicillin', null, '125 mg/5 ml', 'Suspension', 'Oral', 'mg', 'J01CA04', false, true, true],
        ['AMCL-625', 'Amoxicillin and clavulanic acid', 'Co-amoxiclav', '625 mg', 'Tablet', 'Oral', 'mg', 'J01CR02', false, true, true],
        ['CTR-1G', 'Ceftriaxone', null, '1 g', 'Injection (powder)', 'IV/IM', 'g', 'J01DD04', false, true, true],
        ['CTX-480', 'Cotrimoxazole', null, '480 mg', 'Tablet', 'Oral', 'mg', 'J01EE01', false, true, true],
        ['CTX-SYR', 'Cotrimoxazole', null, '200/40 mg per 5 ml', 'Suspension', 'Oral', 'mg', 'J01EE01', false, true, true],
        ['AZM-500', 'Azithromycin', null, '500 mg', 'Tablet', 'Oral', 'mg', 'J01FA10', false, true, true],
        ['CIP-500', 'Ciprofloxacin', null, '500 mg', 'Tablet', 'Oral', 'mg', 'J01MA02', false, true, true],
        ['DOX-100', 'Doxycycline', null, '100 mg', 'Capsule', 'Oral', 'mg', 'J01AA02', false, true, true],
        ['GEN-80', 'Gentamicin', null, '80 mg/2 ml', 'Injection', 'IV/IM', 'mg', 'J01GB03', false, true, true],
        ['MTZ-400', 'Metronidazole', null, '400 mg', 'Tablet', 'Oral', 'mg', 'J01XD01', false, true, true],
        ['ALB-400', 'Albendazole', null, '400 mg', 'Tablet', 'Oral', 'mg', 'P02CA03', false, true, false],
        ['PZQ-600', 'Praziquantel', null, '600 mg', 'Tablet', 'Oral', 'mg', 'P02BA01', false, true, true],
        ['AL-80', 'Artemether and lumefantrine', 'Coartem', '80/480 mg', 'Tablet', 'Oral', 'mg', 'P01BF01', false, true, true],
        ['ALS-40', 'Artesunate', null, '60 mg', 'Injection', 'IV/IM', 'mg', 'P01BE03', false, true, true],
        ['QN-300', 'Quinine', null, '300 mg', 'Tablet', 'Oral', 'mg', 'P01BC01', false, true, true],
        ['ORS', 'Oral rehydration salts', null, '20.5 g sachet', 'Powder', 'Oral', 'g', 'A07CA', false, true, false],
        ['ZINC-20', 'Zinc sulfate', null, '20 mg', 'Tablet (dispersible)', 'Oral', 'mg', 'A12CB', false, true, false],
        ['SALB-100', 'Salbutamol', null, '100 mcg/dose', 'Inhaler', 'Inhalation', 'mcg', 'R03AC02', false, true, true],
        ['PRED-5', 'Prednisolone', null, '5 mg', 'Tablet', 'Oral', 'mg', 'H02AB06', false, true, true],
        ['DEX-4', 'Dexamethasone', null, '4 mg', 'Injection', 'IV/IM', 'mg', 'H02AB02', false, true, true],
        ['CPM-4', 'Chlorphenamine', 'Chlorpheniramine', '4 mg', 'Tablet', 'Oral', 'mg', 'R06AB04', false, true, false],
        ['PRM-25', 'Promethazine', null, '25 mg', 'Tablet', 'Oral', 'mg', 'R06AD02', false, true, true],
        ['OMP-20', 'Omeprazole', null, '20 mg', 'Capsule', 'Oral', 'mg', 'A02BC01', false, true, true],
        ['MET-500', 'Metformin', null, '500 mg', 'Tablet', 'Oral', 'mg', 'A10BA02', false, true, true],
        ['GLIB-5', 'Glibenclamide', null, '5 mg', 'Tablet', 'Oral', 'mg', 'A10BB01', false, true, true],
        ['INS-R', 'Insulin (soluble)', null, '100 IU/ml', 'Injection', 'Subcutaneous', 'IU', 'A10AB01', false, true, true],
        ['AML-5', 'Amlodipine', null, '5 mg', 'Tablet', 'Oral', 'mg', 'C08CA01', false, true, true],
        ['NIF-20', 'Nifedipine', null, '20 mg', 'Tablet (retard)', 'Oral', 'mg', 'C08CA05', false, true, true],
        ['CAP-25', 'Captopril', null, '25 mg', 'Tablet', 'Oral', 'mg', 'C09AA01', false, true, true],
        ['FUR-40', 'Furosemide', null, '40 mg', 'Tablet', 'Oral', 'mg', 'C03CA01', false, true, true],
        ['HTZ-25', 'Hydrochlorothiazide', null, '25 mg', 'Tablet', 'Oral', 'mg', 'C03AA03', false, true, true],
        ['DIG-0.25', 'Digoxin', null, '0.25 mg', 'Tablet', 'Oral', 'mg', 'C01AA05', false, true, true],
        ['ASP-75', 'Acetylsalicylic acid', 'Aspirin', '75 mg', 'Tablet', 'Oral', 'mg', 'B01AC06', false, true, true],
        ['DZP-5', 'Diazepam', null, '5 mg', 'Tablet', 'Oral', 'mg', 'N05BA01', false, true, true],
        ['PHB-30', 'Phenobarbital', null, '30 mg', 'Tablet', 'Oral', 'mg', 'N03AA02', false, true, true],
        ['PHT-100', 'Phenytoin', null, '100 mg', 'Capsule', 'Oral', 'mg', 'N03AB02', false, true, true],
        ['CBZ-200', 'Carbamazepine', null, '200 mg', 'Tablet', 'Oral', 'mg', 'N03AF01', false, true, true],
        ['HAL-5', 'Haloperidol', null, '5 mg', 'Tablet', 'Oral', 'mg', 'N05AD01', false, true, true],
        ['AMI-25', 'Amitriptyline', null, '25 mg', 'Tablet', 'Oral', 'mg', 'N06AA09', false, true, true],
        ['FLX-20', 'Fluoxetine', null, '20 mg', 'Capsule', 'Oral', 'mg', 'N06AB03', false, true, true],
        ['FE-200', 'Ferrous sulfate', null, '200 mg', 'Tablet', 'Oral', 'mg', 'B03AA07', false, true, false],
        ['FOL-5', 'Folic acid', null, '5 mg', 'Tablet', 'Oral', 'mg', 'B03BB01', false, true, false],
        ['VITA-50', 'Vitamin A', null, '200 000 IU', 'Capsule', 'Oral', 'IU', 'A11CA01', false, true, false],
        ['MGS-50', 'Magnesium sulfate', null, '50% 10 ml', 'Injection', 'IV/IM', 'g', 'A12CC02', false, true, true],
        ['OXY-10', 'Oxytocin', null, '10 IU/ml', 'Injection', 'IV/IM', 'IU', 'H01BB02', false, true, true],
        ['MSO-200', 'Misoprostol', null, '200 mcg', 'Tablet', 'Oral', 'mcg', 'G02AD06', false, true, true],
        ['RHZE-TAB', 'Rifampicin, isoniazid, pyrazinamide and ethambutol', null, '150/75/400/275 mg', 'Tablet', 'Oral', 'mg', 'J04AM06', false, true, true],
        ['EV-400', 'Efavirenz', 'EFV 600', '600 mg', 'Tablet', 'Oral', 'mg', 'J05AG03', false, true, true],
        ['TDF-300', 'Tenofovir disoproxil fumarate', null, '300 mg', 'Tablet', 'Oral', 'mg', 'J05AF07', false, true, true],
        ['3TC-150', 'Lamivudine', null, '150 mg', 'Tablet', 'Oral', 'mg', 'J05AF05', false, true, true],
        ['NVP-200', 'Nevirapine', null, '200 mg', 'Tablet', 'Oral', 'mg', 'J05AG01', false, true, true],
        ['MOR-10', 'Morphine sulfate', null, '10 mg/5 ml', 'Oral solution', 'Oral', 'mg', 'N02AA01', true, true, true],
        ['MOR-INJ', 'Morphine sulfate', null, '10 mg/ml', 'Injection', 'IV/IM', 'mg', 'N02AA01', true, true, true],
        ['PETH-50', 'Pethidine', null, '50 mg/ml', 'Injection', 'IV/IM', 'mg', 'N02AB02', true, true, true],
        ['KET-50', 'Ketamine', null, '50 mg/ml', 'Injection', 'IV/IM', 'mg', 'N01AX03', true, true, true],
    ];

    public function run(): void
    {
        foreach (self::DRUGS as [$code, $genericName, $brandName, $strength, $form, $route, $unit, $atcCode, $isControlled, $isEssential, $requiresPrescription]) {
            Drug::updateOrCreate(
                ['code' => $code],
                [
                    'generic_name' => $genericName,
                    'brand_name' => $brandName,
                    'strength' => $strength,
                    'form' => $form,
                    'route' => $route,
                    'unit' => $unit,
                    'atc_code' => $atcCode,
                    'is_controlled' => $isControlled,
                    'is_essential' => $isEssential,
                    'requires_prescription' => $requiresPrescription,
                    'is_active' => true,
                ]
            );
        }
    }
}