<?php

namespace App\Http\Controllers;

use App\Models\Drug;
use App\Models\Icd10Code;
use Illuminate\Http\Request;

/**
 * Server-side lookup endpoints for the OPD consultation page. Pure data, no
 * generated or "smart" suggestions (Part D2).
 */
class LookupController extends Controller
{
    public function icd10(Request $request)
    {
        $term = trim((string) $request->query('q'));

        if ($term === '' || mb_strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $results = Icd10Code::search($term)->limit(30)->get()
            ->map(fn (Icd10Code $code) => [
                'id' => $code->getKey(),
                'code' => $code->code,
                'label' => $code->code.' - '.$code->description,
                'description' => $code->description,
            ]);

        return response()->json(['results' => $results]);
    }

    public function drugs(Request $request)
    {
        $term = trim((string) $request->query('q'));

        if ($term === '' || mb_strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $results = Drug::where('is_active', true)
            ->where(function ($q) use ($term) {
                $q->where('generic_name', 'like', '%'.$term.'%')
                    ->orWhere('brand_name', 'like', '%'.$term.'%')
                    ->orWhere('code', 'like', '%'.$term.'%');
            })
            ->limit(30)
            ->get()
            ->map(fn (Drug $drug) => [
                'id' => $drug->getKey(),
                'name' => $drug->displayName(),
                'form' => $drug->form,
                'unit' => $drug->unit,
                'is_controlled' => $drug->is_controlled,
            ]);

        return response()->json(['results' => $results]);
    }
}