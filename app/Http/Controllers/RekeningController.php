<?php

namespace App\Http\Controllers;

use App\Models\Rekening;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Undangan;

/**
 * @OA\Tag(name="Rekening", description="API endpoints for managing rekenings")
 */
class RekeningController extends Controller {

    /**
     * @OA\Post(
     *     path="/api/rekening",
     *     tags={"Rekening"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create a new rekening",
     *     * @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *         @OA\Property(property="undangan_id", type="integer", example=1),
     *         @OA\Property(property="account_name", type="string", example="John Doe"),
     *         @OA\Property(property="account_number", type="string", example="1234567890"),
     *         @OA\Property(property="bank", type="string", example="Bank BCA")
     *     )
     * ),
     *     @OA\Response(response=201, description="Rekening created successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
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
            'account_name' => 'required|string',
            'account_number' => 'required|string',
            'bank' => 'required|string'
        ]);

        $undangan = Undangan::where('id', $validated['undangan_id'])->where('user_id', $user->id)->first();

        if (!$undangan) {
            return response()->json(['message' => 'Undangan not found or unauthorized'], 404);
        }

        $rekening = Rekening::create($validated);
        return response()->json($rekening, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/undangan/{undangan_id}/rekening",
     *     tags={"Rekening"},
     *     summary="Get rekening by undangan ID",
     *     @OA\Parameter(name="undangan_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of rekening"),
     *     @OA\Response(response=404, description="No rekening found for this invitation")
     * )
     */
    public function getByUndangan($undangan_id) {
        $rekening = Rekening::where('undangan_id', $undangan_id)->get();

        if ($rekening->isEmpty()) {
            return response()->json(['message' => 'No rekening found for this invitation'], 404);
        }

        return response()->json($rekening);
    }

    /**
     * @OA\Put(
     *     path="/api/rekening/{id}",
     *     tags={"Rekening"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update a rekening",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="undangan_id", type="integer", example=1),
     *             @OA\Property(property="account_name", type="string", example="John Doe"),
     *             @OA\Property(property="account_number", type="string", example="1234567890"),
     *             @OA\Property(property="bank", type="string", example="Bank BCA")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Rekening updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Rekening not found or unauthorized")
     * )
     */
    public function update(Request $request, Rekening $rekening) {
        $user = Auth::user(); // Ambil user yang sedang login

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'undangan_id' => 'required|exists:undangan,id',
            'account_name' => 'sometimes|string',
            'account_number' => 'sometimes|string',
            'bank' => 'sometimes|string'
        ]);

        $undangan = Undangan::where('id', $validated['undangan_id'])->where('user_id', $user->id)->first();
        if (!$undangan) {
            return response()->json(['message' => 'Undangan not found or unauthorized'], 404);
        }

        $rekening = Rekening::where('id', $rekening->id)->where('undangan_id', $undangan->id)->first();
        if (!$rekening) {
            return response()->json(['message' => 'Rekening not found or unauthorized'], 404);
        }

        $rekening->update($validated);
        return response()->json($rekening);
    }

    public function destroy(Rekening $rekening) {
        $rekening->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
