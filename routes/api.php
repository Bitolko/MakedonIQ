<?php

use App\Http\Controllers\Api\Admin\AdminCategoryController;
use App\Http\Controllers\Api\Admin\AdminLessonController;
use App\Http\Controllers\Api\Admin\AdminQuestionController;
use App\Http\Controllers\Api\Admin\AdminQuizController;
use App\Http\Controllers\Api\Admin\AdminReadController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\QuizAttemptController;
use App\Http\Controllers\Api\UserStatsController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function (): void {
    Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');
    Route::get('/categories/{slug}/quizzes', [CategoryController::class, 'quizzes'])->name('api.categories.quizzes');
    Route::get('/categories/{slug}', [CategoryController::class, 'show'])->name('api.categories.show');
    Route::get('/categories/{category:slug}/lessons', [LessonController::class, 'category'])->name('api.categories.lessons');
    Route::get('/lessons', [LessonController::class, 'index'])->name('api.lessons.index');
    Route::get('/lessons/{lesson:slug}', [LessonController::class, 'show'])->name('api.lessons.show');
    Route::get('/quizzes/{slug}', [QuizController::class, 'show'])->name('api.quizzes.show');
    Route::get('/quizzes/{slug}/questions', [QuizController::class, 'questions'])->name('api.quizzes.questions');
});

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::get('/me', [ProfileController::class, 'show'])->name('api.me.show');
    Route::patch('/me/profile', [ProfileController::class, 'updateProfile'])->name('api.me.profile.update');
    Route::patch('/me/password', [ProfileController::class, 'updatePassword'])->name('api.me.password.update');
    Route::get('/me/dashboard', [UserStatsController::class, 'dashboard'])->name('api.me.dashboard');
    Route::get('/me/progress', [UserStatsController::class, 'progress'])->name('api.me.progress');
    Route::post('/quizzes/{quiz:slug}/attempts', [QuizAttemptController::class, 'store'])->name('api.quizzes.attempts.store');
    Route::get('/quiz-attempts/{attempt}', [QuizAttemptController::class, 'show'])->name('api.quiz-attempts.show');
});

Route::middleware(['web', 'auth', 'admin'])
    ->prefix('admin')
    ->name('api.admin.')
    ->group(function (): void {
        Route::get('/overview', [AdminReadController::class, 'overview'])->name('overview');
        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}', [AdminCategoryController::class, 'show'])->name('categories.show');
        Route::match(['put', 'patch'], '/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
        Route::get('/lessons', [AdminLessonController::class, 'index'])->name('lessons.index');
        Route::post('/lessons', [AdminLessonController::class, 'store'])->name('lessons.store');
        Route::get('/lessons/{lesson}', [AdminLessonController::class, 'show'])->name('lessons.show');
        Route::match(['put', 'patch'], '/lessons/{lesson}', [AdminLessonController::class, 'update'])->name('lessons.update');
        Route::delete('/lessons/{lesson}', [AdminLessonController::class, 'destroy'])->name('lessons.destroy');
        Route::get('/quizzes', [AdminQuizController::class, 'index'])->name('quizzes.index');
        Route::post('/quizzes', [AdminQuizController::class, 'store'])->name('quizzes.store');
        Route::get('/quizzes/{quiz}/questions', [AdminQuestionController::class, 'index'])->name('quizzes.questions.index');
        Route::post('/quizzes/{quiz}/questions', [AdminQuestionController::class, 'store'])->name('quizzes.questions.store');
        Route::get('/quizzes/{quiz}', [AdminQuizController::class, 'show'])->name('quizzes.show');
        Route::match(['put', 'patch'], '/quizzes/{quiz}', [AdminQuizController::class, 'update'])->name('quizzes.update');
        Route::delete('/quizzes/{quiz}', [AdminQuizController::class, 'destroy'])->name('quizzes.destroy');
        Route::get('/questions', [AdminReadController::class, 'questions'])->name('questions');
        Route::get('/questions/{question}', [AdminQuestionController::class, 'show'])->name('questions.show');
        Route::post('/questions/{question}', [AdminQuestionController::class, 'update'])->name('questions.update.post');
        Route::match(['put', 'patch'], '/questions/{question}', [AdminQuestionController::class, 'update'])->name('questions.update');
        Route::delete('/questions/{question}', [AdminQuestionController::class, 'destroy'])->name('questions.destroy');
        Route::get('/attempts', [AdminReadController::class, 'attempts'])->name('attempts');
    });
