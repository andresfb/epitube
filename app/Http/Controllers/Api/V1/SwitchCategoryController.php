<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class SwitchCategoryController extends Controller
{
    public function __invoke(string $category)
    {
        Session::put('category', $category);

        return response()->json([
            'message' => 'Category switched successfully'
        ]);
    }
}
