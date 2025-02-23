<?php

namespace App\Http\Controllers;

use App\Models\Galery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Undangan;

/**
 * @OA\Tag(name="Galery", description="API endpoints for managing galery")
 */
class GaleryController extends Controller {

    /**
     * @OA\Post(
     *     path="/api/galery",
     *     summary="Create a new galery",
     *     security={{"bearerAuth":{}}},
     *     tags={"Galery"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"undangan_id", "image"},
     *                 @OA\Property(property="undangan_id", type="integer", example=1),
     *                 @OA\Property(property="image", type="file", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Galery created successfully"),
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
            'image' => 'required|file|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        $file = $request->file('image');
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('images', $filename, 'public');

        $validated['image'] = $filePath;

        $undangan = Undangan::where('id', $validated['undangan_id'])->where('user_id', $user->id)->first();

        if (!$undangan) {
            return response()->json(['message' => 'Undangan not found or unauthorized'], 404);
        }

        $galery = Galery::create($validated);
        return response()->json($galery, 201);
    }

   /**
     * @OA\Get(
     *     path="/api/undangan/{undangan_id}/galery",
     *     summary="Get galery by undangan ID",
     *     tags={"Galery"},
     *     @OA\Parameter(name="undangan_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Galery retrieved successfully"),
     *     @OA\Response(response=404, description="No galery found for this invitation"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function getByUndangan($undangan_id) {

        $galery = Galery::where('undangan_id', $undangan_id)->get();

        if ($galery->isEmpty()) {
            return response()->json(['message' => 'No galery found for this invitation'], 404);
        }

        return response()->json($galery);
    }

    /**
     * @OA\Post(
     *     path="/api/galery/{galery}",
     *     summary="Update an existing galery",
     *     security={{"bearerAuth":{}}},
     *     tags={"Galery"},
     *     @OA\Parameter(name="galery", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"undangan_id", "image"},
     *                 @OA\Property(property="undangan_id", type="integer", example=1),
     *                 @OA\Property(property="image", type="file", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Galery updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Undangan not found or unauthorized"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function updateGalery(Request $request, Galery $galery) {
        $user = Auth::user(); // Ambil user yang sedang login

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'undangan_id' => 'required|exists:undangan,id',
            'image' => 'sometimes|string'
        ]);

        $undangan = Undangan::where('id', $validated['undangan_id'])->where('user_id', $user->id)->first();
        if (!$undangan) {
            return response()->json(['message' => 'Undangan not found or unauthorized'], 404);
        }

        // Pastikan galery milik undangan yang sesuai
        if ($galery->undangan_id !== $undangan->id) {
            return response()->json(['message' => 'Galery not found or unauthorized'], 404);
        }

        // Simpan gambar hanya jika ada file baru
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('images', $filename, 'public');
            $validated['image'] = $filePath;
        }

        $galery->update($validated);
        return response()->json($galery);
    }

    /**
     * @OA\Delete(
     *     path="/api/galery/{id}",
     *     summary="Delete an existing galery",
     *     security={{"bearerAuth":{}}},
     *     tags={"Galery"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Galery deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Undangan not found or unauthorized"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function destroy(Galery $galery) {
        $user = Auth::user(); // Ambil user yang sedang login

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $undangan = Undangan::where('user_id', $user->id)->get();

        $galeryToDelete = Galery::where('undangan_id', $undangan->id)->where('id', $galery->id)->first();
        $galeryToDelete->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}

