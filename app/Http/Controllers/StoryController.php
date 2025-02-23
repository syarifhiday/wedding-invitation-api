<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Undangan;

class StoryController extends Controller {

    public function store(Request $request) {
        $user = Auth::user(); // Ambil user yang sedang login

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'undangan_id' => 'required|exists:undangan,id',
            'title' => 'required|string',
            'desc' => 'required|string',
            'image' => 'required|file|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        // Simpan gambar ke public storage
        $file = $request->file('image');
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('images', $filename, 'public');

        $validated['image'] = $filePath;

        $undangan = Undangan::where('id', $validated['undangan_id'])->where('user_id', $user->id)->first();

        if (!$undangan) {
            return response()->json(['message' => 'Undangan not found or unauthorized'], 404);
        }

        $story = Story::create($validated);
        return response()->json($story, 201);
    }

    public function getByUndangan($undangan_id) {
        $user = Auth::user();
        $undangan = Undangan::where('id', $undangan_id)->where('user_id', $user->id)->first();

        if (!$undangan) {
            return response()->json(['message' => 'Undangan not found or unauthorized'], 404);
        }

        $story = Story::where('undangan_id', $undangan_id)->get();

        if ($story->isEmpty()) {
            return response()->json(['message' => 'No stories found for this invitation'], 404);
        }

        return response()->json($story);
    }

    public function updateStory(Request $request, Story $story) {
        $user = Auth::user(); // Ambil user yang sedang login

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'undangan_id' => 'required|exists:undangan,id',
            'title' => 'sometimes|string',
            'desc' => 'sometimes|string',
            'image' => 'sometimes|file|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        $undangan = Undangan::where('id', $validated['undangan_id'])->where('user_id', $user->id)->first();
        if (!$undangan) {
            return response()->json(['message' => 'Undangan not found or unauthorized'], 404);
        }

        // Pastikan story milik undangan yang sesuai
        if ($story->undangan_id !== $undangan->id) {
            return response()->json(['message' => 'Story not found or unauthorized'], 404);
        }

        // Simpan gambar hanya jika ada file baru
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('images', $filename, 'public');
            $validated['image'] = $filePath;
        }

        $story->update($validated);
        return response()->json($story);
    }


    public function destroy(Story $story) {
        $story->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
