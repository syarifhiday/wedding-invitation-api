<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(name="Banks", description="API endpoints for managing bank options")
 */
class BankController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/banks",
     *     summary="Get all bank options",
     *     tags={"Banks"},
     *     @OA\Response(
     *         response=200,
     *         description="List of bank options",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="bank",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example=1
     *                     ),
     *                     @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         example="Bank BCA"
     *                     ),
     *                     @OA\Property(
     *                         property="image",
     *                         type="string",
     *                         example="path/to/image.jpg"
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index() {
        $bank = Bank::where('flag_active', true)->get();

        return response()->json([
            'status' => 'success',
            'bank' => $bank
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/banks",
     *     summary="Create a new bank option",
     *     tags={"Banks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "image"},
     *                 @OA\Property(property="name", type="string", example="Bank BCA"),
     *                 @OA\Property(property="image", type="file", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Bank option created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="bank",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="Bank BCA"
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     example="path/to/image.jpg"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthenticated"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthorized"
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request) {

        $user = Auth::user(); // Ambil user yang sedang login

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string',
            'image' => 'required|file|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        $file = $request->file('image');
        $filename = $validated['name'] . time() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('images', $filename, 'public');

        $validated['image'] = $filePath;
        $validated['flag_active'] = true;
        $bank = Bank::create($validated);

        return response()->json([
            'status' => 'success',
            'bank' => $bank
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/banks/{id}",
     *     summary="Update a bank option",
     *     tags={"Banks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the bank option to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", example="Bank BCA"),
     *                 @OA\Property(property="image", type="file", format="binary"),
     *                 @OA\Property(property="flag_active", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bank option updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="bank",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="Bank BCA"
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     example="path/to/image.jpg"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthenticated"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthorized"
     *             )
     *         )
     *     )
     * )
     */
    public function updateBank(Request $request, Bank $bank) {
        $user = Auth::user(); // Ambil user yang sedang login

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string',
        ]);

        // Simpan gambar hanya jika ada file baru
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('images', $filename, 'public');
            $validated['image'] = $filePath;
        }

        // input request flag active and convert to boolean
        if ($request->has('flag_active')) {
            $validated['flag_active'] = filter_var($request->input('flag_active'), FILTER_VALIDATE_BOOLEAN);
        }

        $bank->update($validated);

        return response()->json([
            'status' => 'success',
            'bank' => $bank
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/banks/{id}",
     *     summary="Delete a bank option",
     *     tags={"Banks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the bank option to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bank option deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Bank deleted successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthenticated"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthorized"
     *             )
     *         )
     *     )
     * )
     */
    public function destroy(Bank $bank) {
        $user = Auth::user(); // Ambil user yang sedang login

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $bank->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Bank deleted successfully'
        ]);
    }
}
