<?php

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
Route::get('/login', $page('Auth.Login'))->name('login');
Route::get('/register', $page('Auth.Register'))->name('register');
Route::get('/dashboard', $page('Dashboard'))->name('dashboard');
Route::get('/progress', $page('Progress'))->name('progress');
Route::get('/admin', $page('Admin.Dashboard'))->name('admin.dashboard');
Route::get('/admin/quizzes', $page('Admin.Quizzes'))->name('admin.quizzes');
Route::get('/admin/questions', $page('Admin.Questions'))->name('admin.questions');
