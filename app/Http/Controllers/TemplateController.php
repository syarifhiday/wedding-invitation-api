<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Template;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(name="Templates")
 */
class TemplateController extends Controller {
    /**
     * @OA\Post(
     *     path="/api/templates",
     *     summary="Upload a new template",
     *     security={{"sanctum":{}}},
     *     tags={"Templates"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "description", "type", "file"},
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="type", type="string", enum={"free", "premium"}),
     *                 @OA\Property(property="file", type="string", format="binary"),
     *                 @OA\Property(property="price", type="number", format="float", minimum=0)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Template uploaded successfully"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function store(Request $request): JsonResponse {
        try {
            Log::info('User making request:', ['user' => Auth::user()]);

            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            if ($user->role !== 'admin') {
                return response()->json(['message' => 'Unauthorized'], 403);
            }


            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'type' => 'required|in:free,premium',
                'file' => 'required|file|max:2048',
                'price' => 'required_if:type,premium|numeric|min:0',
            ]);

            Log::info('Validation passed', ['data' => $validatedData]);

            // Simpan file ke public storage
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('templates', $fileName, 'public');

            Log::info('File stored', ['path' => $filePath]);

            $template = Template::create([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'type' => $validatedData['type'],
                'file_path' => $filePath,
                'price' => $validatedData['price'] ?? 0,
            ]);

            Log::info('Template created', ['template' => $template]);

            return response()->json(['message' => 'Template uploaded successfully', 'template' => $template], 201);
        } catch (\Throwable $e) {
            Log::error('Error occurred:', ['error' => $e->getMessage(), 'trace' => $e->getTrace()]);
            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/templates",
     *     summary="Get paginated templates",
     *     tags={"Templates"},
     *     @OA\Response(response=200, description="List of templates")
     * )
     */
    public function index(Request $request): JsonResponse {
        $templates = Template::paginate(10);
        return response()->json($templates);
    }

    /**
     * @OA\Get(
     *     path="/api/templates/{id}",
     *     summary="Get a template by ID",
     *     tags={"Templates"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Template details"),
     *     @OA\Response(response=404, description="Template not found")
     * )
     */
    public function show($id): JsonResponse {
        $template = Template::findOrFail($id);
        return response()->json($template);
    }
}
