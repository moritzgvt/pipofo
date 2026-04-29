<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\FormTemplateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RESTful API Routes (Requester role)
|--------------------------------------------------------------------------
|
| All endpoints below are prefixed with /api by the framework. They expose
| the form-related functions of the Requester role so external systems
| (e.g. a WordPress plugin) can act on behalf of an authenticated user.
| Authentication uses Laravel Sanctum personal access tokens transported
| as a Bearer token in the Authorization header. Responses are JSON.
|
*/

// Issue a personal access token in exchange for valid credentials.
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    // Auth helpers
    Route::get('/user', [AuthController::class, 'me'])->name('api.user');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    // Form templates available to the requester
    Route::get('/form-templates', [FormTemplateController::class, 'index'])->name('api.form-templates.index');
    Route::get('/form-templates/{formTemplate}', [FormTemplateController::class, 'show'])->name('api.form-templates.show');

    // Requester forms (CRUD + submit)
    Route::get('/forms', [FormController::class, 'index'])->name('api.forms.index');
    Route::post('/form-templates/{formTemplate}/forms', [FormController::class, 'store'])->name('api.forms.store');
    Route::get('/forms/{form}', [FormController::class, 'show'])->name('api.forms.show');
    Route::put('/forms/{form}', [FormController::class, 'update'])->name('api.forms.update');
    Route::patch('/forms/{form}', [FormController::class, 'update']);
    Route::delete('/forms/{form}', [FormController::class, 'destroy'])->name('api.forms.destroy');
    Route::post('/forms/{form}/submit', [FormController::class, 'submit'])->name('api.forms.submit');

    // Comments on a form
    Route::post('/forms/{form}/comments', [CommentController::class, 'store'])->name('api.forms.comments.store');
});
