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
// Route for Login
Route::post('/login', 'AuthController@login');

//Route for Register
Route::post('/register', 'AuthController@register');

// Route for logout
Route::post('/logout', 'AuthController@logout')->middleware('auth:sanctum');

//Group route check login
//Route::middleware(['auth:sanctum'])->prefix('/english/v1')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
// ----------------------------------------------------------------
    Route::post('/listening','English\ListeningController@listening');
    Route::get('/route','AiController@route_landing_page');
    Route::post('/gen_instruction','English\WritingController@writing_gen_instruction');
    Route::post('/evalue','English\WritingController@evalue');
    Route::post('/reading','English\ReadingController@reading');
});



