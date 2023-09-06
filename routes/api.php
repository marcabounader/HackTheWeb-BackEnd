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
            Route::get('/get-completed-labs', 'getCompletedLabs');

            Route::post('/submit-flag', 'submitFlag');

        });
        Route::get('/chat/{prompt}', [ChatbotController::class, "chat"]);

    });
    Route::group(["prefix" => "admin", "middleware" => "valid.admin"], function () {
        Route::controller(AdminController::class)->group(function () {
            Route::post('/add-lab','addLab');
            Route::delete('/delete-lab/{id}','deleteLab');
            Route::get('/get-badges','getBadges');
            Route::post('/add-badge','addBadge');
            Route::delete('/delete-badge/{id}','deleteBadge');
            Route::get('/get-lab-categories','getLabCategories');
            Route::post('/add-lab-category','addLabCategory');
            Route::delete('/delete-lab-category/{id}','deleteLabCategory');
            Route::get('/get-lab-difficulties','getLabDifficulties');
            Route::post('/add-lab-difficulty','addLabDifficulty');
            Route::delete('/delete-lab-difficulty/{id}','deleteDifficulty');
            Route::get('/get-badge-categories','getBadgeCategories');
            Route::post('/add-badge-category','addBadgeCategory');
            Route::delete('/delete-badge-category/{id}','deleteBadgeCategory');
        });
    });
});



