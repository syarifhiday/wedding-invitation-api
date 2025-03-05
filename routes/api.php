<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\UndanganController;
use App\Http\Controllers\AcaraController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\GaleryController;
use App\Http\Controllers\RekeningController;
use App\Http\Controllers\StoryController;
use App\Models\Rekening;
use App\Models\Story;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// AUTH
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/admin-login', [AuthController::class, 'adminLogin']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


// TEMPLATES
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/templates', [TemplateController::class, 'store']);
    Route::put('/templates/{id}', [TemplateController::class, 'update']);
    Route::get('/all-templates', [TemplateController::class, 'getAllTemplates']);
});
Route::get('/templates', [TemplateController::class, 'index']);
Route::get('/templates/{id}', [TemplateController::class, 'show']);
Route::middleware('auth:sanctum')->get('/my-templates', [TemplateController::class, 'myTemplates']);



// USER DATA
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-templates', [TemplateController::class, 'userTemplates']);
    Route::post('save-template', [TemplateController::class, 'saveTemplate']);
    Route::get('/my-undangan', [UndanganController::class, 'userUndangan']);
    Route::post('/undangan', [UndanganController::class, 'store']);
});


// UNDANGAN
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('undangan', UndanganController::class);
    Route::apiResource('undangan/{undangan_id}/acara', AcaraController::class, ['except' => ['index', 'show']]);

    Route::apiResource('undangan/{undangan_id}/story', StoryController::class, ['except' => ['index', 'show', 'update']]);
    Route::post('undangan/{undangan_id}/story/{story}', [StoryController::class, 'updateStory']);

    Route::apiResource('undangan/{undangan_id}/rekening', RekeningController::class, ['except' => ['index', 'show']]);

    Route::apiResource('undangan/{undangan_id}/galery', GaleryController::class, ['except' => ['index', 'show', 'update']]);
    Route::post('galery/{galery}', [GaleryController::class, 'updateGalery']);

    Route::apiResource('banks', BankController::class, ['except' => ['index', 'show', 'update']]);
    Route::post('banks/{bank}', [BankController::class, 'updateBank']);
});

Route::get('/banks', [BankController::class, 'index']);
Route::get('undangan/{undangan_id}/acara', [AcaraController::class, 'getByUndangan']);
Route::get('undangan/{undangan_id}/galery', [GaleryController::class, 'getByUndangan']);
Route::get('undangan/{undangan_id}/rekening', [RekeningController::class, 'getByUndangan']);
Route::get('undangan/{undangan_id}/story', [StoryController::class, 'getByUndangan']);






Route::post('/admin/register', [AdminAuthController::class, 'register']);
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/admin/logout', [AdminAuthController::class, 'logout']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
