<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Undangan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use App\Models\Acara;
use App\Models\Story;
use App\Models\Galery;
use App\Models\Rekening;

/**
 * @OA\Tag(name="Undangan", description="API endpoints for managing undangan")
 */
class UndanganController extends Controller {
    /**
     * @OA\Get(
     *     path="/api/undangan",
     *     summary="Get user's undangan",
     *     tags={"Undangan"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
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
    public function index(): JsonResponse {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $undangan = $user->undangan;

        return response()->json([
            'status' => 'success',
            'undangan' => $undangan
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/undangan",
     *     summary="Create a new undangan",
     *     tags={"Undangan"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"template_id", "man_name", "woman_name", "man_nickname", "woman_nickname"},
     *             @OA\Property(property="template_id", type="string", example="1"),
     *             @OA\Property(property="man_name", type="string", example="John Doe"),
     *             @OA\Property(property="woman_name", type="string", example="Jane Doe"),
     *             @OA\Property(property="man_nickname", type="string", example="John"),
     *             @OA\Property(property="woman_nickname", type="string", example="Jane")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Undangan created successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error")
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
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal Server Error")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:templates,id',
            'man_name' => 'required|string',
            'woman_name' => 'required|string',
            'man_nickname' => 'required|string',
            'woman_nickname' => 'required|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $user_id = Auth::id(); // Ambil user_id dari token login

            $undangan = Undangan::create(array_merge($validated, [
                'user_id' => $user_id,
                'man_ig' => '@sya.hiday',
                'woman_ig' => '@sya.hiday',
                'cover_image' => 'default-cover.jpg',
                'man_address' => 'Jl. Contoh, Kota Contoh',
                'woman_address' => 'Jl. Contoh, Kota Contoh',
                'man_father' => 'Dad',
                'man_mother' => 'Mom',
                'woman_father' => 'Dad',
                'woman_mother' => 'Mom',
            ]));

            // Insert default data into related tables
            Acara::create([
                'undangan_id' => $undangan->id,
                'title' => 'Acara Pernikahan',
                'desc' => 'Deskripsi acara pernikahan',
                'date' => now(),
                'icon' => 'default-icon.png',
            ]);

            Story::create([
                'undangan_id' => $undangan->id,
                'title' => 'Cerita Cinta',
                'desc' => 'Bagaimana kami bertemu dan jatuh cinta',
                'image' => 'default-story.jpg',
            ]);

            Galery::create([
                'undangan_id' => $undangan->id,
                'image' => 'default-gallery.jpg',
            ]);

            Rekening::create([
                'undangan_id' => $undangan->id,
                'account_name' => 'John Doe',
                'account_number' => '1234567890',
                'bank' => 'Bank ABC',
            ]);

            return response()->json($undangan, 201);
        });
    }

    /**
     * @OA\Get(
     *     path="/api/undangan/{id}",
     *     summary="Get a specific undangan",
     *     tags={"Undangan"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the undangan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Undangan not found or unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Undangan not found or unauthorized")
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
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal Server Error")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $user = Auth::user(); // Ambil user yang sedang login

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Pastikan undangan hanya bisa diakses oleh pemiliknya
        $undangan = Undangan::where('id', $id)->where('user_id', $user->id)
            ->with(['acara', 'story', 'galery', 'rekening'])
            ->first();

        if (!$undangan) {
            return response()->json(['message' => 'Undangan not found or unauthorized'], 404);
        }

        return response()->json([
            'status' => 'success',
            'undangan' => $undangan
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/undangan/{id}",
     *     summary="Update a specific undangan",
     *     tags={"Undangan"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the undangan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="man_name", type="string", example="John Doe"),
     *             @OA\Property(property="woman_name", type="string", example="Jane Doe"),
     *             @OA\Property(property="man_nickname", type="string", example="John"),
     *             @OA\Property(property="woman_nickname", type="string", example="Jane"),
     *             @OA\Property(property="man_address", type="string", example="123 Main St"),
     *             @OA\Property(property="woman_address", type="string", example="456 Elm St"),
     *             @OA\Property(property="man_father", type="string", example="Dad"),
     *             @OA\Property(property="man_mother", type="string", example="Mom"),
     *             @OA\Property(property="woman_father", type="string", example="Dad"),
     *             @OA\Property(property="woman_mother", type="string", example="Mom")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Undangan updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Undangan not found or unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Undangan not found or unauthorized")
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
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal Server Error")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {

        $user = Auth::user(); // Ambil user yang sedang login

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $undangan = Undangan::where('id', $id)->where('user_id', $user->id)->first();

        if (!$undangan) {
            return response()->json(['message' => 'Undangan not found or unauthorized'], 404);
        }

        // menambahkan validasi
        $request->validate([
            'man_name' => 'required|string',
            'woman_name' => 'required|string',
            'man_nickname' => 'required|string',
            'woman_nickname' => 'required|string',
            'man_address' => 'required|string',
            'woman_address' => 'required|string',
            'man_father' => 'required|string',
            'man_mother' => 'required|string',
            'woman_father' => 'required|string',
            'woman_mother' => 'required|string',
        ]);

        $undangan->man_name = $request->man_name;
        $undangan->woman_name = $request->woman_name;
        $undangan->man_nickname = $request->man_nickname;
        $undangan->woman_nickname = $request->woman_nickname;
        $undangan->man_address = $request->man_address;
        $undangan->woman_address = $request->woman_address;
        $undangan->man_father = $request->man_father;
        $undangan->man_mother = $request->man_mother;
        $undangan->woman_father = $request->woman_father;
        $undangan->woman_mother = $request->woman_mother;
        $undangan->save();
        return response()->json($undangan);
    }

    /**
     * @OA\Delete(
     *     path="/api/undangan/{id}",
     *     summary="Delete an undangan",
     *     tags={"Undangan"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the undangan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Undangan deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Undangan not found or unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Undangan not found or unauthorized")
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
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal Server Error")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $user = Auth::user(); // Ambil user yang sedang login

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        Undangan::findOrFail($id)->delete();
        return response()->json(['message' => 'Undangan deleted successfully']);
    }
}
