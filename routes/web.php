<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FormTemplateController;
use App\Http\Controllers\RequesterController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Requester routes
    Route::middleware('role:requester')->prefix('requester')->name('requester.')->group(function () {
        Route::get('/available-forms', [RequesterController::class, 'availableForms'])->name('available-forms');
        Route::get('/my-forms', [RequesterController::class, 'myForms'])->name('my-forms');
        Route::get('/pending-corrections', [RequesterController::class, 'pendingCorrections'])->name('pending-corrections');
        Route::get('/forms/create/{formTemplate}', [FormController::class, 'create'])->name('forms.create');
        Route::post('/forms/create/{formTemplate}', [FormController::class, 'store'])->name('forms.store');
    });

    // Form routes shared by requesters and employees
    Route::get('/forms/{form}', [FormController::class, 'show'])->name('forms.show');
    Route::get('/forms/{form}/edit', [FormController::class, 'edit'])->name('forms.edit');
    Route::put('/forms/{form}', [FormController::class, 'update'])->name('forms.update');
    Route::post('/forms/{form}/submit', [FormController::class, 'submit'])->name('forms.submit');
    Route::get('/forms/{form}/revisions', [FormController::class, 'revisions'])->name('forms.revisions');
    Route::post('/forms/{form}/comments', [CommentController::class, 'store'])->name('forms.comments.store');

    // Employee routes
    Route::middleware('role:employee_base,employee_manager,employee_admin')->prefix('employee')->name('employee.')->group(function () {
        Route::get('/submitted', [EmployeeController::class, 'submittedForms'])->name('submitted');
        Route::get('/corrections', [EmployeeController::class, 'correctionsRequested'])->name('corrections');
        Route::get('/completed', [EmployeeController::class, 'completedForms'])->name('completed');
        Route::post('/forms/{form}/accept', [FormController::class, 'accept'])->name('forms.accept');
        Route::post('/forms/{form}/decline', [FormController::class, 'decline'])->name('forms.decline');
        Route::post('/forms/{form}/request-corrections', [FormController::class, 'requestCorrections'])->name('forms.request-corrections');
    });

    // Manager/Admin routes
    Route::middleware('role:employee_manager,employee_admin')->group(function () {
        Route::resource('form-templates', FormTemplateController::class)->except(['show']);
        Route::post('/forms/{form}/assign-employee', [FormController::class, 'assignEmployee'])->name('forms.assign-employee');
        Route::post('/forms/{form}/remove-employee', [FormController::class, 'removeEmployee'])->name('forms.remove-employee');
    });
});

require __DIR__.'/auth.php';
