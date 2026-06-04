<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'show'])->defaults('page', 'Home')->name('home');
Route::get('/learn', [PageController::class, 'show'])->defaults('page', 'Learn.Index')->name('learn.index');
Route::get('/learn/{categorySlug}', [PageController::class, 'show'])->defaults('page', 'Learn.Category')->name('learn.category');
Route::get('/learn/{categorySlug}/{lessonSlug}', [PageController::class, 'show'])->defaults('page', 'Learn.Show')->name('learn.show');
Route::get('/map-challenge', [PageController::class, 'show'])->defaults('page', 'MapChallenge')->name('map-challenge');
Route::get('/quizzes', [PageController::class, 'show'])->defaults('page', 'Quizzes.Index')->name('quizzes.index');
Route::get('/quizzes/history', [PageController::class, 'show'])->defaults('page', 'Quizzes.Category')->name('quizzes.history');
Route::get('/quizzes/history/start', [PageController::class, 'show'])->defaults('page', 'Quizzes.Start')->name('quizzes.history.start');
Route::get('/quizzes/history/active', [PageController::class, 'show'])->defaults('page', 'Quizzes.Active')->name('quizzes.history.active');
Route::get('/quizzes/history/results', [PageController::class, 'show'])->defaults('page', 'Quizzes.Results')->name('quizzes.history.results');
Route::get('/quizzes/{categorySlug}', [PageController::class, 'show'])->defaults('page', 'Quizzes.Category')->name('quizzes.category');
Route::get('/quizzes/{categorySlug}/{quizSlug}/start', [PageController::class, 'show'])->defaults('page', 'Quizzes.Start')->name('quizzes.start');
Route::get('/quizzes/{categorySlug}/{quizSlug}/active', [PageController::class, 'show'])->defaults('page', 'Quizzes.Active')->name('quizzes.active');
Route::get('/quizzes/{categorySlug}/{quizSlug}/results/{attempt}', [PageController::class, 'show'])->defaults('page', 'Quizzes.Results')->name('quizzes.results.show');
Route::get('/quizzes/{categorySlug}/{quizSlug}/results', [PageController::class, 'show'])->defaults('page', 'Quizzes.Results')->name('quizzes.results');
Route::get('/about', [PageController::class, 'show'])->defaults('page', 'About')->name('about');
Route::get('/contact', [PageController::class, 'show'])->defaults('page', 'Contact')->name('contact');

Route::get('/login', [PageController::class, 'login'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
Route::get('/register', [PageController::class, 'register'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [PageController::class, 'show'])->defaults('page', 'Dashboard')->name('dashboard');
    Route::get('/progress', [PageController::class, 'show'])->defaults('page', 'Progress')->name('progress');
    Route::get('/profile', [PageController::class, 'show'])->defaults('page', 'Profile')->name('profile');
});

Route::middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/admin', [PageController::class, 'show'])->defaults('page', 'Admin.Dashboard')->name('admin.dashboard');
    Route::get('/admin/categories', [PageController::class, 'show'])->defaults('page', 'Admin.Categories')->name('admin.categories');
    Route::get('/admin/lessons', [PageController::class, 'show'])->defaults('page', 'Admin.Lessons')->name('admin.lessons');
    Route::get('/admin/quizzes', [PageController::class, 'show'])->defaults('page', 'Admin.Quizzes')->name('admin.quizzes');
    Route::get('/admin/questions', [PageController::class, 'show'])->defaults('page', 'Admin.Questions')->name('admin.questions');
    Route::get('/admin/attempts', [PageController::class, 'show'])->defaults('page', 'Admin.Attempts')->name('admin.attempts');
    Route::get('/admin/attempts/{attempt}', [PageController::class, 'show'])->defaults('page', 'Admin.AttemptResult')->name('admin.attempts.show');
});

Route::fallback([PageController::class, 'fallback']);
