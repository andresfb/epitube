<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

final class SwitchCategoryController extends Controller
{
    public function __invoke(string $category): RedirectResponse
    {
        Session::put('category', $category);

        return redirect()->route('home');
    }
}
