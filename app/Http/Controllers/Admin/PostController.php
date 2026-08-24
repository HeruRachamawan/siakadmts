<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('author:id,name')->latest()->paginate(10);
        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:published,draft',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('posts', 'public');
            $imagePath = '/storage/' . $path;
        }

        $post = Post::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . uniqid(),
            'content' => $request->content,
            'status' => $request->status,
            'image' => $imagePath,
            'user_id' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Berita berhasil ditambahkan.', 'data' => $post], 201);
    }

    public function show($id)
    {
        return response()->json(Post::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:published,draft',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240'
        ]);

        $imagePath = $post->image;
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($post->image && str_starts_with($post->image, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $post->image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('posts', 'public');
            $imagePath = '/storage/' . $path;
        }

        $post->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . uniqid(),
            'content' => $request->content,
            'status' => $request->status,
            'image' => $imagePath,
        ]);

        return response()->json(['message' => 'Berita berhasil diperbarui.', 'data' => $post]);
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        if ($post->image && str_starts_with($post->image, '/storage/')) {
            $oldPath = str_replace('/storage/', '', $post->image);
            Storage::disk('public')->delete($oldPath);
        }
        $post->delete();
        return response()->json(['message' => 'Berita berhasil dihapus.']);
    }
}
