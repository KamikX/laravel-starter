<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class JoditTestController extends Controller
{
    public function index(): View
    {
        return view('pages.jodit-test');
    }
}
