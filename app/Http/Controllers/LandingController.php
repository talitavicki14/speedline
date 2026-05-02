<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Hero;

class LandingController extends Controller
{
    public function index()
    {
        $services = Service::limit(6)->get();
        $heroes   = Hero::where('is_active', true)->orderBy('order')->get();
        return view('landing', compact('services', 'heroes'));
    }
}
