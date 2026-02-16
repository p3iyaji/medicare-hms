<?php

namespace App\Http\Controllers;

use App\Models\NoteTemplate;
use App\Models\Appointment;
use App\Models\DoctorSchedule;
use App\Models\ScheduleException;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{

    public function index(Request $request)
    {
        /**  @var User $user */
        $user = $request->user();

        if (!in_array($user->user_type, ['admin', 'doctor', 'nurse'])) {
            abort(403, 'Unauthorized action!');
        }

        $version = Cache::store('redis')->get('appointments:version', 1);

        $filters = collect($request->only([
            'search',
            'priority',
            'status',
            'appointment_type',
            'page'
        ]))->sortKeys()->toArray();

        $cacheKey = "appointments:index:user:{$user->user_type}:v{$version}:" . md5(json_encode($filters));

        //$cacheKey = $this->appointmentCacheKey($request, $user->id);

        $appointments = Cache::store('redis')->remember($cacheKey, now()->addMinutes(5), function () use ($request) {
            $query = Appointment::query()->with(['patient:id,first_name,last_name,email,patient_no', 'doctor:id,first_name,last_name,email', 'vitalSigns' => function ($q) {
                $q->with('recorded_by:id,first_name,last_name')->latest();
            }, 'recordedBy:id,first_name,last_name'])
                ->latest();

            if ($request->filled('search')) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('appointment_no', 'like', "%{$search}%")
                        ->orWhere('reason', 'like', "%{$search}%")
                        ->orWhere('symptoms', 'like', "%{$search}%")
                        ->orWhereHas('patient', function ($q2) use ($search) {
                            $q2->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('patient_no', 'like', "%{$search}%");
                        })->orWhereHas('doctor', function ($q3) use ($search) {
                            $q3->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            }

            if ($request->filled('priority')) {
                $query->where('priority', '=', $request->priority);
            }

            if ($request->filled('status')) {
                $query->where('status', '=', $request->status);
            }

            if ($request->filled('appointment_type')) {
                $query->where('appointment_type', '=', $request->appointment_type);
            }
            return $query->paginate(50)->withQueryString();
        });



        return Inertia::render('Appointments/Index', [
            'appointments' => $appointments,
        ]);
    }

    private function appointmentCacheKey(Request $request, $userId)
    {
        return 'appointments:' . md5(json_encode([
            'user' => $userId,
            'search' => $request->search,
            'priority' => $request->priority,
            'status' => $request->status,
            'appointment_type' => $request->appointment_type,
            'page' => $request->page,
        ]));
    }


    public function create()
    {
        $patients = User::where('user_type', 'patient')
            ->select('id', 'patient_no', 'title', 'first_name', 'last_name', 'email')
            ->get();
        $doctors = User::where('user_type', 'doctor')
            ->with('specializations')
            ->select('id', 'title', 'first_name', 'last_name')
            ->get();
        $doctorSchedules = DoctorSchedule::with('user')
            ->where('is_active', true)
            ->get()
            ->groupBy('doctor_id');
        $doctorsException = ScheduleException::where('exception_date', '>', now())
            ->get();
        // Get existing appointments for the next 90 days
        $existingAppointments = Appointment::where('scheduled_date', '>=', now())
            ->where('scheduled_date', '<=', now()->addDays(90))
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->get(['doctor_id', 'scheduled_date', 'scheduled_time', 'estimated_duration']);

        return Inertia('Appointments/Create', [
            'patients' => $patients,
            'doctors' => $doctors,
            'doctorsSchedules' => $doctorSchedules,
            'doctorsException' => $doctorsException,
            'existingAppointments' => $existingAppointments,
        ]);
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'appointment_no' => 'required|string|unique:appointments',
            'patient_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:users,id',
            'appointment_type' => 'required|string',
            'status' => 'required',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required|date_format:H:i',
            'estimated_duration' => 'required|integer|min:15|max:240',
            'reason' => 'required|string|min:10|max:1000',
            'symptoms' => 'nullable|string|max:1000',
            'priority' => 'required|string|in:low,normal,high,urgent',
        ]);

        // Combine date and time
        $dateTime = Carbon::parse($validated['scheduled_date'] . ' ' . $validated['scheduled_time']);

        // Check for conflicting appointments
        $conflictingAppointment = Appointment::where('doctor_id', $validated['doctor_id'])
            ->where('scheduled_date', $validated['scheduled_date'])
            ->where('scheduled_time', $validated['scheduled_time'])
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($conflictingAppointment) {
            return back()->withErrors([
                'scheduled_time' => 'This time slot is already booked. Please choose another time.'
            ]);
        }

        // Create appointment

        $appointment = new Appointment();
        $appointment->appointment_no = $validated['appointment_no'];
        $appointment->patient_id = $validated['patient_id'];
        $appointment->doctor_id = $validated['doctor_id'];
        $appointment->appointment_type = $validated['appointment_type'];
        $appointment->status = $validated['status'];
        $appointment->scheduled_date = $validated['scheduled_date'];
        $appointment->scheduled_time = $validated['scheduled_time'];
        $appointment->estimated_duration = $validated['estimated_duration'];
        $appointment->reason = $validated['reason'];
        $appointment->symptoms = $validated['symptoms'];
        $appointment->priority = $validated['priority'];
        $appointment->recorded_by = Auth::id();
        $appointment->save();

        logger($appointment);

        return redirect()->route('appointments')
            ->with('success', 'Appointment scheduled successfully!');
    }

    public function edit($id)
    {
        $appointment = Appointment::with(['patient', 'doctor'])->findOrFail($id);

        $patients = User::where('user_type', 'patient')
            ->select('id', 'patient_no', 'title', 'first_name', 'last_name', 'email')
            ->get();

        $doctors = User::where('user_type', 'doctor')
            ->with('specializations')
            ->select('id', 'title', 'first_name', 'last_name')
            ->get();

        $doctorSchedules = DoctorSchedule::with('user')
            ->where('is_active', true)
            ->get()
            ->groupBy('doctor_id');

        $doctorsExceptions = ScheduleException::where('exception_date', '>', now())
            ->get();

        // Get existing appointments for the next 90 days (excluding current appointment)
        $existingAppointments = Appointment::where('scheduled_date', '>=', now())
            ->where('scheduled_date', '<=', now()->addDays(90))
            ->where('id', '!=', $id) // Exclude current appointment
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->get(['id', 'doctor_id', 'scheduled_date', 'scheduled_time', 'estimated_duration']);

        return Inertia::render('Appointments/Edit', [
            'appointment' => $appointment,
            'patients' => $patients,
            'doctors' => $doctors,
            'doctorsSchedules' => $doctorSchedules,
            'doctorsException' => $doctorsExceptions,
            'existingAppointments' => $existingAppointments,
        ]);
    }


    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        $validated = $request->validate([
            'patient_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:users,id',
            'appointment_type' => 'required|string',
            'status' => 'required|string|in:scheduled,confirmed,completed,cancelled,no-show,rescheduled',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required|date_format:H:i',
            'estimated_duration' => 'required|integer|min:15|max:240',
            'reason' => 'required|string|min:10|max:1000',
            'symptoms' => 'nullable|string|max:1000',
            'priority' => 'required|string|in:low,medium,high,emergency',
            'actual_start_time' => 'nullable|date',
            'actual_end_time' => 'nullable|date|after:actual_start_time',
        ]);

        // Ensure estimated_duration is an integer
        $validated['estimated_duration'] = (int) $validated['estimated_duration'];

        // Check for conflicting appointments (excluding current appointment)
        if (
            $appointment->scheduled_date !== $validated['scheduled_date'] ||
            $appointment->scheduled_time !== $validated['scheduled_time'] ||
            $appointment->doctor_id !== $validated['doctor_id']
        ) {

            $appointmentDateTime = Carbon::parse($validated['scheduled_date'] . ' ' . $validated['scheduled_time']);
            $appointmentEndDateTime = $appointmentDateTime->copy()->addMinutes($validated['estimated_duration']);

            $conflictingAppointments = Appointment::where('doctor_id', $validated['doctor_id'])
                ->where('scheduled_date', $validated['scheduled_date'])
                ->where('id', '!=', $id)
                ->whereIn('status', ['scheduled', 'confirmed'])
                ->get()
                ->filter(function ($existingAppointment) use ($appointmentDateTime, $appointmentEndDateTime, $validated) {
                    $existingStart = Carbon::parse($existingAppointment->scheduled_date . ' ' . $existingAppointment->scheduled_time);
                    $existingEnd = $existingStart->copy()->addMinutes((int) $existingAppointment->estimated_duration);

                    return $appointmentDateTime->lt($existingEnd) && $appointmentEndDateTime->gt($existingStart);
                });

            if ($conflictingAppointments->count() > 0) {
                return back()->withErrors([
                    'scheduled_time' => 'This time slot overlaps with an existing appointment. Please choose another time.'
                ]);
            }
        }

        // Update appointment
        $appointment->update($validated);

        return redirect()->route('appointments')
            ->with('success', 'Appointment updated successfully!');
    }

    public function doctorAppointments(Request $request)
    {
        $doctorId = Auth::user()->id;

        $query = Appointment::with(['patient', 'consultation'])
            ->where('doctor_id', $doctorId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_date', $request->date);
        }

        $appointments = $query->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->paginate(20)
            ->withQueryString();

        $noteTemplates = NoteTemplate::forDoctor($doctorId)
            ->orderBy('usage_count', 'desc')
            ->get();

        return Inertia::render('Appointments/DoctorAppointments', [
            'appointments' => $appointments,
            'noteTemplates' => $noteTemplates,
            'filters' => $request->only('status', 'date'),
        ]);
    }



    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->notes()->delete();
        $appointment->consultation()->delete();
        $appointment->statusHistory()->delete();
        $appointment->delete();

        return redirect()->route('appointments')
            ->with('success', 'Appointment deleted successfully!');
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

    public function show(Appointment $appointment)
    {
        $appointment->load([
            'patient',
            'doctor',
            'consultation' => function ($query) {
                $query->with(['prescriptions', 'labOrders']);
            },
            'notes' => function ($query) {
                $query->visibleForUser(Auth::user())
                    ->latest();
            },
            'vitalSigns' => function ($query) {
                $query->latest()->limit(5);
            },
            'statusHistory' => function ($query) {
                $query->with('user')->latest()->limit(10);
            }
        ]);

        $noteTemplates = [];
        if (Auth::user()->user_type === 'doctor') {
            $noteTemplates = NoteTemplate::forDoctor(Auth::id())
                ->orderBy('usage_count', 'desc')
                ->get();
        }

        return Inertia::render('Appointments/Show', [
            'appointment' => $appointment,
            'noteTemplates' => $noteTemplates,
        ]);
    }

    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'duration' => 'required|integer|min:15'
        ]);

        $doctorId = $request->doctor_id;
        $date = Carbon::parse($request->date);
        $dayOfWeek = strtolower($date->format('l'));
        $duration = $request->duration;

        // Get doctor's schedule for this day
        $schedule = DoctorSchedule::where('doctor_id', $doctorId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (!$schedule) {
            return response()->json(['slots' => []]);
        }

        // Check for schedule exceptions
        $exception = ScheduleException::where('doctor_id', $doctorId)
            ->whereDate('exception_date', $date)
            ->first();

        if ($exception) {
            if ($exception->is_available) {
                // Custom hours
                $startTime = Carbon::parse($exception->start_time);
                $endTime = Carbon::parse($exception->end_time);
            } else {
                // Doctor not available
                return response()->json(['slots' => []]);
            }
        } else {
            // Regular schedule
            $startTime = Carbon::parse($schedule->start_time);
            $endTime = Carbon::parse($schedule->end_time);

            // Add break handling if needed
            if ($schedule->break_start && $schedule->break_end) {
                $breakStart = Carbon::parse($schedule->break_start);
                $breakEnd = Carbon::parse($schedule->break_end);
            }
        }

        // Get booked appointments for this date
        $bookedSlots = Appointment::where('doctor_id', $doctorId)
            ->whereDate('scheduled_date', $date)
            ->whereIn('status', ['scheduled', 'confirmed', 'in-progress'])
            ->get(['scheduled_time', 'estimated_duration'])
            ->map(function ($appointment) {
                $start = Carbon::parse($appointment->scheduled_time);
                $end = $start->copy()->addMinutes($appointment->estimated_duration);
                return [
                    'start' => $start->format('H:i'),
                    'end' => $end->format('H:i')
                ];
            });

        // Generate available slots
        $slots = [];
        $currentSlot = $startTime->copy();

        while ($currentSlot->copy()->addMinutes($duration)->lte($endTime)) {
            $slotEnd = $currentSlot->copy()->addMinutes($duration);
            $slotTime = $currentSlot->format('H:i');

            // Check if slot is during break
            $isDuringBreak = false;
            if (isset($breakStart) && isset($breakEnd)) {
                $isDuringBreak = $currentSlot->between($breakStart, $breakEnd->subMinute());
            }

            // Check if slot conflicts with booked appointments
            $isBooked = $bookedSlots->contains(function ($booked) use ($currentSlot, $slotEnd) {
                $bookedStart = Carbon::parse($booked['start']);
                $bookedEnd = Carbon::parse($booked['end']);
                return $currentSlot->lt($bookedEnd) && $slotEnd->gt($bookedStart);
            });

            if (!$isDuringBreak && !$isBooked) {
                $slots[] = $slotTime;
            }

            $currentSlot->addMinutes(15); // 15-minute intervals
        }

        return response()->json(['slots' => $slots]);
    }

    /**
     * Reschedule appointment
     */
    public function reschedule(Request $request, Appointment $appointment)
    {
        $request->validate([
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required|date_format:H:i',
            'reason' => 'nullable|string|max:500'
        ]);

        $oldDate = $appointment->scheduled_date;
        $oldTime = $appointment->scheduled_time;

        $appointment->update([
            'scheduled_date' => $request->scheduled_date,
            'scheduled_time' => $request->scheduled_time,
            'status' => 'rescheduled',
            'rescheduled_from' => json_encode([
                'date' => $oldDate,
                'time' => $oldTime
            ])
        ]);

        $appointment->statusHistory()->create([
            'user_id' => Auth::id(),
            'old_status' => $appointment->status,
            'new_status' => 'rescheduled',
            'notes' => "Rescheduled from {$oldDate} {$oldTime}. Reason: {$request->reason}"
        ]);

        return back()->with('success', 'Appointment rescheduled successfully.');
    }

    /**
     * Cancel appointment
     */
    public function cancel(Request $request, Appointment $appointment)
    {

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $appointment->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->reason,
            'cancelled_by' => Auth::id()
        ]);

        $appointment->statusHistory()->create([
            'user_id' => Auth::id(),
            'old_status' => $appointment->status,
            'new_status' => 'cancelled',
            'notes' => "Cancelled. Reason: {$request->reason}"
        ]);

        return back()->with('success', 'Appointment cancelled successfully.');
    }

    /**
     * Get appointment statistics for dashboard
     */
    public function getStats()
    {
        $user = Auth::user();

        $query = Appointment::query();

        if ($user->user_type === 'doctor') {
            $query->where('doctor_id', $user->id);
        }

        $stats = [
            'total_today' => (clone $query)->whereDate('scheduled_date', today())->count(),
            'total_upcoming' => (clone $query)->whereDate('scheduled_date', '>', today())
                ->whereIn('status', ['scheduled', 'confirmed'])->count(),
            'completed_today' => (clone $query)->whereDate('scheduled_date', today())
                ->where('status', 'completed')->count(),
            'cancelled_today' => (clone $query)->whereDate('scheduled_date', today())
                ->where('status', 'cancelled')->count(),
            'no_show_today' => (clone $query)->whereDate('scheduled_date', today())
                ->where('status', 'no-show')->count(),
            'in_progress' => (clone $query)->where('status', 'in-progress')->count(),
        ];

        return response()->json($stats);
    }
}
