<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FacilityController extends Controller
{
    public function index()
    {
        $facilities = Facility::latest()->paginate(10);
        return response()->json($facilities);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:published,draft',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('facilities', 'public');
            $imagePath = '/storage/' . $path;
        }

        $facility = Facility::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
            'image' => $imagePath,
        ]);

        return response()->json(['message' => 'Fasilitas berhasil ditambahkan.', 'data' => $facility], 201);
    }

    public function show($id)
    {
        return response()->json(Facility::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $facility = Facility::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:published,draft',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240'
        ]);

        $imagePath = $facility->image;
        if ($request->hasFile('image')) {
            if ($facility->image && str_starts_with($facility->image, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $facility->image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('facilities', 'public');
            $imagePath = '/storage/' . $path;
        }

        $facility->update([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
            'image' => $imagePath,
        ]);

        return response()->json(['message' => 'Fasilitas berhasil diperbarui.', 'data' => $facility]);
    }

    public function destroy($id)
    {
        $facility = Facility::findOrFail($id);
        if ($facility->image && str_starts_with($facility->image, '/storage/')) {
            $oldPath = str_replace('/storage/', '', $facility->image);
            Storage::disk('public')->delete($oldPath);
        }
        $facility->delete();
        return response()->json(['message' => 'Fasilitas berhasil dihapus.']);
    }
}
