<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PageController extends Controller
{
    public function show(Request $request): View
    {
        $page = $request->route()?->defaults['page'] ?? 'Home';

        return view('app', ['page' => $page]);
    }

    public function login(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect('/dashboard');
        }

        return view('app', ['page' => 'Auth.Login']);
    }

    public function register(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect('/dashboard');
        }

        return view('app', ['page' => 'Auth.Register']);
    }

    public function fallback(Request $request): Response
    {
        if ($request->is('api/*')) {
            abort(404);
        }

        return response()->view('errors.404', [], 404);
    }
}
