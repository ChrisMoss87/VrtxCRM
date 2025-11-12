<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the tenant dashboard.
     */
    public function index(): Response
    {
        return Inertia::render('Dashboard');
    }
}
