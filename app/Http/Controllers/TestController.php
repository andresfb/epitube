<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class TestController extends Controller
{
    public function index(): Factory|View
    {
        return view('test.index');
    }

    public function update(Request $request, string $slug): void
    {
        //
    }
}
