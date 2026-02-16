<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ScheduleException;
use Illuminate\Http\Request;

class ScheduleExceptionController extends Controller
{
    public function index(Request $request)
    {
        $query = ScheduleException::orderBy("exception_date")->with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('exception_date', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhere('start_time', 'like', "%{$search}%")
                    ->orWhere('end_time', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'unavailable') {
                $query->where('is_available', false);
            } elseif ($request->status === 'available') {
                $query->where('is_available', true);
            }
        }

        $scheduleExceptions = $query->paginate(100)->withQueryString();

        return inertia('ScheduleExceptions/Index', [
            'scheduleExceptions' => $scheduleExceptions,
        ]);
    }

    public function create()
    {
        $doctors = User::where('user_type', 'doctor')->where('is_active', true)->get();
        return inertia('ScheduleExceptions/Create', [
            'doctors' => $doctors,
        ]);
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'exception_date' => 'required|date',
            'reason' => 'required|string|max:255',
            'is_available' => 'required|boolean',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        ScheduleException::create($validated);

        return redirect()->route('schedule-exceptions')->with('success', 'Schedule exception created successfully.');
    }

    public function edit(ScheduleException $scheduleException)
    {
        $doctors = User::where('user_type', 'doctor')->where('is_active', true)->get();
        return inertia('ScheduleExceptions/Create', [
            'scheduleExp' => $scheduleException,
            'doctors' => $doctors,
        ]);
    }
}
