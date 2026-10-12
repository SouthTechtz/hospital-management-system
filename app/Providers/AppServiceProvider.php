<?php

namespace App\Providers;

use App\Services\AuditLogger;
use App\Services\BillingService;
use App\Services\BreakGlassService;
use App\Services\ConsultationService;
use App\Services\DuplicateDetectionService;
use App\Services\EncounterService;
use App\Services\MrnGenerator;
use App\Services\OrderService;
use App\Services\PatientAccessService;
use App\Services\PatientService;
use App\Services\SequenceService;
use App\Services\SettingsService;
use App\Services\TotpService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SequenceService::class);
        $this->app->singleton(MrnGenerator::class);
        $this->app->singleton(PatientAccessService::class);
        $this->app->singleton(BreakGlassService::class);
        $this->app->singleton(AuditLogger::class);
        $this->app->singleton(TotpService::class);
        $this->app->singleton(SettingsService::class);
        $this->app->bind(PatientService::class);
        $this->app->bind(EncounterService::class);
        $this->app->bind(ConsultationService::class);
        $this->app->bind(OrderService::class);
        $this->app->bind(BillingService::class);
        $this->app->bind(DuplicateDetectionService::class);

        // Per-request CSP nonce so inline scripts are not needed (Part B).
        $this->app->singleton('hms.csp.nonce', fn () => base64_encode(random_bytes(16)));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::share('cspNonce', $this->app->make('hms.csp.nonce'));
    }
}