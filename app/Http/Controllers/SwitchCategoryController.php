<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class SwitchCategoryController extends Controller
{
    public function __invoke(string $category): RedirectResponse
    {
        session('category', $category);

        return redirect()->route('home');
    }
}
