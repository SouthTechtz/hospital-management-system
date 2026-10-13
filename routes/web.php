<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BreakGlassReviewController;
use App\Http\Controllers\Admin\CredentialController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\MfaController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\EncounterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\LookupController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\TriageController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('home') : redirect()->route('login'));

// ---------------------------------------------------------------------------
// Public sign-in
// ---------------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::post('logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Language switch is available at the sign-in screen as well.
Route::get('locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// ---------------------------------------------------------------------------
// Post-authentication gates: compulsory password change and TOTP MFA
// ---------------------------------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::get('password/change', [PasswordController::class, 'showChangeForm'])->name('password.change');
    Route::post('password/change', [PasswordController::class, 'change'])->name('password.change.attempt');

    // MFA removed - simplified auth
});

// ---------------------------------------------------------------------------
// Application screens
// ---------------------------------------------------------------------------
Route::middleware(['auth', 'password.changed'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/home/ajax', [HomeController::class, 'ajaxStats'])->name('home.ajax');

    // Patients and the Master Patient Index (Part D7.3/4)
    Route::get('patients', [PatientController::class, 'index'])->name('patients.index');
    Route::get('patients/create', [PatientController::class, 'create'])->name('patients.create');
    Route::post('patients', [PatientController::class, 'store'])->name('patients.store');
    Route::get('patients/{patient}', [PatientController::class, 'show'])->name('patients.show');
    Route::get('patients/{patient}/edit', [PatientController::class, 'edit'])->name('patients.edit');
    Route::patch('patients/{patient}', [PatientController::class, 'update'])->name('patients.update');
    Route::delete('patients/{patient}', [PatientController::class, 'destroy'])->name('patients.destroy');

    Route::post('patients/{patient}/allergies', [PatientController::class, 'addAllergy'])->name('patients.allergies.store');
    Route::post('patients/{patient}/allergies/confirm-none', [PatientController::class, 'confirmNoKnownAllergies'])->name('patients.allergies.confirm-none');

    Route::get('patients/{patient}/merge', [PatientController::class, 'mergeCandidates'])->name('patients.merge.index');
    Route::post('patients/{patient}/merge', [PatientController::class, 'merge'])->name('patients.merge.store');
    Route::post('patients/{patient}/merges/{merge}/unmerge', [PatientController::class, 'unmerge'])->name('patients.merge.unmerge');

    Route::get('patients/{patient}/break-glass', [PatientController::class, 'showBreakGlass'])->name('patients.break-glass.show');
    Route::post('patients/break-glass', [PatientController::class, 'breakGlass'])->name('patients.break-glass.store');

    // Encounters
    Route::post('patients/{patient}/encounters', [EncounterController::class, 'store'])->name('encounters.store');
    Route::get('encounters/{encounter}', [EncounterController::class, 'show'])->name('encounters.show');
    Route::post('encounters/{encounter}/close', [EncounterController::class, 'close'])->name('encounters.close');

    // Triage
    Route::get('triage', [TriageController::class, 'index'])->name('triage.index');
    Route::get('triage/{encounter}', [TriageController::class, 'create'])->name('triage.form');
    Route::post('triage', [TriageController::class, 'store'])->name('triage.store');

    // OPD consultation
    Route::get('encounters/{encounter}/consultation', [ConsultationController::class, 'show'])->name('consultations.show');
    Route::post('encounters/{encounter}/consultation', [ConsultationController::class, 'store'])->name('consultations.store');

    Route::get('lookup/icd10', [LookupController::class, 'icd10'])->name('lookup.icd10');
    Route::get('lookup/drugs', [LookupController::class, 'drugs'])->name('lookup.drugs');

    // Cash and exemption billing
    Route::get('patients/{patient}/billing', [BillingController::class, 'patientBilling'])->name('billing.patient');
    Route::post('patients/{patient}/billing/invoices', [BillingController::class, 'openInvoice'])->name('billing.invoice.open');
    Route::post('invoices/{invoice}/items', [BillingController::class, 'addItem'])->name('billing.item.add');
    Route::post('invoices/{invoice}/payments', [BillingController::class, 'recordPayment'])->name('billing.payment.record');
    Route::post('payments/{payment}/void', [BillingController::class, 'voidPayment'])->name('billing.payment.void');
    Route::post('invoices/{invoice}/void', [BillingController::class, 'voidInvoice'])->name('billing.invoice.void');

    // -----------------------------------------------------------------------
    // System administration (Part D7.16)
    // -----------------------------------------------------------------------
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
        Route::post('users/{user}/reactivate', [UserController::class, 'reactivate'])->name('users.reactivate');

        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show');
        Route::put('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions.update');

        Route::get('audit', [AuditLogController::class, 'index'])->name('audit.index');
        Route::get('audit/export', [AuditLogController::class, 'export'])->name('audit.export');

        Route::get('break-glass', [BreakGlassReviewController::class, 'index'])->name('break-glass.index');
        Route::get('break-glass/{access}', [BreakGlassReviewController::class, 'show'])->name('break-glass.show');
        Route::post('break-glass/{access}/review', [BreakGlassReviewController::class, 'review'])->name('break-glass.review');

        Route::get('facilities', [FacilityController::class, 'index'])->name('facilities.index');
        Route::post('facilities', [FacilityController::class, 'store'])->name('facilities.store');
        Route::put('facilities/{facility}', [FacilityController::class, 'update'])->name('facilities.update');

        Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::get('departments/{facility}', [DepartmentController::class, 'index'])->name('departments.byFacility');
        Route::post('departments', [DepartmentController::class, 'store'])->name('departments.store');

        Route::get('credentials', [CredentialController::class, 'index'])->name('credentials.index');
        Route::post('credentials/outbound', [CredentialController::class, 'storeOutbound'])->name('credentials.outbound.store');
        Route::post('credentials/integration/{key}', [CredentialController::class, 'toggleIntegration'])->name('credentials.integration.toggle');
        Route::post('credentials/inbound', [CredentialController::class, 'storeInbound'])->name('credentials.inbound.store');
        Route::post('credentials/inbound/{key}/revoke', [CredentialController::class, 'revokeInbound'])->name('credentials.inbound.revoke');
    });
});