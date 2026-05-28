<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\QuizAttemptController;
use Illuminate\Support\Facades\Route;

Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');
Route::get('/categories/{slug}/quizzes', [CategoryController::class, 'quizzes'])->name('api.categories.quizzes');
Route::get('/categories/{slug}', [CategoryController::class, 'show'])->name('api.categories.show');
Route::get('/quizzes/{slug}', [QuizController::class, 'show'])->name('api.quizzes.show');
Route::get('/quizzes/{slug}/questions', [QuizController::class, 'questions'])->name('api.quizzes.questions');

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::post('/quizzes/{quiz:slug}/attempts', [QuizAttemptController::class, 'store'])->name('api.quizzes.attempts.store');
    Route::get('/quiz-attempts/{attempt}', [QuizAttemptController::class, 'show'])->name('api.quiz-attempts.show');
});
