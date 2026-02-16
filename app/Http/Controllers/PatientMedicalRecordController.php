<?php
// app/Http/Controllers/PatientMedicalRecordController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Appointment;
use App\Models\VitalSign;
use App\Models\LabOrder;
use App\Models\Prescription;
use App\Models\AppointmentNote;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class PatientMedicalRecordController extends Controller
{
    public function show($patientId)
    {
        $patient = User::with(['patients'])->findOrFail($patientId);

        //Ensure the user is a patient
        if ($patient->user_type !== 'patient') {
            abort(404, 'Patient not found');
        }

        // Get all appointments with related data
        $appointments = Appointment::with([
            'doctor',
            'vitalSigns' => function ($q) {
                $q->with('recorded_by')->latest();
            },
            'notes' => function ($q) {
                $q->with('doctor')->latest();
            },
            'consultation.prescriptions',
            'consultation.labOrders'
        ])
            ->where('patient_id', $patientId)
            ->orderBy('scheduled_date', 'desc')
            ->orderBy('scheduled_time', 'desc')
            ->get();

        // Get all vitals across all appointments
        $allVitals = VitalSign::with(['appointment', 'recorded_by'])
            ->where('patient_id', $patientId)
            ->orderBy('recorded_at', 'desc')
            ->get();

        // Get all lab orders
        $labOrders = LabOrder::with(['doctor', 'consultation.appointment'])
            ->where('patient_id', $patientId)
            ->orderBy('ordered_at', 'desc')
            ->get();

        // Get all prescriptions
        $prescriptions = Prescription::with(['doctor', 'consultation.appointment'])
            ->where('patient_id', $patientId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get all notes
        $allNotes = AppointmentNote::with(['doctor', 'appointment'])
            ->whereHas('appointment', function ($q) use ($patientId) {
                $q->where('patient_id', $patientId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate statistics
        $statistics = [
            'total_appointments' => $appointments->count(),
            'completed_appointments' => $appointments->where('status', 'completed')->count(),
            'upcoming_appointments' => $appointments->whereIn('status', ['scheduled', 'confirmed'])
                ->where('scheduled_date', '>=', now())
                ->count(),
            'total_lab_orders' => $labOrders->count(),
            'pending_lab_orders' => $labOrders->where('status', 'pending')->count(),
            'total_prescriptions' => $prescriptions->count(),
            'active_prescriptions' => $prescriptions->where('status', 'active')->count(),
            'last_visit' => $appointments->where('status', 'completed')->first()?->scheduled_date,
            'first_visit' => $appointments->last()?->scheduled_date,
        ];

        // Group vitals by type for charts
        $vitalsChartData = $this->prepareVitalsChartData($allVitals);

        return Inertia::render('Patients/MedicalRecord', [
            'patient' => $patient,
            'appointments' => $appointments,
            'vitals' => $allVitals,
            'labOrders' => $labOrders,
            'prescriptions' => $prescriptions,
            'notes' => $allNotes,
            'statistics' => $statistics,
            'vitalsChartData' => $vitalsChartData,
            'filters' => request()->only(['date_range', 'type']),
        ]);
    }

    public function print($patientId)
    {
        $patient = User::with(['patientProfile'])->findOrFail($patientId);

        if ($patient->user_type !== 'patient') {
            abort(404, 'Patient not found');
        }

        $data = $this->getPatientMedicalData($patientId);

        return Inertia::render('Patients/PrintMedicalRecord', [
            'patient' => $patient,
            'appointments' => $data['appointments'],
            'vitals' => $data['vitals'],
            'labOrders' => $data['labOrders'],
            'prescriptions' => $data['prescriptions'],
            'notes' => $data['notes'],
        ]);
    }

    public function export($patientId, $format = 'pdf')
    {
        // Implementation for PDF/Excel export
        // You can use barryvdh/laravel-dompdf or maatwebsite/excel
    }

    public function timeline($patientId)
    {
        $patient = User::findOrFail($patientId);

        $timeline = collect();

        // Add appointments to timeline
        $appointments = Appointment::with('doctor')
            ->where('patient_id', $patientId)
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'type' => 'appointment',
                    'title' => 'Appointment with Dr. ' . $appointment->doctor->first_name . ' ' . $appointment->doctor->last_name,
                    'description' => $appointment->reason,
                    'date' => $appointment->scheduled_date . ' ' . $appointment->scheduled_time,
                    'status' => $appointment->status,
                    'icon' => 'CalendarIcon',
                    'color' => 'blue'
                ];
            });

        // Add vitals to timeline
        $vitals = VitalSign::with('recorded_by')
            ->where('patient_id', $patientId)
            ->get()
            ->map(function ($vital) {
                return [
                    'id' => $vital->id,
                    'type' => 'vital',
                    'title' => 'Vital Signs Recorded',
                    'description' => "BP: {$vital->blood_pressure_systolic}/{$vital->blood_pressure_diastolic}, HR: {$vital->heart_rate}, Temp: {$vital->temperature}°C",
                    'date' => $vital->recorded_at,
                    'icon' => 'HeartIcon',
                    'color' => 'red'
                ];
            });

        // Add lab orders to timeline
        $labs = LabOrder::with('doctor')
            ->where('patient_id', $patientId)
            ->get()
            ->map(function ($lab) {
                return [
                    'id' => $lab->id,
                    'type' => 'lab',
                    'title' => 'Lab Order: ' . $lab->test_name,
                    'description' => $lab->instructions,
                    'date' => $lab->ordered_at,
                    'status' => $lab->status,
                    'icon' => 'BeakerIcon',
                    'color' => 'green'
                ];
            });

        // Add prescriptions to timeline
        $prescriptions = Prescription::with('doctor')
            ->where('patient_id', $patientId)
            ->get()
            ->map(function ($prescription) {
                return [
                    'id' => $prescription->id,
                    'type' => 'prescription',
                    'title' => 'Prescription: ' . $prescription->medication_name,
                    'description' => "{$prescription->dosage} - {$prescription->frequency} for {$prescription->duration}",
                    'date' => $prescription->start_date,
                    'status' => $prescription->status,
                    'icon' => 'DocumentTextIcon',
                    'color' => 'purple'
                ];
            });

        // Add notes to timeline
        $notes = AppointmentNote::with(['doctor', 'appointment'])
            ->whereHas('appointment', function ($q) use ($patientId) {
                $q->where('patient_id', $patientId);
            })
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->id,
                    'type' => 'note',
                    'title' => 'Clinical Note',
                    'description' => substr($note->content, 0, 100) . '...',
                    'date' => $note->created_at,
                    'icon' => 'DocumentDuplicateIcon',
                    'color' => 'yellow'
                ];
            });

        $timeline = $timeline->concat($appointments)
            ->concat($vitals)
            ->concat($labs)
            ->concat($prescriptions)
            ->concat($notes)
            ->sortByDesc('date')
            ->values();

        return response()->json(['timeline' => $timeline]);
    }

    private function prepareVitalsChartData($vitals)
    {
        $vitals = $vitals->take(20); // Last 20 readings

        return [
            'labels' => $vitals->pluck('recorded_at')->map(function ($date) {
                return Carbon::parse($date)->format('M d, Y');
            })->toArray(),
            'blood_pressure_systolic' => $vitals->pluck('blood_pressure_systolic')->toArray(),
            'blood_pressure_diastolic' => $vitals->pluck('blood_pressure_diastolic')->toArray(),
            'heart_rate' => $vitals->pluck('heart_rate')->toArray(),
            'temperature' => $vitals->pluck('temperature')->toArray(),
            'oxygen_saturation' => $vitals->pluck('oxygen_saturation')->toArray(),
            'weight' => $vitals->pluck('weight')->toArray(),
        ];
    }

    private function getPatientMedicalData($patientId)
    {
        return [
            'appointments' => Appointment::with(['doctor', 'vitalSigns', 'notes'])
                ->where('patient_id', $patientId)
                ->orderBy('scheduled_date', 'desc')
                ->get(),
            'vitals' => VitalSign::with('recorded_by')
                ->where('patient_id', $patientId)
                ->orderBy('recorded_at', 'desc')
                ->get(),
            'labOrders' => LabOrder::with('doctor')
                ->where('patient_id', $patientId)
                ->orderBy('ordered_at', 'desc')
                ->get(),
            'prescriptions' => Prescription::with('doctor')
                ->where('patient_id', $patientId)
                ->orderBy('created_at', 'desc')
                ->get(),
            'notes' => AppointmentNote::with(['doctor', 'appointment'])
                ->whereHas('appointment', function ($q) use ($patientId) {
                    $q->where('patient_id', $patientId);
                })
                ->orderBy('created_at', 'desc')
                ->get(),
        ];
    }
}
