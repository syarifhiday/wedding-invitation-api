<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\UndanganController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// AUTH
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


// TEMPLATES
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/templates', [TemplateController::class, 'store']);
    Route::put('/templates/{id}', [TemplateController::class, 'update']);
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


Route::post('/admin/register', [AdminAuthController::class, 'register']);
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/admin/logout', [AdminAuthController::class, 'logout']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
