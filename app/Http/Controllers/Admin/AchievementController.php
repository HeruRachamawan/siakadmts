<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $achievements = \App\Models\Achievement::latest()->get();
        return response()->json(['data' => $achievements]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'student_name' => 'required|string|max:255',
            'level' => 'nullable|string|max:255',
            'year' => 'required|integer',
            'description' => 'nullable|string',
            'status' => 'required|in:published,draft',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240'
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('achievements', 'public');
        }

        $achievement = \App\Models\Achievement::create($validated);
        return response()->json($achievement, 201);
    }

    public function show(string $id)
    {
        $achievement = \App\Models\Achievement::findOrFail($id);
        return response()->json($achievement);
    }

    public function update(Request $request, string $id)
    {
        $achievement = \App\Models\Achievement::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'student_name' => 'required|string|max:255',
            'level' => 'nullable|string|max:255',
            'year' => 'required|integer',
            'description' => 'nullable|string',
            'status' => 'required|in:published,draft',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240'
        ]);

        if ($request->hasFile('image')) {
            if ($achievement->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($achievement->image);
            }
            $validated['image'] = $request->file('image')->store('achievements', 'public');
        }

        $achievement->update($validated);
        return response()->json($achievement);
    }

    public function destroy(string $id)
    {
        $achievement = \App\Models\Achievement::findOrFail($id);
        if ($achievement->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($achievement->image);
        }
        $achievement->delete();
        return response()->json(['message' => 'Achievement deleted']);
    }
}
