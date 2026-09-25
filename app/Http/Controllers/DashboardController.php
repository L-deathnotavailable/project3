<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('dashboard', [
            'notesCount' => $request->user()->notes()->count(),
            'tagsCount' => Tag::query()->count(),
        ]);
    }
}
