<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Models\InboundApiKey;
use App\Models\OutboundCredential;
use App\Services\AuditLogger;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Credential vault (Part B):
 *  - outbound credentials: secrets used to call national systems.
 *  - inbound API keys: keys issued to systems calling the HMS.
 * Only ICT administrators may edit; secrets are never displayed after saving.
 */
class CredentialController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly SettingsService $settings,
    ) {
    }

    public function index()
    {
        $this->authorize('manage', OutboundCredential::class);

        return view('admin.credentials.index', [
            'outbound' => OutboundCredential::orderBy('provider')->get(),
            'inbound' => InboundApiKey::with('facility')->latest()->get(),
        ]);
    }

    public function storeOutbound(Request $request)
    {
        $this->authorize('manage', OutboundCredential::class);

        $data = $request->validate([
            'provider' => ['required', 'string', 'max:60', Rule::in(['nhif', 'dhis2', 'moh', 'gepg', 'hfr', 'nida', 'hie'])],
            'label' => ['required', 'string', 'max:150'],
            'environment' => ['required', Rule::in(['sandbox', 'production'])],
            'base_url' => ['nullable', 'url'],
            'username' => ['nullable', 'string'],
            'secret' => ['nullable', 'string'],
            'extra' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (OutboundCredential::where('provider', $data['provider'])->where('environment', $data['environment'])->exists()) {
            return back()->withErrors(['provider' => __('admin.credential_exists')])->withInput();
        }

        $secretAllowed = $request->user()->isIctAdministrator()
            || $request->user()->hasPermission('credentials.manage_secret');

        $credential = OutboundCredential::create([
            ...$data,
            'verification_status' => 'VERIFY_WITH_PROVIDER',
            'updated_by' => $request->user()->getKey(),
            'secret' => $secretAllowed ? ($data['secret'] ?? null) : null,
            'extra' => $secretAllowed ? ($data['extra'] ?? null) : null,
        ]);

        $this->audit->log(AuditAction::KeyOperation, $credential, description: 'Outbound credential added', oldValues: [], newValues: [
            'provider' => $data['provider'],
            'environment' => $data['environment'],
        ], actor: $request->user());

        return redirect()->route('admin.credentials.index')->with('status', __('admin.credential_saved'));
    }

    public function toggleIntegration(Request $request, string $key)
    {
        $this->authorize('manage', OutboundCredential::class);

        $data = $request->validate(['value' => ['required', Rule::in(['mock', 'live'])]], );

        $this->settings->set('integration.mode.'.$key, $data['value'], 'integrations', 'string', 'confirmed', $request->user()?->getKey());

        $this->audit->log(AuditAction::KeyOperation, description: 'Integration mode changed', newValues: [
            'integration' => $key,
            'mode' => $data['value'],
        ], actor: $request->user());

        return redirect()->route('admin.credentials.index')->with('status', __('admin.integration_mode_updated'));
    }

    public function storeInbound(Request $request)
    {
        $this->authorize('manage', InboundApiKey::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'scopes' => ['required', 'array', 'min:1'],
            'scopes.*' => ['string', 'max:60'],
            'expires_at' => ['nullable', 'date', 'after:today'],
            'rate_limit_per_minute' => ['nullable', 'integer', 'between:1,100000'],
            'facility_id' => ['nullable', 'exists:facilities,id'],
        ]);

        $rawKey = 'hms_'.bin2hex(random_bytes(16));
        $key = InboundApiKey::create([
            'name' => $data['name'],
            'key_prefix' => substr($rawKey, 0, 8),
            'key_hash' => hash('sha256', $rawKey),
            'scopes' => $data['scopes'],
            'rate_limit_per_minute' => $data['rate_limit_per_minute'] ?? 60,
            'expires_at' => $data['expires_at'] ?? null,
            'facility_id' => $data['facility_id'] ?? null,
            'created_by' => $request->user()->getKey(),
        ]);

        $this->audit->log(AuditAction::KeyOperation, $key, description: 'Inbound API key issued (shown once)', newValues: [
            'name' => $data['name'],
            'scopes' => $data['scopes'],
        ], actor: $request->user());

        return redirect()->route('admin.credentials.index')
            ->with('apiKeyOnce', $rawKey)
            ->with('status', __('admin.api_key_created'));
    }

    public function revokeInbound(InboundApiKey $key, Request $request)
    {
        $this->authorize('manage', $key);

        $key->forceFill(['revoked_at' => now(), 'is_active' => false])->save();

        $this->audit->log(AuditAction::KeyOperation, $key, description: 'Inbound API key revoked', actor: $request->user());

        return redirect()->back()->with('status', __('admin.api_key_revoked'));
    }
}