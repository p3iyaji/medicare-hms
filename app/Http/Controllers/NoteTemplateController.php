<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NoteTemplate;
use Illuminate\Support\Facades\Validator;

class NoteTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = NoteTemplate::forDoctor(auth()->id());

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('content', 'like', "%{$request->search}%");
            });
        }

        $templates = $query->orderBy('usage_count', 'desc')
            ->orderBy('updated_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return response()->json([
            'templates' => $templates,
            'categories' => $this->getCategories()
        ]);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string|in:' . implode(',', array_keys($this->getCategories())),
            'is_shared' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $template = NoteTemplate::create([
            'doctor_id' => auth()->id(),
            'name' => $request->name,
            'content' => $request->input('content'),
            'category' => $request->category,
            'is_shared' => $request->is_shared ?? false,
            'placeholders' => $this->extractPlaceholders($request->input('content'))
        ]);

        return redirect()->back()->with('success', 'Template created successfully.');
    }

    public function update(Request $request, NoteTemplate $noteTemplate)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'category' => 'sometimes|required|string|in:' . implode(',', array_keys($this->getCategories())),
            'is_shared' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only(['name', 'content', 'category', 'is_shared']);

        if (isset($data['content'])) {
            $data['placeholders'] = $this->extractPlaceholders($data['content']);
        }

        $noteTemplate->update($data);

        return redirect()->back()->with('success', 'Template updated successfully.');
    }

    public function destroy(NoteTemplate $noteTemplate)
    {
        $noteTemplate->delete();

        return redirect()->back()->with('success', 'Template deleted successfully.');
    }

    public function duplicate(NoteTemplate $noteTemplate)
    {
        $newTemplate = $noteTemplate->replicate();
        $newTemplate->name = $noteTemplate->name . ' (Copy)';
        $newTemplate->doctor_id = auth()->id();
        $newTemplate->usage_count = 0;
        $newTemplate->save();

        return response()->json([
            'message' => 'Template duplicated successfully',
            'template' => $newTemplate
        ], 201);
    }

    public function incrementUsage(NoteTemplate $noteTemplate)
    {
        $noteTemplate->incrementUsage();

        return redirect()->back()->with('success', 'Template usage count incremented.');
    }

    private function getCategories()
    {
        return [
            'consultation' => 'Consultation',
            'followup' => 'Follow-up',
            'procedure' => 'Procedure',
            'emergency' => 'Emergency',
            'routine' => 'Routine Check',
            'referral' => 'Referral',
            'discharge' => 'Discharge',
            'other' => 'Other'
        ];
    }

    private function extractPlaceholders($content)
    {
        preg_match_all('/\[(.*?)\]/', $content, $matches);
        return array_unique($matches[1] ?? []);
    }
}
