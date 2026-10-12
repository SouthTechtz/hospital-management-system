@extends('layouts.app')

@section('title', __('patients.details_title'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $patient->full_name }}</h1>
            <p class="muted small">
                <span class="mono">{{ $patient->mrn }}</span>
                @if ($patient->sex)
                    - {{ __($patient->sex->labelKey()) }}
                @endif
                - {{ $patient->ageDisplay() }}
                @if ($patient->isDeceased())
                    - <span class="tag tag-alert">{{ __('patients.deceased') }}</span>
                @endif
                @if ($patient->merge_status === 'merged')
                    - <span class="tag tag-alert">{{ __('patients.status_merged') }}</span>
                @endif
            </p>
        </div>
        <div class="page-actions">
            @can('update', $patient)
                <a class="btn btn-secondary" href="{{ route('patients.edit', $patient) }}">{{ __('ui.edit') }}</a>
            @endcan
            @can('merge', $patient)
                <a class="btn btn-secondary" href="{{ route('patients.merge.index', $patient) }}">{{ __('patients.merge_into') }}</a>
            @endcan
            @can('breakGlass', $patient)
                <a class="btn btn-secondary" href="{{ route('patients.break-glass.show', $patient) }}">{{ __('patients.view_break_glass') }}</a>
            @endcan
            <a class="btn btn-secondary" href="{{ route('patients.index') }}">{{ __('ui.back') }}</a>
        </div>
    </div>

    @if ($patient->activeAllergies->isNotEmpty())
        @foreach ($patient->activeAllergies as $allergy)
            <div class="alert alert-danger" role="alert">
                {{ __('patients.active_allergy_banner', [
                    'substance' => $allergy->substance,
                    'reaction' => $allergy->reaction ?: __('ui.unknown'),
                ]) }}
            </div>
        @endforeach
    @endif

    <div class="card-grid">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">{{ __('patients.demographics') }}</h2>
            </div>
            <div class="card-body">
                <dl class="detail-list">
                    <div class="detail-row">
                        <dt>{{ __('patients.registered_at') }}</dt>
                        <dd>{{ $patient->registered_at?->format(config('hms.clinical.dt_formats.date')) ?: '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt>{{ __('patients.date_of_birth') }}</dt>
                        <dd>{{ $patient->date_of_birth?->format(config('hms.clinical.dt_formats.date')) ?: '-' }}
                            @if ($patient->birth_date_estimated)
                                <span class="muted small">({{ __('patients.birth_date_estimated') }})</span>
                            @endif
                        </dd>
                    </div>
                    <div class="detail-row">
                        <dt>{{ __('patients.age') }}</dt>
                        <dd>{{ $patient->ageDisplay() }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt>{{ __('patients.marital_status') }}</dt>
                        <dd>{{ $patient->marital_status ?: '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt>{{ __('patients.occupation') }}</dt>
                        <dd>{{ $patient->occupation ?: '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt>{{ __('patients.blood_group') }}</dt>
                        <dd>{{ $patient->blood_group ?: '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt>{{ __('patients.patient_category') }}</dt>
                        <dd>{{ $patient->patient_category ?: '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt>{{ __('patients.payer_type') }}</dt>
                        <dd>{{ $patient->payer_type ? __($patient->payer_type->labelKey()) : '-' }}</dd>
                    </div>
                    @if ($patient->nhif_number)
                        <div class="detail-row">
                            <dt>{{ __('patients.nhif_number') }}</dt>
                            <dd>{{ $patient->nhif_number }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">{{ __('patients.contact') }}</h2>
            </div>
            <div class="card-body">
                <dl class="detail-list">
                    <div class="detail-row">
                        <dt>{{ __('patients.phone') }}</dt>
                        <dd>{{ $patient->phone ?: '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt>{{ __('patients.region') }}</dt>
                        <dd>{{ $patient->region ?: '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt>{{ __('patients.district') }}</dt>
                        <dd>{{ $patient->district ?: '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt>{{ __('patients.ward') }}</dt>
                        <dd>{{ $patient->ward ?: '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt>{{ __('patients.street') }}</dt>
                        <dd>{{ $patient->street ?: '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt>{{ __('patients.nationality') }}</dt>
                        <dd>{{ $patient->nationality ?: '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt>{{ __('patients.religion') }}</dt>
                        <dd>{{ $patient->religion ?: '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('patients.allergies') }}</h2>
            @can('manageAllergies', $patient)
                <a class="btn btn-sm btn-secondary" href="#allergy-form">{{ __('ui.add') }}</a>
            @endcan
        </div>
        <div class="card-body table-wrap">
            @if ($patient->allergies->isEmpty() && ! $patient->no_known_allergies_confirmed)
                <p class="muted">{{ __('patients.allergies_unconfirmed') }}</p>
            @elseif ($patient->allergies->isEmpty())
                <p class="muted">{{ __('patients.allergies_none') }}
                    <span class="tag tag-ok">{{ __('ui.confirmed') }}</span>
                </p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('patients.allergy_substance') }}</th>
                            <th>{{ __('patients.allergy_reaction') }}</th>
                            <th>{{ __('patients.allergy_severity') }}</th>
                            <th>{{ __('ui.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($patient->allergies as $allergy)
                            <tr>
                                <td>{{ $allergy->substance }}</td>
                                <td>{{ $allergy->reaction ?: '-' }}</td>
                                <td>{{ __('patients.allergy_severity_'.$allergy->severity) }}</td>
                                <td>{{ $allergy->status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        @can('manageAllergies', $patient)
            <div class="card-body" id="allergy-form">
                <form method="POST" action="{{ route('patients.allergies.store', $patient) }}" class="stack">
                    @csrf
                    <div class="field-grid">
                        <div class="form-field">
                            <label class="form-label" for="substance">{{ __('patients.allergy_substance') }}</label>
                            <input class="input" id="substance" name="substance" type="text" required>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="reaction">{{ __('patients.allergy_reaction') }}</label>
                            <input class="input" id="reaction" name="reaction" type="text">
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="severity">{{ __('patients.allergy_severity') }}</label>
                            <select class="select" id="severity" name="severity">
                                @foreach (['mild', 'moderate', 'severe', 'life_threatening'] as $severity)
                                    <option value="{{ $severity }}">{{ __('patients.allergy_severity_'.$severity) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">{{ __('ui.save') }}</button>
                    </div>
                </form>
                @if (! $patient->no_known_allergies_confirmed)
                    <form method="POST" action="{{ route('patients.allergies.confirm-none', $patient) }}">
                        @csrf
                        <button class="btn btn-ghost" type="submit">{{ __('patients.confirm_no_known') }}</button>
                    </form>
                @endif
            </div>
        @endcan
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('patients.consents') }}</h2>
        </div>
        <div class="card-body table-wrap">
            @php
                $consentList = $patient->activeConsents();
            @endphp
            @if ($consentList->isEmpty())
                <p class="muted">{{ __('ui.none') }}</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.details') }}</th>
                            <th>{{ __('patients.consent_method') }}</th>
                            <th>{{ __('ui.recorded') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($consentList as $consent)
                            <tr>
                                <td>{{ __($consent->purpose->labelKey()) }}</td>
                                <td>{{ __('patients.consent_method_'.($consent->method ?? 'verbal')) }}</td>
                                <td class="small">{{ $consent->granted_at?->format(config('hms.clinical.dt_formats.date')) ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('patients.next_of_kin') }}</h2>
        </div>
        <div class="card-body table-wrap">
            @if ($patient->nextOfKin->isEmpty())
                <p class="muted">{{ __('ui.none') }}</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('patients.next_of_kin_name') }}</th>
                            <th>{{ __('patients.next_of_kin_relationship') }}</th>
                            <th>{{ __('patients.next_of_kin_phone') }}</th>
                            <th>{{ __('patients.next_of_kin_address') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($patient->nextOfKin as $kin)
                            <tr>
                                <td>{{ $kin->full_name }}{{ $kin->is_primary ? ' *' : '' }}</td>
                                <td>{{ $kin->relationship }}</td>
                                <td>{{ $kin->phone ?: '-' }}</td>
                                <td>{{ $kin->address ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('encounters.title') }}</h2>
        </div>
        <div class="card-body table-wrap">
            @if ($patient->encounters->isEmpty())
                <p class="muted">{{ __('encounters.no_consultation') }}</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('encounters.visit') }}</th>
                            <th>{{ __('encounters.type') }}</th>
                            <th>{{ __('encounters.department') }}</th>
                            <th>{{ __('encounters.clinician') }}</th>
                            <th>{{ __('encounters.started_at') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($patient->encounters as $encounter)
                            <tr>
                                <td class="mono small">{{ $encounter->visit_number }}</td>
                                <td>{{ __($encounter->encounter_type->labelKey()) }}</td>
                                <td>{{ $encounter->department?->name ?: '-' }}</td>
                                <td>{{ $encounter->clinician?->full_name ?: '-' }}</td>
                                <td class="small">{{ $encounter->started_at?->format(config('hms.clinical.dt_formats.datetime')) ?: '-' }}</td>
                                <td class="num">
                                    <a class="btn btn-sm btn-secondary" href="{{ route('encounters.show', $encounter) }}">{{ __('ui.view') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        @can('create', App\Models\Encounter::class)
            <div class="card-body">
                <form method="POST" action="{{ route('encounters.store', $patient) }}" class="row-items">
                    @csrf
                    <input type="hidden" name="patient_id" value="{{ $patient->getKey() }}">
                    <div class="form-field grow">
                        <label class="form-label" for="encounter_type">{{ __('encounters.select_type') }}</label>
                        <select class="select" id="encounter_type" name="encounter_type" required>
                            @foreach (\App\Enums\EncounterType::cases() as $type)
                                <option value="{{ $type->value }}">{{ __($type->labelKey()) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <button class="btn btn-primary" type="submit">{{ __('encounters.open_for_this_patient') }}</button>
                    </div>
                </form>
            </div>
        @endcan
    </div>

    @if ($patient->notes)
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">{{ __('patients.notes') }}</h2>
            </div>
            <div class="card-body">
                <p>{{ $patient->notes }}</p>
            </div>
        </div>
    @endif
@endsection