<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index()
    {
        $galleries = Gallery::latest()->paginate(10);
        return response()->json($galleries);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // Required for gallery
            'description' => 'nullable|string'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('galleries', 'public');
            $imagePath = '/storage/' . $path;
        }

        $gallery = Gallery::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imagePath,
        ]);

        return response()->json(['message' => 'Foto galeri berhasil ditambahkan.', 'data' => $gallery], 201);
    }

    public function show($id)
    {
        return response()->json(Gallery::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $gallery = Gallery::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'description' => 'nullable|string'
        ]);

        $imagePath = $gallery->image;
        if ($request->hasFile('image')) {
            if ($gallery->image && str_starts_with($gallery->image, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $gallery->image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('galleries', 'public');
            $imagePath = '/storage/' . $path;
        }

        $gallery->update([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imagePath,
        ]);

        return response()->json(['message' => 'Foto galeri berhasil diperbarui.', 'data' => $gallery]);
    }

    public function destroy($id)
    {
        $gallery = Gallery::findOrFail($id);
        if ($gallery->image && str_starts_with($gallery->image, '/storage/')) {
            $oldPath = str_replace('/storage/', '', $gallery->image);
            Storage::disk('public')->delete($oldPath);
        }
        $gallery->delete();
        return response()->json(['message' => 'Foto galeri berhasil dihapus.']);
    }
}
