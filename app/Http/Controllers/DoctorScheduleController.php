<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DoctorSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorScheduleController extends Controller
{
    public function index(Request $request)
    {


        $query = DoctorSchedule::with('user')
            ->orderByRaw("
                CASE day_of_week
                    WHEN 'Monday' THEN 1
                    WHEN 'Tuesday' THEN 2
                    WHEN 'Wednesday' THEN 3
                    WHEN 'Thursday' THEN 4
                    WHEN 'Friday' THEN 5
                    WHEN 'Saturday' THEN 6
                    WHEN 'Sunday' THEN 7
                END
            ");
            
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('day_of_week', 'like', "%{$search}%")
                    ->orWhere('start_time', 'like', "%{$search}%")
                    ->orWhere('end_time', 'like', "%{$search}%")
                    ->orWhere('slot_duration', 'like', "%{$search}%")
                    ->orWhere('max_patients_per_slot', 'like', "%{$search}%")
                    ->orWhere('is_active', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'active') {
                $query->where('is_active', true);
            }
        }

        if ($request->filled('doctor')) {
            $doc = $request->doctor;

            $query->whereHas('user', function ($q) use ($doc) {
                $q->where('id', '=', $doc);
            });
        }

        if ($request->filled('day')) {
            $day = $request->day;

            $query->where('day_of_week', '=', $day);
        }

        $schedules = $query->paginate(100)->withQueryString();
        $doctors = User::where('user_type', 'doctor')->where('is_active', true)->get();

        return inertia('Schedules/Index', [
            'schedules' => $schedules,
            'doctors' => $doctors,
        ]);
    }

    public function create()
    {

        $doctors = User::where('user_type', 'doctor')->where('is_active', true)->get();

        return inertia('Schedules/Create', [
            'doctors' => $doctors,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'day_of_week' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'slot_duration' => 'required|integer|min:5',
            'max_patients_per_slot' => 'required|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]);

        DoctorSchedule::create($validated);

        return redirect()->route('schedules')->with('success', 'Doctor schedule created successfully.');
    }

    public function edit(Request $request, DoctorSchedule $schedule)
    {

        $user = Auth::user();
        if (!in_array($user->user_type, ['admin', 'doctor', 'nurse'])) {
            abort(403, 'Unauthorized action.');
        }

        $sdoctors = User::where('user_type', 'doctor')->where('is_active', true)->get();
        return inertia('Schedules/Create', [
            'doctors' => $sdoctors,
            'schedule' => $schedule,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'day_of_week' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'slot_duration' => 'required|integer|min:5',
            'max_patients_per_slot' => 'required|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]);

        DoctorSchedule::findOrFail($id)->update($validated);

        return redirect()->route('schedules')->with('success', 'Doctor schedule updated successfully.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if (!in_array($user->user_type, ['admin', 'doctor'])) {
            abort(403, 'Unauthorized action.');
        }

        $schedule = DoctorSchedule::findOrFail($id);
        $schedule->delete();

        return redirect()->route('schedules')->with('success', 'Doctor schedule deleted successfully');
    }
}
