<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    public function index()
    {
        $this->authorize('manage', Facility::class);

        return view('admin.facilities.index', [
            'facilities' => Facility::withCount('departments', 'users')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage', Facility::class);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:32', 'unique:facilities,code'],
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:50'],
            'hfr_code' => ['nullable', 'string', 'max:50'],
            'region' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'ward' => ['nullable', 'string', 'max:100'],
            'physical_address' => ['nullable', 'string', 'max:255'],
        ]);

        $facility = Facility::create($data);

        $this->audit->log(AuditAction::Create, $facility, description: 'Facility created', newValues: [
            'code' => $facility->code,
            'name' => $facility->name,
        ], actor: $request->user());

        return redirect()->route('admin.facilities.index')->with('status', __('admin.facility_created'));
    }

    public function update(Facility $facility, Request $request)
    {
        $this->authorize('manage', $facility);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:100'],
            'hfr_code' => ['nullable', 'string', 'max:50'],
            'region' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'ward' => ['nullable', 'string', 'max:100'],
            'physical_address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $before = $facility->getOriginal();
        $facility->fill($data)->save();

        $this->audit->log(AuditAction::Update, $facility, description: 'Facility updated', oldValues: $before, newValues: [
            'changed' => array_keys($facility->getChanges()),
        ], actor: $request->user());

        return redirect()->back()->with('status', __('admin.facility_updated'));
    }
}