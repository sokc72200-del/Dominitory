<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/signin', [AuthController::class, 'signin']);
Route::post('/signup', [AuthController::class, 'signup']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/my-room', function (Request $request) {
        $student = $request->user()->student()->with('room')->first();

        if (!$student || !$student->room) {
            return response()->json(['message' => 'No room assigned yet'], 404);
        }

        return response()->json($student->load('room'));
    });


    Route::middleware(EnsureUserIsAdmin::class)->group(function () {
        Route::apiResource('rooms', RoomController::class);
        Route::apiResource('students', StudentController::class);
        Route::post('students/{student}/assign-room', [StudentController::class, 'assignRoom']);
    });

    Route::post('/signout', [AuthController::class, 'signout']);
});
