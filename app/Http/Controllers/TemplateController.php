<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Template;
use App\Models\UserTemplate;
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
     * @OA\Post(
     *     path="/api/save-template",
     *     summary="Save a template for the authenticated user",
     *     tags={"Templates"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer", example=1, description="The ID of the template to save")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Template saved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Template saved successfully"),
     *             @OA\Property(property="template", ref="#/components/schemas/UserTemplate")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=409, description="Template already saved"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function saveTemplate(Request $request): JsonResponse {
        try {
            Log::info('User making request:', ['user' => Auth::user()]);

            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $validatedData = $request->validate([
                'id' => 'required|exists:templates,id',
            ]);

            Log::info('Validation passed', ['data' => $validatedData]);

            $exists = UserTemplate::where('user_id', $user->id)
                                  ->where('template_id', $validatedData['id'])
                                  ->exists();

            if ($exists) {
                return response()->json(['message' => 'Template already saved'], 409);
            }

            $template = UserTemplate::create([
                'user_id' => $user->id,
                'template_id' => $validatedData['id'],
            ]);

            return response()->json(['message' => 'Template saved successfully', 'template' => $template], 201);
        } catch (\Throwable $e) {
            Log::error('Error occurred:', ['error' => $e->getMessage(), 'trace' => $e->getTrace()]);
            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }


    /**
     * @OA\Put(
     *     path="/api/templates/{id}",
     *     summary="Update an existing template",
     *     security={{"sanctum":{}}},
     *     tags={"Templates"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "description", "type", "flag_active"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="type", type="string", enum={"free", "premium"}),
     *             @OA\Property(property="price", type="number", format="float", minimum=0),
     *             @OA\Property(property="flag_active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Template updated successfully"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Template not found")
     * )
     */
    public function update(Request $request, $id): JsonResponse {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $template = Template::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:free,premium',
            'price' => 'required_if:type,premium|numeric|min:0',
            'flag_active' => 'required|boolean',
        ]);

        $template->update($request->only(['title', 'description', 'type', 'price', 'flag_active']));

        return response()->json(['message' => 'Template updated successfully', 'template' => $template], 200);
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

    /**
     * @OA\Get(
     *     path="/api/my-templates",
     *     summary="Get templates owned by the authenticated user",
     *     security={{"sanctum":{}}},
     *     tags={"Templates"},
     *     @OA\Response(
     *         response=200,
     *         description="List of user's templates",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Template")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */

     public function userTemplates(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $templates = $user->templates; // Pastikan relasi sudah benar di Model User

        return response()->json([
            'status' => 'success',
            'templates' => $templates
        ]);
    }

}

/**
 * @OA\Schema(
 *     schema="Template",
 *     type="object",
 *     title="Template",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="type", type="string", enum={"free", "premium"}),
 *     @OA\Property(property="file_path", type="string"),
 *     @OA\Property(property="price", type="number", format="float"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="UserTemplate",
 *     type="object",
 *     title="UserTemplate",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="template_id", type="integer"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
