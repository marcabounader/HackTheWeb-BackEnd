<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\HackerController;
use App\Http\Controllers\UnauthorizedController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
});


Route::group(["middleware" => "auth:api"], function () {
    Route::group(["prefix" => "common"], function () {
        Route::controller(CommonController::class)->group(function () {
            Route::get('/get-labs','getLabs');
        });
    });
    Route::group(["prefix" => "hacker", "middleware" => "valid.normal"], function () {
        Route::controller(HackerController::class)->group(function () {
            Route::post('/run-sqli-lab', 'runSqliForUser');
            Route::delete('/stop-user-lab/{project_name}', 'stopUserLab');
            Route::get('/get-active-labs', 'getActiveLabs');
            Route::post('/submit-flag', 'submitFlag');

        });
        Route::get('/chat/{prompt}', [ChatbotController::class, "chat"]);

    });
    Route::group(["prefix" => "admin", "middleware" => "valid.admin"], function () {
        Route::controller(AdminController::class)->group(function () {
            Route::post('/add-lab','addLab');
            Route::delete('/delete-lab/{id}','deleteLab');

        });
    });
});



