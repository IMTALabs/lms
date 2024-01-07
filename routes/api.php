<?php

use App\Http\Controllers\Api\AiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['prefix' => '/development'], function () {

    Route::get('/', function () {
        return 'api test';
    });

    Route::middleware('api')->group(base_path('routes/api/auth.php'));

    Route::namespace('Web')->group(base_path('routes/api/guest.php'));

    Route::prefix('panel')->middleware('api.auth')->namespace('Panel')->group(base_path('routes/api/user.php'));

    Route::group(['namespace' => 'Config', 'middleware' => []], function () {
        Route::get('/config', ['uses' => 'ConfigController@list']);
    });

    Route::prefix('instructor')->middleware(['api.auth', 'api.level-access:teacher'])->namespace('Instructor')->group(base_path('routes/api/instructor.php'));
});

// Chatbot
Route::group(['prefix' => '/chatbot'], function () {
    Route::post('/chat', ['uses' => 'ChatbotController@chat'])
        ->middleware('throttle:chat')
        ->withoutMiddleware(\App\Http\Middleware\Api\CheckApiKey::class);

    Route::post('/new', ['uses' => 'ChatbotController@new'])
        ->middleware('throttle:chat')
        ->withoutMiddleware(\App\Http\Middleware\Api\CheckApiKey::class);

});
// Route::post('/login', 'AiController@login');
Route::post('/login', [AiController::class, 'login']);

// Route for logout
Route::post('/logout', [AiController::class, 'logout'])->middleware('auth:sanctum');

// Route for user registration
// Route::post('/register', [AiController::class, 'register']);
//group check login
Route::middleware(['auth:sanctum'])->group(function () {
// ----------------------------------------------------------------
Route::post('/listening',[AiController::class,'listening']);
Route::get('/route',[AiController::class,'route_landing_page']);
Route::post('/gen_instruction',[AiController::class,'writing_gen_instruction']);
Route::post('/evalue',[AiController::class,'evalue']);
Route::post('/reading',[AiController::class,'reading']);
});



