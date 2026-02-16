<?php

namespace App\Http\Controllers;

use App\Models\VitalSign;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class VitalSignController extends Controller
{
    public function index()
    {
        //
    }

    public function create($id)
    {
        $appointmentId = $id;
        $patientId = Appointment::findOrFail($appointmentId);
        $patient = User::findOrFail($patientId->patient_id);

        $vitalHistory = VitalSign::with('recorded_by')->where('patient_id', $patient->id)
            ->where('appointment_id', $appointmentId)
            ->orderBy('recorded_at', 'desc')
            ->latest()
            ->get();


        return Inertia('Vitals/Create', [
            'appointmentId' => $appointmentId,
            'patient' => $patient,
            'vitalHistory' => $vitalHistory,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'appointment_id' => 'nullable|exists:appointments,id',
            'patient_id' => 'required|exists:users,id',
            'temperature' => 'nullable|numeric|between:32,43',
            'heart_rate' => 'nullable|integer|min:30|max:200',
            'blood_pressure_systolic' => 'nullable|integer|min:50|max:250',
            'blood_pressure_diastolic' => 'nullable|integer|min:30|max:150',
            'respiratory_rate' => 'nullable|integer|min:8|max:40',
            'oxygen_saturation' => 'nullable|numeric|min:70|max:100',
            'weight' => 'nullable|numeric|min:1|max:300',
            'height' => 'nullable|numeric|min:30|max:250',
            'bmi' => 'nullable|numeric',
            'notes' => 'nullable|string|max:1000',
            'recorded_at' => 'nullable|date',
            'data_source' => 'nullable|in:manual,device',
            'device_type' => 'nullable|string|max:100',
            'device_id' => 'nullable|integer',
            'pain_scale' => 'nullable|integer|min:0|max:10',
        ]);

        $vitalSign = VitalSign::create([
            'appointment_id' => $validatedData['appointment_id'] ?? null,
            'patient_id' => $validatedData['patient_id'],
            'recorded_by' => Auth::id(),
            'temperature' => $validatedData['temperature'] ?? null,
            'heart_rate' => $validatedData['heart_rate'] ?? null,
            'blood_pressure_systolic' => $validatedData['blood_pressure_systolic'] ?? null,
            'blood_pressure_diastolic' => $validatedData['blood_pressure_diastolic'] ?? null,
            'respiratory_rate' => $validatedData['respiratory_rate'] ?? null,
            'oxygen_saturation' => $validatedData['oxygen_saturation'] ?? null,
            'weight' => $validatedData['weight'] ?? null,
            'height' => $validatedData['height'] ?? null,
            'bmi' => $validatedData['bmi'] ?? null,
            'notes' => $validatedData['notes'] ?? null,
            'recorded_at' => $validatedData['recorded_at'] ?? now(),
            'data_source' => $validatedData['data_source'] ?? 'manual',
            'device_type' => $validatedData['device_type'] ?? null,
            'device_id' => $validatedData['device_id'] ?? null,
            'pain_scale' => $validatedData['pain_scale'] ?? null,
        ]);

        Log::info('Vital signs recorded:', [
            'vital_sign_id' => $vitalSign->id,
            'patient_id' => $vitalSign->patient_id,
            'appointment_id' => $vitalSign->appointment_id,
        ]);

        return redirect()->route('appointments')->with('success', 'Vital signs recorded successfully.');
    }

    /**
     * Get vital signs for an appointment
     */
    public function getAppointmentVitals($appointmentId)
    {
        $appointment = Appointment::with(['patient', 'vitalSigns' => function ($query) {
            $query->orderBy('recorded_at', 'desc');
        }])->findOrFail($appointmentId);

        return response()->json([
            'success' => true,
            'appointment' => $appointment,
            'vitals' => $appointment->vitalSigns
        ]);
    }

    /**
     * Get vital signs for a patient
     */
    public function getPatientVitals($patientId)
    {
        $vitals = VitalSign::where('patient_id', $patientId)
            ->with(['recordedBy', 'appointment'])
            ->orderBy('recorded_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'vitals' => $vitals
        ]);
    }
}
