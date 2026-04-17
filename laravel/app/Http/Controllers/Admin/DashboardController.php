<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        // TODO: pass real stats once content models exist
        return Inertia::render('Admin/Dashboard', [
            'stats' => [],
        ]);
    }
}
