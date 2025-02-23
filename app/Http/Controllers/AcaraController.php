<?php

namespace App\Http\Controllers;

use App\Models\Acara;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Undangan;

/**
 * @OA\Tag(name="Acara", description="API endpoints for managing events")
 */
class AcaraController extends Controller {

    /**
     * @OA\Post(
     *     path="/api/acara",
     *     summary="Create a new event",
     *     security={{"bearerAuth":{}}},
     *     tags={"Acara"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"undangan_id", "title", "desc", "date", "icon"},
     *             @OA\Property(property="undangan_id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Wedding Invitation"),
     *             @OA\Property(property="desc", type="string", example="Lorem ipsum dolor sit amet, consectetur adipiscing elit."),
     *             @OA\Property(property="date", type="string", example="2023-08-15"),
     *             @OA\Property(property="icon", type="string", example="https://example.com/icon.png")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Event created successfully"),
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
            'date' => 'required|date',
            'icon' => 'required|string'
        ]);

        $undangan = Undangan::where('id', $validated['undangan_id'])->where('user_id', $user->id)->first();

        if (!$undangan) {
            return response()->json(['message' => 'Undangan not found or unauthorized'], 404);
        }

        $acara = Acara::create($validated);
        return response()->json($acara, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/undangan/{undangan_id}/acara",
     *     summary="Get events by undangan ID",
     *     tags={"Acara"},
     *     @OA\Parameter(name="undangan_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of events"),
     *     @OA\Response(response=404, description="Undangan not found or unauthorized"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function getByUndangan($undangan_id) {
        $undangan = Undangan::where('id', $undangan_id)->first();

        if (!$undangan) {
            return response()->json(['message' => 'Undangan not found or unauthorized'], 404);
        }

        $acaras = Acara::where('undangan_id', $undangan_id)->get();

        if ($acaras->isEmpty()) {
            return response()->json(['message' => 'No events found for this invitation'], 404);
        }

        return response()->json($acaras);
    }


    /**
     * @OA\Put(
     *     path="/api/acara/{id}",
     *     summary="Update an event",
     *     security={{"bearerAuth":{}}},
     *     tags={"Acara"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"undangan_id", "title", "desc", "date", "icon"},
     *             @OA\Property(property="undangan_id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Wedding Invitation"),
     *             @OA\Property(property="desc", type="string", example="Elegant wedding invitation template"),
     *             @OA\Property(property="date", type="string", example="2023-08-15"),
     *             @OA\Property(property="icon", type="string", example="https://example.com/icon.png")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Event updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Undangan not found or unauthorized"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
    */
    public function update(Request $request, Acara $acara) {
        $user = Auth::user(); // Ambil user yang sedang login

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'undangan_id' => 'required|exists:undangan,id',
            'title' => 'sometimes|string',
            'desc' => 'sometimes|string',
            'date' => 'sometimes|date',
            'icon' => 'sometimes|string'
        ]);

        $undangan = Undangan::where('id', $validated['undangan_id'])->where('user_id', $user->id)->first();
        if (!$undangan) {
            return response()->json(['message' => 'Undangan not found or unauthorized'], 404);
        }

        $acara = Acara::where('id', $acara->id)->where('undangan_id', $undangan->id)->first();
        if (!$acara) {
            return response()->json(['message' => 'Acara not found or unauthorized'], 404);
        }

        $acara->update($validated);
        return response()->json($acara);
    }

    /**
     * @OA\Delete(
     *     path="/api/acara/{id}",
     *     summary="Delete an event",
     *     security={{"bearerAuth":{}}},
     *     tags={"Acara"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Event deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Undangan not found or unauthorized"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
    */
    public function destroy(Acara $acara) {
        $user = Auth::user(); // Ambil user yang sedang login

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $acara->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
