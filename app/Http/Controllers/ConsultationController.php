<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\NoteTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ConsultationController extends Controller
{
    public function show(Appointment $appointment)
    {
        $appointment->load([
            'patient',
            'doctor',
            'notes' => function ($query) {
                $query->visibleForUser(auth()->user())
                    ->latest();
            },
            'statusHistory' => function ($query) {
                $query->latest()->limit(5);
            }
        ]);

        // Load patient's previous appointments
        $previousAppointments = Appointment::where('patient_id', $appointment->patient_id)
            ->where('id', '!=', $appointment->id)
            ->with(['doctor', 'notes'])
            ->orderBy('scheduled_date', 'desc')
            ->limit(10)
            ->get();

        $noteTemplates = NoteTemplate::forDoctor(auth()->id())
            ->orderBy('usage_count', 'desc')
            ->get();

        return Inertia::render('Doctor/Consultation/Show', [
            'appointment' => $appointment,
            'previousAppointments' => $previousAppointments,
            'noteTemplates' => $noteTemplates
        ]);
    }

    public function start(Appointment $appointment)
    {
        if ($appointment->status !== 'in-progress') {
            $appointment->startConsultation();
        }

        return redirect()->route('doctor.consultation.show', $appointment);
    }

    public function update(Request $request, Appointment $appointment)
    {

        $request->validate([
            'doctor_notes' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'prescription' => 'nullable|string',
            'lab_orders' => 'nullable|string',
            'follow_up_instructions' => 'nullable|string',
            'follow_up_date' => 'nullable|date|after:today',
            'status' => 'sometimes|in:completed,rescheduled,follow-up'
        ]);

        $appointment->update([
            'doctor_notes' => $request->doctor_notes,
            'diagnosis' => $request->diagnosis,
            'prescription' => $request->prescription,
            'lab_orders' => $request->lab_orders,
            'follow_up_instructions' => $request->follow_up_instructions,
            'follow_up_date' => $request->follow_up_date
        ]);

        if ($request->status === 'completed') {
            $appointment->endConsultation();
        } elseif ($request->status === 'rescheduled') {
            $appointment->updateStatus('rescheduled', 'Rescheduled during consultation');
        } elseif ($request->status === 'follow-up') {
            $appointment->updateStatus('scheduled', 'Follow-up scheduled');
        }

        return back()->with('success', 'Consultation updated successfully.');
    }


    //Doctors Funcitonality
    public function updateStatus(Request $request, Appointment $appointment)
    {
        if (!in_array(Auth::user()->user_type, ['admin', 'doctor'])) {
            abort(403);
        }
        if (Auth::user()->user_type === 'doctor' && $appointment->doctor_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:scheduled,confirmed,in-progress,completed,cancelled,no-show,rescheduled',
            'notes' => 'nullable|string|max:500'
        ]);

        $appointment->updateStatus($request->status, $request->notes);

        return back()->with('success', 'Appointment status updated successfully');
    }

    public function saveNotes(Request $request, Appointment $appointment)
    {
        if (!in_array(Auth::user()->user_type, ['admin', 'doctor'])) {
            abort(403);
        }
        if (Auth::user()->user_type === 'doctor' && $appointment->doctor_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'notes' => 'required|string|max:2000',
            'is_private' => 'nullable|boolean',
        ]);


        $appointment->notes()->updateOrCreate([
            'appointment_id' => $appointment->id,
            'doctor_id' => Auth::id(),

        ], [
            'content' => $request->notes,
            'is_private' => $request->is_private ?? false,
            'metadata' => [
                'updated_at' => now(),
                'ip' => $request->ip(),
            ]
        ]);

        return back()->with('success', 'Notes saved successfully');
    }

    public function complete(Appointment $appointment)
    {

        $appointment->endConsultation();

        return redirect()->route('appointments')
            ->with('success', 'Consultation completed successfully.');
    }
}
