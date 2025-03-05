<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Undangan;

/**
 * @OA\Tag(name="Story", description="API endpoints for managing stories")
 */
class StoryController extends Controller {
    /**
 * @OA\Schema(
 *     schema="Story",
 *     title="Story",
 *     description="Story model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="undangan_id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Wedding Story"),
 *     @OA\Property(property="desc", type="string", example="This is a wedding story"),
 *     @OA\Property(property="image", type="string", example="images/story.jpg"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-02-23T12:34:56Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-02-23T12:34:56Z")
 * )
 */



    /**
     * @OA\Post(
     *     path="/api/undangan/{undangan_id}/story",
     *     summary="Create a new story",
     *     tags={"Story"},
     *     security={{ "bearer": {} }},
     *     @OA\Parameter(name="undangan_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "desc", "image"},
     *                 @OA\Property(property="title", type="string", example="Wedding Story"),
     *                 @OA\Property(property="desc", type="string", example="This is a wedding story"),
     *                 @OA\Property(property="image", type="file", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Story created successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Undangan not found or unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Undangan not found or unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal Server Error")
     *         )
     *     )
     * )
     */
    public function store(Request $request, $undangan_id) {
        $user = Auth::user(); // Ambil user yang sedang login

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $undangan = Undangan::where('id', $undangan_id)->where('user_id', $user->id)->first();
        if (!$undangan) {
            return response()->json(['message' => 'Undangan not found or unauthorized'], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string',
            'desc' => 'required|string',
            'image' => 'required|file|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        // Simpan gambar ke public storage
        $file = $request->file('image');
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('images', $filename, 'public');

        $story = Story::create([
            'undangan_id' => $undangan_id,
            'title' => $validated['title'],
            'desc' => $validated['desc'],
            'image' => $filePath
        ]);

        return response()->json($story, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/undangan/{undangan_id}/story",
     *     summary="Get stories by undangan ID",
     *     tags={"Story"},
     *     @OA\Parameter(name="undangan_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Undangan not found or unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Undangan not found or unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal Server Error")
     *         )
     *     )
     * )
     */
    public function getByUndangan($undangan_id) {

        $story = Story::where('undangan_id', $undangan_id)->get();

        if ($story->isEmpty()) {
            return response()->json(['message' => 'No stories found for this invitation'], 404);
        }

        return response()->json($story);
    }

    /**
     * @OA\Post(
     *     path="/api/undangan/{undangan_id}/story/{story_id}",
     *     summary="Update a story",
     *     tags={"Story"},
     *     @OA\Parameter(name="undangan_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="story_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "desc", "image"},
     *                 @OA\Property(property="title", type="string", example="Wedding Story"),
     *                 @OA\Property(property="desc", type="string", example="This is a wedding story"),
     *                 @OA\Property(property="image", type="file", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Story updated successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Story not found or unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Story not found or unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal Server Error")
     *         )
     *     )
     * )
     */
    public function updateStory(Request $request, $undangan_id, Story $story) {
        $user = Auth::user(); // Ambil user yang sedang login

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Pastikan undangan ada dan milik user yang sedang login
        $undangan = Undangan::where('id', $undangan_id)->where('user_id', $user->id)->first();
        if (!$undangan) {
            return response()->json(['message' => 'Undangan not found or unauthorized'], 404);
        }

        // Pastikan story milik undangan yang sesuai
        if ($story->undangan_id !== $undangan->id) {
            return response()->json(['message' => 'Story not found or unauthorized'], 404);
        }

        // Validasi request
        $validated = $request->validate([
            'title' => 'sometimes|string',
            'desc' => 'sometimes|string',
            'image' => 'sometimes|file|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        // Simpan gambar hanya jika ada file baru
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('images', $filename, 'public');
            $validated['image'] = $filePath;
        }

        // Update hanya field yang dikirim dalam request
        $story->update($validated);

        return response()->json([
            'message' => 'Story updated successfully',
            'story' => $story
        ]);
    }


    /**
     * @OA\Delete(
     *     path="/api/undangan/{undangan_id}/story/{story_id}",
     *     summary="Delete a story",
     *     tags={"Story"},
     *     @OA\Parameter(name="undangan_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="story_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Story deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Story not found or unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Story not found or unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal Server Error")
     *         )
     *     )
     * )
     */
    public function destroy($undangan_id, Story $story) {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $undangan = Undangan::where('id', $undangan_id)->where('user_id', $user->id)->first();
        if (!$undangan) {
            return response()->json(['message' => 'Undangan not found or unauthorized'], 404);
        }

        if ($story->undangan_id !== $undangan->id) {
            return response()->json(['message' => 'Story not found or unauthorized'], 404);
        }

        $story->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
