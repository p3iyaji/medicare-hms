<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});


Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
            return inertia('Dashboard');
        })->name('dashboard');    
   // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/phone', [ProfileController::class, 'phone'] )->name('profile.phone.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto'])->name('profile.photo.destroy');
    Route::put('/medical-update', [ProfileController::class, 'updateMedical']);
    Route::put('/emergencycontact-update', [ProfileController::class, 'updateEmergencyContact']);
    Route::put('insuranceinfo-update', [ProfileController::class, 'insuranceInfoUpdate']);
    Route::get('/profile-export', [ProfileController::class, 'exportPdf'])->name('profile.export.pdf');
    
    // Profile forms page
    Route::get('/profile/forms', function () {
        return inertia('Profile/Forms');
    })->name('profile.forms');
    
    // Blank form downloads
    Route::get('/profile/forms/download/complete', [ProfileController::class, 'exportBlankForm'])
        ->name('profile.forms.download.complete');
    
    Route::get('/profile/forms/download/compact', [ProfileController::class, 'exportCompactBlankForm'])
        ->name('profile.forms.download.compact');
    // Appointments
    Route::get('/appointments', function () {
        return inertia('Appointments/Index');
    })->name('appointments.index');
    
    Route::get('/appointments/create', function () {
        return inertia('Appointments/Create');
    })->name('appointments.create');
    
    // Medical Records
    Route::get('/medical-records', function () {
        return inertia('MedicalRecords/Index');
    })->name('medical-records.index');
    
    // Patients (for doctors/admins)
    Route::get('/patients', function () {
        return inertia('Patients/Index');
    })->name('patients.index');
    
    // Simple fallback routes
    Route::get('/settings', function () {
        return inertia('Settings/Index');
    })->name('settings');
    
    Route::get('/billing', function () {
        return inertia('Billing/Index');
    })->name('billing.index');
    
    Route::get('/reports', function () {
        return inertia('Reports/Index');
    })->name('reports.index');
    
    Route::get('/doctors', function () {
        return inertia('Doctors/Index');
    })->name('doctors.index');
});

require __DIR__.'/auth.php';
