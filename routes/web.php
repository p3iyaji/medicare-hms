<?php

use App\Http\Controllers\ScheduleExceptionController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PatientController;
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

Route::get('deactivated', function () {
    return Inertia::render('Auth/AccountDeactivated');
})->name('deactivated');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return inertia('Dashboard');
    })->name('dashboard');
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/phone', [ProfileController::class, 'phone'])->name('profile.phone.update');
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
    Route::get('/patients', [PatientController::class, 'index'])->name('patients');
    Route::get('/patients/create', [PatientController::class, 'create'])->name('patients.create');
        // Activation/deactivation routes
    Route::put('/patients/{patient}/deactivate', [PatientController::class, 'deactivate'])
        ->name('patients.deactivate');
    Route::put('/patients/{patient}/activate', [PatientController::class, 'activate'])
        ->name('patients.activate');
    Route::post('/patients/store', [PatientController::class, 'store'])->name('patients.store');

    Route::get('/patients/edit/{patient}', [PatientController::class, 'edit'])->name('patients.edit');
    Route::put('/patients/update/{patient}', [PatientController::class, 'update'])->name('patients.update');
    Route::delete('/patients/destroy/{patient}', [PatientController::class, 'destroy'])->name('patients.destroy');
    
    // Doctors (for admins)
    Route::get('doctors', [DoctorController::class, 'index'])->name('doctors');
    Route::get('/doctors/create', [DoctorController::class, 'create'])->name('doctors.create');
    Route::post('/doctors/store', [DoctorController::class, 'store'])->name('doctors.store');
    Route::get('/doctors/edit/{doctor}', [DoctorController::class,'edit'])->name('doctors.edit');
    Route::put('/doctors/update/{doctor}', [DoctorController::class,'update'])->name('doctors.update');
    Route::delete('/doctors/destroy/{doctor}', [DoctorController::class,'destroy'])->name('doctors.destroy');
    Route::put('/doctors/{doctor}/deactivate', [DoctorController::class,'deactivate'])->name('doctors.deactivate');
    Route::put('/doctors/{doctor}/activate', [DoctorController::class,'activate'])->name('doctors.activate');   
    
    // specializations
    Route::get('/specializations', [SpecializationController::class, 'index'])->name('specializations');
    Route::get('/specializations/create', [SpecializationController::class, 'create'])->name('specializations.create');
    Route::post('/specializations/store', [SpecializationController::class, 'store'])->name('specializations.store');  
    Route::get('/specializations/edit/{specialization}', [SpecializationController::class, 'edit'])->name('specializations.edit');
    Route::put('/specializations/update/{specialization}', [SpecializationController::class, 'update'])->name('specializations.update');
    Route::delete('/specializations/destroy/{specialization}', [SpecializationController::class, 'destroy'])->name('specializations.destroy');

    // doctor schedules
    Route::get('/schedules', [DoctorScheduleController::class, 'index'])->name('schedules');
    Route::get('/schedules/create', [DoctorScheduleController::class, 'create'])->name('schedules.create');
    Route::post('/schedules/store', [DoctorScheduleController::class, 'store'])->name('schedules.store');  
    Route::get('/schedules/edit/{schedule}', [DoctorScheduleController::class,'edit'])->name('schedules.edit');
    Route::put('/schedules/update/{schedule}', [DoctorScheduleController::class, 'update'])->name('schedules.update');
    Route::delete('/schedules/destroy/{schedule}', [DoctorScheduleController::class, 'destroy'])->name('schedules.destroy');

    // Schedule Exceptions
    Route::get('/schedule-exceptions', [ScheduleExceptionController::class, 'index'])->name('schedule-exceptions');
    Route::get('/schedule-exceptions/create', [ScheduleExceptionController::class,'create'])->name('schedule-exceptions.create');
    Route::post('/schedule-exceptions/store', [ScheduleExceptionController::class,'store'])->name('schedule-exceptions.store');
    Route::get('/schedule-exceptions/edit/{scheduleException}', [ScheduleExceptionController::class,'edit'])->name('schedule-exceptions.edit');
    
    
    
    Route::get('/settings', function () {
        return inertia('Settings/Index');
    })->name('settings');

    Route::get('/billing', function () {
        return inertia('Billing/Index');
    })->name('billing.index');

    Route::get('/reports', function () {
        return inertia('Reports/Index');
    })->name('reports.index');

    
});

require __DIR__ . '/auth.php';
