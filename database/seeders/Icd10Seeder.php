<?php

namespace Database\Seeders;

use App\Models\Icd10Code;
use Illuminate\Database\Seeder;

/**
 * A practical ICD-10 subset used by the diagnosis autocomplete. ICD-10 is the
 * system of record (Part A). Every row is flagged is_common_tanzania; the
 * full ICD-10 catalogue is imported by the national integration, not bundled.
 */
class Icd10Seeder extends Seeder
{
    /**
     * [code, description, chapter, category, is_billable]
     */
    private const CODES = [
        ['A09', 'Diarrhoea and gastroenteritis of presumed infectious origin', 'Certain infectious and parasitic diseases', 'Intestinal infectious diseases', true],
        ['A15', 'Respiratory tuberculosis, bacteriologically and histologically confirmed', 'Certain infectious and parasitic diseases', 'Tuberculosis', true],
        ['A30', 'Leprosy [Hansen disease]', 'Certain infectious and parasitic diseases', 'Other bacterial diseases', true],
        ['A41', 'Other septicaemia', 'Certain infectious and parasitic diseases', 'Other bacterial diseases', true],
        ['A54', 'Gonococcal infection', 'Certain infectious and parasitic diseases', 'Infections with a predominantly sexual mode of transmission', true],
        ['A80', 'Acute poliomyelitis', 'Certain infectious and parasitic diseases', 'Viral infections of the central nervous system', false],
        ['B05', 'Measles', 'Certain infectious and parasitic diseases', 'Viral infections characterised by skin and mucous membrane lesions', false],
        ['B20', 'Unspecified human immunodeficiency virus [HIV] disease', 'Certain infectious and parasitic diseases', 'Human immunodeficiency virus [HIV] disease', false],
        ['B50', 'Plasmodium falciparum malaria', 'Certain infectious and parasitic diseases', 'Protozoal diseases', true],
        ['B54', 'Unspecified malaria', 'Certain infectious and parasitic diseases', 'Protozoal diseases', true],
        ['B77', 'Ascariasis', 'Certain infectious and parasitic diseases', 'Helminthiases', true],
        ['C18', 'Malignant neoplasm of colon', 'Neoplasms', 'Malignant neoplasms of digestive organs', true],
        ['C50', 'Malignant neoplasm of breast', 'Neoplasms', 'Malignant neoplasms of breast', true],
        ['C53', 'Malignant neoplasm of cervix uteri', 'Neoplasms', 'Malignant neoplasms of female genital organs', true],
        ['C61', 'Malignant neoplasm of prostate', 'Neoplasms', 'Malignant neoplasms of male genital organs', true],
        ['D50', 'Iron deficiency anaemia', 'Diseases of the blood and blood-forming organs', 'Nutritional anaemias', true],
        ['D64', 'Other anaemias', 'Diseases of the blood and blood-forming organs', 'Aplastic and other anaemias', true],
        ['E10', 'Type 1 diabetes mellitus', 'Endocrine, nutritional and metabolic diseases', 'Diabetes mellitus', true],
        ['E11', 'Type 2 diabetes mellitus', 'Endocrine, nutritional and metabolic diseases', 'Diabetes mellitus', true],
        ['E14', 'Unspecified diabetes mellitus', 'Endocrine, nutritional and metabolic diseases', 'Diabetes mellitus', true],
        ['E44', 'Protein-energy malnutrition of moderate and mild degree', 'Endocrine, nutritional and metabolic diseases', 'Malnutrition', true],
        ['E46', 'Unspecified protein-energy malnutrition', 'Endocrine, nutritional and metabolic diseases', 'Malnutrition', true],
        ['E86', 'Volume depletion', 'Endocrine, nutritional and metabolic diseases', 'Metabolic disorders', true],
        ['F32', 'Depressive episode', 'Mental and behavioural disorders', 'Mood [affective] disorders', true],
        ['G40', 'Epilepsy', 'Diseases of the nervous system', 'Episodic and paroxysmal disorders', true],
        ['G43', 'Migraine', 'Diseases of the nervous system', 'Episodic and paroxysmal disorders', true],
        ['H10', 'Conjunctivitis', 'Diseases of the eye and adnexa', 'Disorders of conjunctiva', true],
        ['H66', 'Suppurative and unspecified otitis media', 'Diseases of the ear and mastoid process', 'Diseases of middle ear and mastoid', true],
        ['I10', 'Essential (primary) hypertension', 'Diseases of the circulatory system', 'Hypertensive diseases', true],
        ['I20', 'Angina pectoris', 'Diseases of the circulatory system', 'Ischaemic heart diseases', true],
        ['I21', 'Acute myocardial infarction', 'Diseases of the circulatory system', 'Ischaemic heart diseases', true],
        ['I50', 'Heart failure', 'Diseases of the circulatory system', 'Other forms of heart disease', true],
        ['J00', 'Acute nasopharyngitis [common cold]', 'Diseases of the respiratory system', 'Acute upper respiratory infections', true],
        ['J02', 'Acute pharyngitis', 'Diseases of the respiratory system', 'Acute upper respiratory infections', true],
        ['J03', 'Acute tonsillitis', 'Diseases of the respiratory system', 'Acute upper respiratory infections', true],
        ['J06', 'Acute upper respiratory infections of multiple and unspecified sites', 'Diseases of the respiratory system', 'Acute upper respiratory infections', true],
        ['J11', 'Influenza due to unidentified influenza virus', 'Diseases of the respiratory system', 'Influenza and pneumonia', true],
        ['J15', 'Bacterial pneumonia, not elsewhere classified', 'Diseases of the respiratory system', 'Influenza and pneumonia', true],
        ['J18', 'Pneumonia, organism unspecified', 'Diseases of the respiratory system', 'Influenza and pneumonia', true],
        ['J20', 'Acute bronchitis', 'Diseases of the respiratory system', 'Other acute lower respiratory infections', true],
        ['J45', 'Asthma', 'Diseases of the respiratory system', 'Chronic lower respiratory diseases', true],
        ['K29', 'Gastritis and duodenitis', 'Diseases of the digestive system', 'Diseases of oesophagus, stomach and duodenum', true],
        ['K35', 'Acute appendicitis', 'Diseases of the digestive system', 'Diseases of appendix', true],
        ['K52', 'Other noninfective gastroenteritis and colitis', 'Diseases of the digestive system', 'Noninfective enteritis and colitis', true],
        ['K76', 'Other diseases of liver', 'Diseases of the digestive system', 'Diseases of liver', true],
        ['L02', 'Cutaneous abscess, furuncle and carbuncle', 'Diseases of the skin and subcutaneous tissue', 'Infections of the skin and subcutaneous tissue', true],
        ['L03', 'Cellulitis', 'Diseases of the skin and subcutaneous tissue', 'Infections of the skin and subcutaneous tissue', true],
        ['M54', 'Dorsalgia', 'Diseases of the musculoskeletal system and connective tissue', 'Dorsopathies', true],
        ['N10', 'Acute tubulo-interstitial nephritis', 'Diseases of the genitourinary system', 'Renal tubulo-interstitial diseases', true],
        ['N20', 'Calculus of kidney and ureter', 'Diseases of the genitourinary system', 'Urolithiasis', true],
        ['N30', 'Cystitis', 'Diseases of the genitourinary system', 'Other diseases of urinary system', true],
        ['N39', 'Other disorders of urinary system', 'Diseases of the genitourinary system', 'Other diseases of urinary system', true],
        ['N70', 'Salpingitis and oophoritis', 'Diseases of the genitourinary system', 'Inflammatory diseases of female pelvic organs', true],
        ['O03', 'Spontaneous abortion', 'Pregnancy, childbirth and the puerperium', 'Pregnancy with abortive outcome', true],
        ['O15', 'Eclampsia', 'Pregnancy, childbirth and the puerperium', 'Oedema, proteinuria and hypertensive disorders in pregnancy', false],
        ['O80', 'Single spontaneous delivery', 'Pregnancy, childbirth and the puerperium', 'Delivery', true],
        ['O86', 'Other puerperal infections', 'Pregnancy, childbirth and the puerperium', 'Complications predominantly related to the puerperium', true],
        ['P22', 'Respiratory distress of newborn', 'Certain conditions originating in the perinatal period', 'Respiratory and cardiovascular disorders specific to the perinatal period', true],
        ['P59', 'Neonatal jaundice from other and unspecified causes', 'Certain conditions originating in the perinatal period', 'Haemorrhagic and haematological disorders of fetus and newborn', true],
        ['R05', 'Cough', 'Symptoms, signs and abnormal clinical and laboratory findings', 'Symptoms and signs involving the circulatory and respiratory systems', false],
        ['R10', 'Abdominal and pelvic pain', 'Symptoms, signs and abnormal clinical and laboratory findings', 'Symptoms and signs involving the digestive system and abdomen', false],
        ['R11', 'Nausea and vomiting', 'Symptoms, signs and abnormal clinical and laboratory findings', 'Symptoms and signs involving the digestive system and abdomen', false],
        ['R50', 'Fever of other and unknown origin', 'Symptoms, signs and abnormal clinical and laboratory findings', 'General symptoms and signs', false],
        ['R51', 'Headache', 'Symptoms, signs and abnormal clinical and laboratory findings', 'General symptoms and signs', false],
        ['S52', 'Fracture of forearm', 'Injury, poisoning and certain other consequences of external causes', 'Injuries to the elbow and forearm', true],
        ['S72', 'Fracture of femur', 'Injury, poisoning and certain other consequences of external causes', 'Injuries to the hip and thigh', true],
        ['T14', 'Injury of unspecified body region', 'Injury, poisoning and certain other consequences of external causes', 'Injuries involving multiple body regions', true],
        ['Z00', 'General examination and investigation of persons without complaint or reported diagnosis', 'Factors influencing health status and contact with health services', 'Persons encountering health services for general examination', false],
        ['Z30', 'Contraceptive management', 'Factors influencing health status and contact with health services', 'Persons encountering health services in circumstances related to reproduction', false],
        ['Z34', 'Supervision of normal pregnancy', 'Factors influencing health status and contact with health services', 'Persons encountering health services in circumstances related to reproduction', false],
    ];

    public function run(): void
    {
        foreach (self::CODES as [$code, $description, $chapter, $category, $isBillable]) {
            Icd10Code::updateOrCreate(
                ['code' => $code],
                [
                    'description' => $description,
                    'chapter' => $chapter,
                    'category' => $category,
                    'is_billable' => $isBillable,
                    'is_common_tanzania' => true,
                    'keywords' => strtolower($description),
                ]
            );
        }
    }
}