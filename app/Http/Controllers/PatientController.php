<?php

namespace App\Http\Controllers;

use App\Enums\AuditAction;
use App\Http\Requests\BreakGlassRequest;
use App\Http\Requests\RegisterPatientRequest;
use App\Services\AuditLogger;
use App\Services\BreakGlassService;
use App\Services\DuplicateDetectionService;
use App\Services\PatientService;
use App\Models\Patient;
use App\Models\PatientMerge;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function __construct(
        private readonly PatientService $patients,
        private readonly DuplicateDetectionService $duplicates,
        private readonly BreakGlassService $breakGlass,
        private readonly AuditLogger $audit,
    ) {
    }

    /**
     * Global patient search (top bar) and MPI result table (Part D7.3).
     */
    public function index(Request $request)
    {
        $this->authorize('search', Patient::class);

        $term = trim((string) $request->query('q'));

        $query = Patient::query()->with('facility')->active();

        if ($term !== '') {
            $normalized = Patient::normalizeName($term);

            $query->where(function ($q) use ($term, $normalized) {
                $q->where('first_name', 'like', '%'.$term.'%')
                    ->orWhere('last_name', 'like', '%'.$term.'%')
                    ->orWhere('mrn', 'like', '%'.$term.'%')
                    ->orWhere('full_name_normalized', 'like', '%'.$normalized.'%')
                    ->orWhere('national_id_hash', \App\Support\SensitiveHasher::hash($term))
                    ->orWhere('phone_hash', \App\Support\SensitiveHasher::phone($term))
                    ->orWhere('nhif_hash', \App\Support\SensitiveHasher::hash($term));
            });
        }

        $patients = $query->orderBy('last_name')->orderBy('first_name')->paginate(25)->withQueryString();

        if ($request->expectsJson()) {
            return response()->json([
                'results' => $patients->items(),
                'total' => $patients->total(),
                'term' => $term,
            ]);
        }

        return view('patients.index', [
            'patients' => $patients,
            'term' => $term,
        ]);
    }

    public function show(Patient $patient, Request $request)
    {
        $this->authorize('view', $patient);
        $this->audit->patientView($patient, 'Patient record opened');

        $patient->load([
            'facility', 'nextOfKin', 'allergies',
            'encounters' => fn ($q) => $q->with(['department', 'clinician'])->latest(),
        ]);

        return view('patients.show', [
            'patient' => $patient,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Patient::class);

        return view('patients.create');
    }

    public function store(RegisterPatientRequest $request)
    {
        $this->authorize('create', Patient::class);

        $data = $request->validated();

        if (! $request->boolean('confirm_duplicate')) {
            $candidates = $this->duplicates->find($data, facilityId: $request->user()->facility_id);

            if ($candidates->isNotEmpty()) {
                return back()->withInput()->with('duplicates', $candidates);
            }
        }

        /** @var \App\Models\User $user */
        $user = $request->user();
        $patient = $this->patients->register($data, $user);

        return redirect()->route('patients.show', $patient)
            ->with('status', __('patients.registered', ['mrn' => $patient->mrn]));
    }

    public function edit(Patient $patient)
    {
        $this->authorize('update', $patient);

        return view('patients.edit', [
            'patient' => $patient->load('nextOfKin'),
        ]);
    }

    public function update(RegisterPatientRequest $request, Patient $patient)
    {
        $this->authorize('update', $patient);

        $data = $request->validated();

        if (! $request->boolean('confirm_duplicate')) {
            $candidates = $this->duplicates->find($data, facilityId: $request->user()->facility_id, excludeId: $patient->getKey());

            if ($candidates->isNotEmpty()) {
                return back()->withInput()->with('duplicates', $candidates);
            }
        }

        $this->patients->update($patient, $data, $request->user());

        return redirect()->route('patients.show', $patient)
            ->with('status', __('patients.updated'));
    }

    public function destroy(Patient $patient, Request $request)
    {
        $this->authorize('delete', $patient);

        $this->audit->log(AuditAction::Delete, $patient, $patient, 'Patient record deactivated (soft delete)', $patient->getAttributes(), []);

        $patient->delete();

        return redirect()->route('patients.index')->with('status', __('patients.deleted'));
    }

    public function addAllergy(Patient $patient, Request $request)
    {
        $this->authorize('manageAllergies', $patient);

        $data = $request->validate([
            'substance' => ['required', 'string', 'max:150'],
            'reaction' => ['nullable', 'string', 'max:300'],
            'severity' => ['required', 'in:mild,moderate,severe,life_threatening'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->patients->addAllergy($patient, $data, $request->user());

        return back()->with('status', __('patients.allergy_added'));
    }

    public function confirmNoKnownAllergies(Patient $patient, Request $request)
    {
        $this->authorize('manageAllergies', $patient);

        $this->patients->confirmNoKnownAllergies($patient, $request->user());

        return back()->with('status', __('patients.no_allergy_confirmed'));
    }

    public function mergeCandidates(Patient $patient, Request $request)
    {
        $this->authorize('merge', $patient);

        $q = trim((string) $request->query('q'));
        $candidates = collect();

        if ($q !== '') {
            $normalized = Patient::normalizeName($q);
            $candidates = Patient::query()
                ->active()
                ->whereKeyNot($patient->getKey())
                ->where(function ($query) use ($q, $normalized) {
                    $query->where('mrn', 'like', '%'.$q.'%')
                        ->orWhere('first_name', 'like', '%'.$q.'%')
                        ->orWhere('last_name', 'like', '%'.$q.'%')
                        ->orWhere('full_name_normalized', 'like', '%'.$normalized.'%')
                        ->orWhere('national_id_hash', \App\Support\SensitiveHasher::hash($q))
                        ->orWhere('phone_hash', \App\Support\SensitiveHasher::phone($q))
                        ->orWhere('nhif_hash', \App\Support\SensitiveHasher::hash($q));
                })
                ->orderBy('last_name')
                ->limit(25)
                ->get();
        }

        return view('patients.merge', [
            'patient' => $patient,
            'candidates' => $candidates,
            'q' => $q,
            'mergeHistory' => PatientMerge::with(['target'])
                ->where('source_patient_id', $patient->getKey())
                ->latest()
                ->get(),
        ]);
    }

    public function merge(Patient $patient, Request $request)
    {
        $this->authorize('merge', $patient);

        $data = $request->validate([
            'target_patient_id' => ['required', 'exists:patients,id'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $target = Patient::findOrFail($data['target_patient_id']);
        $this->authorize('merge', $target);

        $this->patients->merge($patient, $target, $data['reason'], $request->user());

        return redirect()->route('patients.show', $target)
            ->with('status', __('patients.merged'));
    }

    public function unmerge(Patient $patient, PatientMerge $merge, Request $request)
    {
        $this->authorize('merge', $patient);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $this->patients->unmerge($merge, $data['reason'], $request->user());

        return redirect()->route('patients.show', $patient)
            ->with('status', __('patients.unmerged'));
    }

    public function showBreakGlass(Patient $patient, Request $request)
    {
        $this->authorize('breakGlass', $patient);

        return view('patients.break-glass', [
            'patient' => $patient,
            'currentAccess' => $this->breakGlass->clientBreakGlass($request->user(), $patient),
        ]);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function breakGlass(BreakGlassRequest $request)
    {
        $this->authorize('breakGlass', Patient::class);

        $patient = Patient::findOrFail($request->input('patient_id'));

        $this->breakGlass->activate(
            $request->user(),
            $patient,
            $request->input('reason_code'),
            $request->input('reason_text'),
        );

        return redirect()->route('patients.show', $patient)
            ->with('status', __('break_glass.granted'));
    }
}