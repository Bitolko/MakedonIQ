<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\QuizController;
use Illuminate\Support\Facades\Route;

Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');
Route::get('/categories/{slug}/quizzes', [CategoryController::class, 'quizzes'])->name('api.categories.quizzes');
Route::get('/quizzes/{slug}', [QuizController::class, 'show'])->name('api.quizzes.show');
Route::get('/quizzes/{slug}/questions', [QuizController::class, 'questions'])->name('api.quizzes.questions');
