<?php

use App\Http\Controllers\PatientMedicalRecordController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ScheduleExceptionController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\VitalSignController;
use App\Http\Controllers\NoteTemplateController;
use App\Http\Controllers\ConsultationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});
// Route::get('/', function () {
//     dd(app()->getBindings());
// });

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
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.delete');

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

    // Auto refresh patients with redis
    Route::get('/patients/version', function () {
        return response()->json([
            'version' => Cache::store('redis')->get('patients:version', 1),
        ]);
    });

    // Doctors (for admins)
    Route::get('doctors', [DoctorController::class, 'index'])->name('doctors');
    Route::get('/doctors/create', [DoctorController::class, 'create'])->name('doctors.create');
    Route::post('/doctors/store', [DoctorController::class, 'store'])->name('doctors.store');
    Route::get('/doctors/edit/{doctor}', [DoctorController::class, 'edit'])->name('doctors.edit');
    Route::put('/doctors/update/{doctor}', [DoctorController::class, 'update'])->name('doctors.update');
    Route::delete('/doctors/destroy/{doctor}', [DoctorController::class, 'destroy'])->name('doctors.destroy');
    Route::put('/doctors/{doctor}/deactivate', [DoctorController::class, 'deactivate'])->name('doctors.deactivate');
    Route::put('/doctors/{doctor}/activate', [DoctorController::class, 'activate'])->name('doctors.activate');

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
    Route::get('/schedules/edit/{schedule}', [DoctorScheduleController::class, 'edit'])->name('schedules.edit');
    Route::put('/schedules/update/{schedule}', [DoctorScheduleController::class, 'update'])->name('schedules.update');
    Route::delete('/schedules/destroy/{schedule}', [DoctorScheduleController::class, 'destroy'])->name('schedules.destroy');

    // Schedule Exceptions
    Route::get('/schedule-exceptions', [ScheduleExceptionController::class, 'index'])->name('schedule-exceptions');
    Route::get('/schedule-exceptions/create', [ScheduleExceptionController::class, 'create'])->name('schedule-exceptions.create');
    Route::post('/schedule-exceptions/store', [ScheduleExceptionController::class, 'store'])->name('schedule-exceptions.store');
    Route::get('/schedule-exceptions/edit/{scheduleException}', [ScheduleExceptionController::class, 'edit'])->name('schedule-exceptions.edit');


    // Appointments
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments');
    Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments/store', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/edit/{id}', [AppointmentController::class, 'edit'])->name('appointments.edit');
    Route::put('/appointments/update/{id}', [AppointmentController::class, 'update'])->name('appointments.update');
    Route::delete('/appointments/destroy/{id}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
    Route::get('/appointments/{appointment}/vitals', [AppointmentController::class, 'vitals'])
        ->name('appointments.vitals');

    // Auto refresh appointments with redis
    Route::get('/appointments/version', function () {
        return response()->json([
            'version' => Cache::store('redis')->get('appointments:version', 1),
        ]);
    })->name('appointments.version');

    // View single appointment
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');

    // Doctor's appointments view
    Route::get('/doctor/appointments', [AppointmentController::class, 'doctorAppointments'])->name('doctor.appointments');

    // NEW: Appointment actions
    Route::put('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])
        ->name('appointments.status.update');
    Route::post('/appointments/{appointment}/notes', [AppointmentController::class, 'saveNotes'])
        ->name('appointments.notes.save');
    Route::post('/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])
        ->name('appointments.reschedule');
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])
        ->name('appointments.cancel');

    // NEW: Available slots
    Route::get('/appointments/available-slots', [AppointmentController::class, 'getAvailableSlots'])
        ->name('appointments.available-slots');

    // NEW: Dashboard stats
    Route::get('/appointments/stats', [AppointmentController::class, 'getStats'])
        ->name('appointments.stats');



    // Note Templates (Doctors only)
    Route::middleware(['role:doctor'])->prefix('doctor/note-templates')->name('doctor.note-templates.')->group(function () {
        Route::get('/', [NoteTemplateController::class, 'index'])->name('index');
        Route::post('/', [NoteTemplateController::class, 'store'])->name('store');
        Route::put('/{noteTemplate}', [NoteTemplateController::class, 'update'])->name('update');
        Route::delete('/{noteTemplate}', [NoteTemplateController::class, 'destroy'])->name('destroy');
        Route::post('/{noteTemplate}/duplicate', [NoteTemplateController::class, 'duplicate'])->name('duplicate');
        Route::post('/{noteTemplate}/increment-usage', [NoteTemplateController::class, 'incrementUsage'])->name('increment-usage');
    });

    // Vital Signs
    Route::get('/vitals/create/{id}', [VitalSignController::class, 'create'])->name('vitals.create');
    Route::post('/vitals/store', [VitalSignController::class, 'store'])->name('vitals.store');

    // Get vital signs for appointment
    Route::get('/appointments/{appointment}/vitals', [VitalSignController::class, 'getAppointmentVitals'])->name('appointments.vitals');

    // Get vital signs for patient
    Route::get('/patients/{patient}/vitals', [VitalSignController::class, 'getPatientVitals'])->name('patients.vitals');

    Route::get('/doctor/appointments', [AppointmentController::class, 'doctorAppointments'])->name('doctor.appointments');

    // Appointment Status & Notes
    Route::put('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])
        ->name('appointments.status.update');
    Route::post('/appointments/{appointment}/notes', [AppointmentController::class, 'saveNotes'])
        ->name('appointments.notes.save');

    // Note Templates
    Route::get('/note-templates', [NoteTemplateController::class, 'index'])->name('note-templates');
    Route::post('/note-templates/store', [NoteTemplateController::class, 'store'])->name('note-templates.store');
    Route::put('/note-templates/{noteTemplate}', [NoteTemplateController::class, 'update'])->name('note-templates.update');
    Route::delete('/note-templates/{noteTemplate}', [NoteTemplateController::class, 'destroy'])->name('note-templates.destroy');
    Route::post('/note-templates/{noteTemplate}/duplicate', [NoteTemplateController::class, 'duplicate'])->name('note-templates.duplicate');
    Route::post('/note-templates/{noteTemplate}/increment-usage', [NoteTemplateController::class, 'incrementUsage'])->name('note-templates.increment-usage');


    // Consultation (Doctors only)
    Route::get('/consultation/{appointment}', [ConsultationController::class, 'show'])->name('consultation.show');
    Route::post('/{appointment}/start', [ConsultationController::class, 'start'])->name('start');
    Route::put('/consultation/{appointment}', [ConsultationController::class, 'update'])->name('consultation.update');
    Route::post('/consultation/{appointment}/complete', [ConsultationController::class, 'complete'])->name('consultation.complete');

    // Prescriptions
    Route::post('/{consultation}/prescriptions', [ConsultationController::class, 'addPrescription'])->name('prescriptions.store');
    Route::put('/prescriptions/{prescription}', [ConsultationController::class, 'updatePrescription'])->name('prescriptions.update');
    Route::delete('/prescriptions/{prescription}', [ConsultationController::class, 'deletePrescription'])->name('prescriptions.destroy');

    // Lab Orders
    Route::post('/{consultation}/lab-orders', [ConsultationController::class, 'addLabOrder'])->name('lab-orders.store');
    Route::put('/lab-orders/{labOrder}', [ConsultationController::class, 'updateLabOrder'])->name('lab-orders.update');
    Route::delete('/lab-orders/{labOrder}', [ConsultationController::class, 'deleteLabOrder'])->name('lab-orders.destroy');


    // Patient Medical Record
    Route::get('/patients/{patient}/medical-record', [PatientMedicalRecordController::class, 'show'])->name('patients.medical-record');
    Route::get('/patients/{patient}/medical-record/print', [PatientMedicalRecordController::class, 'print'])->name('patients.medical-record.print');
    Route::get('/patients/{patient}/medical-record/export', [PatientMedicalRecordController::class, 'export'])->name('patients.medical-record.export');
    Route::get('/patients/{patient}/medical-record/timeline', [PatientMedicalRecordController::class, 'timeline'])->name('patients.medical-record.timeline');


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
