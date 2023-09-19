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
            Route::get('/get-all-labs','getAllLabs');
            Route::get('/get-labs-info', 'getLabsInfo');

            Route::get('/get-badges','getBadges');
            Route::get('/top-ten','topTen');
        });
    });
    Route::group(["prefix" => "hacker", "middleware" => "valid.normal"], function () {
        Route::controller(HackerController::class)->group(function () {
            Route::get('/get-labs', 'getLabs');
            Route::post('/run-sqli-instance', 'runSqliForUser');
            Route::post('/run-ci-instance','runCommandInjection');
            Route::post('/run-jwt-instance','runInsecureJWT');

            Route::delete('/stop-user-lab/{project_name}', 'stopUserLab');
            Route::get('/get-active-labs', 'getActiveLabs');
            Route::get('/get-completed-labs', 'getCompletedLabs');
            Route::put('/modify-profile','modifyProfile');
            Route::post('/submit-flag', 'submitFlag');
            Route::get('/get-my-badges','getMyBadges');
            Route::get('/statistics','statistics');
        });
        Route::get('/chat/{prompt}', [ChatbotController::class, "chat"]);

    });
    Route::group(["prefix" => "admin", "middleware" => "valid.admin"], function () {
        Route::controller(AdminController::class)->group(function () {
            Route::get('/statistics','statistics');
            Route::get('/get-users','getUsers');
            Route::post('/add-lab','addLab');
            Route::put('/restrict/{user_id}','restrict');
            Route::delete('/stop-user-lab/{project_name}','stopUserLab');
            Route::put('/modify-lab/{lab_id}','modifyLab');
            Route::put('/modify-badge/{badge_id}','modifyBadge');
            Route::get('/get-active-labs', 'getActiveLabs');
            Route::delete('/delete-lab/{id}','deleteLab');
            Route::post('/add-badge','addBadge');
            Route::delete('/delete-badge/{id}','deleteBadge');
            Route::get('/get-lab-categories','getLabCategories');
            Route::post('/add-lab-category','addLabCategory');
            Route::delete('/delete-lab-category/{id}','deleteLabCategory');
            Route::get('/get-lab-difficulties','getLabDifficulties');
            Route::post('/add-lab-difficulty','addLabDifficulty');
            Route::delete('/delete-lab-difficulty/{id}','deleteLabDifficulty');
            Route::get('/get-badge-categories','getBadgeCategories');
            Route::post('/add-badge-category','addBadgeCategory');
            Route::delete('/delete-badge-category/{id}','deleteBadgeCategory');
            Route::put('/modify-lab-difficulty','modifyLabDifficulty');
            Route::put('/modify-lab-category','modifyLabCategory');
            Route::put('/modify-badge-category','modifyBadgeCategory');

        });
    });
});



