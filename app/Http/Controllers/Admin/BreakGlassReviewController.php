<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BreakGlassAccess;
use App\Services\BreakGlassService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BreakGlassReviewController extends Controller
{
    public function __construct(private readonly BreakGlassService $breakGlass)
    {
    }

    public function index()
    {
        $this->authorize('review', BreakGlassAccess::class);

        $accesses = BreakGlassAccess::with('user', 'patient')
            ->latest('granted_at')
            ->paginate(25);

        return view('admin.break-glass.index', ['accesses' => $accesses]);
    }

    public function show(BreakGlassAccess $access)
    {
        $this->authorize('review', $access);

        return view('admin.break-glass.show', [
            'access' => $access->load('user', 'patient', 'reviewer'),
        ]);
    }

    public function review(BreakGlassAccess $access, Request $request)
    {
        $this->authorize('review', $access);

        $data = $request->validate([
            'outcome' => ['required', Rule::in(['appropriate', 'inappropriate', 'needs_followup'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->breakGlass->recordReview($access, $request->user(), $data['outcome'], $data['notes'] ?? null);

        return redirect()->route('admin.break-glass.index')->with('status', __('admin.break_glass_reviewed'));
    }
}