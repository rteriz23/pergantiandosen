<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->role === 'kaprodi') {
            return redirect()->route('kaprodi.requests');
        } elseif ($user->role === 'baa') {
            return redirect()->route('baa.requests');
        }
        
        return redirect()->route('schedules.public');
    }
}
