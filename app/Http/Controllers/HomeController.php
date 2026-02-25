<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the welcome page or redirect authenticated users to dashboard
     */
    public function index()
    {
        // If user is authenticated, redirect to their dashboard
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }
        
        return view('landing');
    }
    
    /**
     * Redirect to role-specific dashboard
     */
    public function dashboard()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $user = auth()->user();
        
        if ($user->hasRole('Admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('Teacher')) {
            return redirect()->route('teacher.dashboard');
        } else {
            return redirect()->route('student.dashboard');
        }
    }
}
