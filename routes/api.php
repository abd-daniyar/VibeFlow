<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BoardController;
use App\Http\Controllers\Api\ColumnController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\CommentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Public routes
Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::group(['prefix' => 'auth'], function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // Board routes
    Route::group(['prefix' => 'boards'], function () {
        Route::get('/', [BoardController::class, 'index']);
        Route::post('/', [BoardController::class, 'store']);
        Route::get('{board}', [BoardController::class, 'show']);
        Route::put('{board}', [BoardController::class, 'update']);
        Route::delete('{board}', [BoardController::class, 'destroy']);

        // Board user management
        Route::post('{board}/users', [BoardController::class, 'addUser']);
        Route::delete('{board}/users/{userId}', [BoardController::class, 'removeUser']);
        Route::put('{board}/users/{userId}/role', [BoardController::class, 'updateUserRole']);

        // Columns within board
        Route::group(['prefix' => '{board}/columns'], function () {
            Route::get('/', [ColumnController::class, 'index']);
            Route::post('/', [ColumnController::class, 'store']);
            Route::post('reorder', [ColumnController::class, 'reorder']);
            Route::get('{column}', [ColumnController::class, 'show']);
            Route::put('{column}', [ColumnController::class, 'update']);
            Route::delete('{column}', [ColumnController::class, 'destroy']);

            // Tasks within column
            Route::group(['prefix' => '{column}/tasks'], function () {
                Route::get('/', [TaskController::class, 'index']);
                Route::post('/', [TaskController::class, 'store']);
                Route::post('reorder', [TaskController::class, 'reorder']);
                Route::get('{task}', [TaskController::class, 'show']);
                Route::put('{task}', [TaskController::class, 'update']);
                Route::delete('{task}', [TaskController::class, 'destroy']);
                Route::post('{task}/move', [TaskController::class, 'move']);

                // Comments within task
                Route::group(['prefix' => '{task}/comments'], function () {
                    Route::get('/', [CommentController::class, 'index']);
                    Route::post('/', [CommentController::class, 'store']);
                    Route::put('{comment}', [CommentController::class, 'update']);
                    Route::delete('{comment}', [CommentController::class, 'destroy']);
                });
            });
        });
    });
});
