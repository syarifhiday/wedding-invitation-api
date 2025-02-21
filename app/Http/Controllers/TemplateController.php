<?php

namespace App\Http\Controllers;
use App\Models\Template;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/templates",
     *     summary="Get list of templates",
     *     tags={"Templates"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     )
     * )
     */
    public function index()
    {
        return response()->json(Template::all());
    }
}
