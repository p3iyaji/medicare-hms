<?php

namespace App\Http\Controllers;

use App\Models\Specialization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SpecializationController extends Controller
{
    public function index(Request $request)
    {

        $user = Auth::user();
        if (!in_array($user->user_type, ['admin', 'doctor'])) {
            abort(403, 'Unauthorized action.');
        }

        $specializations = Specialization::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $specializations->where('name', 'like', "%{$search}%");
        }

        $specializations = $specializations->orderBy('name', 'asc')->get();

        return inertia::render("Specializations/Index", [
            'specializations' => $specializations,
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        if (!in_array($user->user_type, ['admin', 'doctor'])) {
            abort(403, 'Unauthorized action.');
        }

        return inertia::render("Specializations/Create");
    }

    public function store(Request $request)
    {

        $user = Auth::user();
        if (!in_array($user->user_type, ['admin', 'doctor'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:specializations,name',
            'description' => 'nullable|string',
        ]);

        Specialization::create($validated);

        return redirect()->route('specializations')->with('success', 'Specialization created successfully.');
    }

    public function edit(Request $request, Specialization $specialization)
    {
        $user = Auth::user();
        if (!in_array($user->user_type, ['admin', 'doctor'])) {
            abort(403, 'Unauthorized action.');
        }

        return inertia::render("Specializations/Create", [
            'specialization' => $specialization,
        ]);
    }

    public function update(Request $request, Specialization $specialization)
    {
        $user = Auth::user();
        if (!in_array($user->user_type, ['admin', 'doctor'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:specializations,name,' . $specialization->id,
            'description' => 'nullable|string',
        ]);

        $specialization->update($validated);

        return redirect()->route('specializations')->with('success', 'Specialization updated successfully.');
    }

    public function destroy(Request $request, Specialization $specialization)
    {
        $user = Auth::user();
        if (!in_array($user->user_type, ['admin', 'doctor'])) {
            abort(403, 'Unauthorized action.');
        }

        $specialization->delete();

        return redirect()->route('specializations')->with('success', 'Specialization deleted successfully.');
    }
}
