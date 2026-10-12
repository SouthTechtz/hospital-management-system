<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Facility;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    public function index(?Facility $facility)
    {
        $this->authorize('manage', Department::class);

        $departments = Department::with('facility')
            ->when($facility->exists, fn ($q) => $q->where('facility_id', $facility->getKey()))
            ->paginate(50);

        return view('admin.departments.index', [
            'departments' => $departments,
            'facilities' => Facility::all(),
            'selected' => $facility,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage', Department::class);

        $data = $request->validate([
            'facility_id' => ['required', 'exists:facilities,id'],
            'code' => ['required', 'string', 'max:32'],
            'name' => ['required', 'string', 'max:255'],
            'directorate' => ['required', Rule::in(array_column(\App\Enums\Directorate::cases(), 'value'))],
            'location' => ['nullable', 'string', 'max:255'],
            'is_clinical' => ['sometimes', 'boolean'],
        ]);

        $data['is_clinical'] = $data['is_clinical'] ?? true;

        $department = Department::create($data);

        $this->audit->log(AuditAction::Create, $department, description: 'Department created', newValues: [
            'code' => $department->code,
            'name' => $department->name,
        ], actor: $request->user());

        return redirect()->back()->with('status', __('admin.department_created'));
    }
}