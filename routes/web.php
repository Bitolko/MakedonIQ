<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

$page = fn (string $name) => fn () => view('app', ['page' => $name]);

Route::get('/', $page('Home'))->name('home');
Route::get('/quizzes', $page('Quizzes.Index'))->name('quizzes.index');
Route::get('/quizzes/history', $page('Quizzes.Category'))->name('quizzes.history');
Route::get('/quizzes/history/start', $page('Quizzes.Start'))->name('quizzes.history.start');
Route::get('/quizzes/history/active', $page('Quizzes.Active'))->name('quizzes.history.active');
Route::get('/quizzes/history/results', $page('Quizzes.Results'))->name('quizzes.history.results');
Route::get('/about', $page('About'))->name('about');
Route::get('/contact', $page('Contact'))->name('contact');

Route::get('/login', fn () => auth()->check() ? redirect('/dashboard') : view('app', ['page' => 'Auth.Login']))->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
Route::get('/register', fn () => auth()->check() ? redirect('/dashboard') : view('app', ['page' => 'Auth.Register']))->name('register');
Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () use ($page): void {
    Route::get('/dashboard', $page('Dashboard'))->name('dashboard');
    Route::get('/progress', $page('Progress'))->name('progress');
});

// Admin routes remain public until real admin roles and authorization are added.
Route::get('/admin', $page('Admin.Dashboard'))->name('admin.dashboard');
Route::get('/admin/quizzes', $page('Admin.Quizzes'))->name('admin.quizzes');
Route::get('/admin/questions', $page('Admin.Questions'))->name('admin.questions');
