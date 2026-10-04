<?php

namespace App\Http\Controllers\Tendik;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ArsipController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Tendik/Arsip');
    }
}
