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
     *     path="/api/story",
     *     summary="Create a new story",
     *     tags={"Story"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"undangan_id", "title", "desc", "image"},
     *                 @OA\Property(property="undangan_id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Wedding Invitation"),
     *                 @OA\Property(property="desc", type="string", example="Lorem ipsum dolor sit amet, consectetur adipiscing elit."),
     *                 @OA\Property(property="image", type="file", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Story created successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Undangan not found or unauthorized"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/undangan/{undangan_id}/story",
     *     summary="Get stories by undangan ID",
     *     tags={"Story"},
     *     @OA\Parameter(
     *         name="undangan_id",
     *         in="path",
     *         required=true,
     *         description="ID of the undangan",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of stories"
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
     *
     * @OA\Response(
     *     response=404,
     *     description="Undangan not found or unauthorized",
     *     @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Undangan not found or unauthorized")
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/story/{id}",
     *     summary="Update a story",
     *     tags={"Story"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the story",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"undangan_id", "title", "desc", "image"},
     *                 @OA\Property(property="undangan_id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Wedding Invitation"),
     *                 @OA\Property(property="desc", type="string", example="Lorem ipsum dolor sit amet, consectetur adipiscing elit."),
     *                 @OA\Property(property="image", type="file", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Story updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Story not found or unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Story not found or unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object", @OA\Property(property="undangan_id", type="array", @OA\Items(type="string", example="Undangan not found or unauthorized")))
     *         )
     *     )
     * )
     */
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
