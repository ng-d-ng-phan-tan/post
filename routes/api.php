<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\AnswerController;

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

Route::get('/health', [HealthController::class, 'check']);

Route::get('/tags', [TagController::class, 'index']);
Route::post('/tags', [TagController::class, 'store']);
Route::get('/tags/{id}', [TagController::class, 'show']);
Route::put('/tags/{id}', [TagController::class, 'update']);
Route::delete('/tags/{id}', [TagController::class, 'destroy']);
Route::get('/tags/restore/{id}', [TagController::class, 'restore']);

Route::get('/questions/getTop3Question', [QuestionController::class, 'getTop3Question']);
Route::get('/questions/search', [QuestionController::class, 'search']);
Route::get('/questions', [QuestionController::class, 'index']);
Route::post('/questions', [QuestionController::class, 'store']);
Route::get('/questions/{id}', [QuestionController::class, 'show']);
Route::put('/questions/{id}', [QuestionController::class, 'update']);
Route::delete('/questions/{id}', [QuestionController::class, 'destroy']);
Route::post('/questions/vote/{id}', [QuestionController::class, 'vote']);
Route::post('/questions/report/{id}', [QuestionController::class, 'report']);



Route::get('/answers', [AnswerController::class, 'index']);
Route::post('/answers', [AnswerController::class, 'store']);
Route::get('/answers/{id}', [AnswerController::class, 'show']);
Route::put('/answers/{id}', [AnswerController::class, 'update']);
Route::delete('/answers/{id}', [AnswerController::class, 'destroy']);
Route::get('/answers/restore/{id}', [AnswerController::class, 'restore']);
Route::post('/answers/verify/{id}', [AnswerController::class, 'verify']);

Route::prefix('admin')->group(function () {
    //approve
    Route::get('/questions', [QuestionController::class, 'index']);
    Route::post('/questions', [QuestionController::class, 'store']);
    Route::get('/questions/{id}', [QuestionController::class, 'show']);
    Route::put('/questions/{id}', [QuestionController::class, 'update']);
    Route::delete('/questions/{id}', [QuestionController::class, 'destroy']);
    Route::get('/questions/restore/{id}', [QuestionController::class, 'restore']);
    Route::post('/questions/approve/{id}', [QuestionController::class, 'approve']);
    Route::post('/questions/searchPostByTitleOrBody', [QuestionController::class, 'searchPostByTitleOrBody']);
    Route::post('/questions/getCount', [QuestionController::class, 'getCount']);

    //
    Route::get('/tags', [TagController::class, 'index']);
    Route::post('/tags', [TagController::class, 'store']);
    Route::get('/tags/{id}', [TagController::class, 'show']);
    Route::put('/tags/{id}', [TagController::class, 'update']);
    Route::delete('/tags/{id}', [TagController::class, 'destroy']);
    Route::get('/tags/restore/{id}', [TagController::class, 'restore']);
});
